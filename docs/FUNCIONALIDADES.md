# Funcionalidades y Flujos — Sistema de Inventario Taller

Documento técnico/funcional basado en la lógica real de los modelos y controladores del sistema. Describe el comportamiento efectivo del código, no comportamientos hipotéticos.

Convenciones generales observadas en el código:

- Toda operación que toca stock se ejecuta dentro de una transacción atómica (`beginTransaction` / `commit` / `rollback`). Si algo falla, se revierte todo.
- Las cantidades capturadas por el usuario se normalizan reemplazando coma por punto: `str_replace(',', '.', ...)` antes de convertir a `float`. Esto aplica en entradas, salidas, traspasos, recepción de traspasos y facturas.
- Los movimientos de stock se hacen con `INSERT ... ON DUPLICATE KEY UPDATE` sobre la tabla `stock_sucursal`, que tiene clave única por `(producto_id, sucursal_id)`.
- Cada acción registra auditoría (`$this->auditoria(...)`) y mensajes flash (`Session::flash`).
- Las acciones POST validan CSRF (`$this->validarCsrf()`).
- Los folios se generan con `generarFolio()` en `core/Model.php`, formato `PREFIJO-AÑO-00001`, contando los movimientos del mismo tipo en el año en curso. El campo `folio` tiene índice UNIQUE: si dos transacciones generan el mismo número, el segundo INSERT falla y revierte.

Prefijos de folio:

| Tipo de movimiento | Prefijo |
|---|---|
| Entrada | `ENT` |
| Salida | `SAL` |
| Traspaso (salida y entrada) | `TRP` |
| Ajuste | `AJU` |
| Factura | `FAC-{sucursal}-{año}-00001` |

---

## 1. Entradas

Archivos: `modules/entradas/EntradaModel.php`, `modules/entradas/EntradaController.php`.

### Alta de entrada (con escáner / CFDI)

Flujo en `EntradaController::nueva()`:

1. Requiere permiso `entradas.crear`.
2. La sucursal de la entrada se toma del POST (`sucursal_id`) o, si no viene, de la sucursal actual del usuario (`Auth::sucursalActual()`).
3. Datos de cabecera capturados: `proveedor_id` (opcional), `referencia_factura`, `uuid_cfdi` (UUID del CFDI), `notas`, y `usuario_id` del usuario autenticado.
4. Las partidas se leen como arreglos paralelos del POST: `producto_id[]`, `cantidad[]`, `precio_unitario[]`. Cada partida se acepta solo si `producto_id > 0`, `cantidad > 0` y `precio_unitario >= 0`.
5. El alta de productos en la captura usa el endpoint de búsqueda con escáner (ver sección 6/7: `api/productos_buscar.php`), que devuelve código, nombre, precios y stock.

Flujo en `EntradaModel::confirmar()` (transacción atómica):

1. Si no hay partidas, lanza error ("La entrada debe tener al menos una partida.").
2. Adquiere un lock advisory `GET_LOCK('folio_entrada', 5)` para reducir la ventana de colisión de folio, y genera el folio `ENT-AÑO-00001`.
3. Inserta la cabecera en `movimientos` con `tipo='entrada'` y `estado='confirmado'`.
4. Por cada partida: inserta el detalle en `movimientos_detalle` y **suma** al stock de la sucursal con `INSERT ... ON DUPLICATE KEY UPDATE cantidad = cantidad + VALUES(cantidad)`.
5. Libera el lock y hace commit. Devuelve el id del movimiento.
6. Ante cualquier excepción libera el lock, hace rollback y relanza el error.

### Actualización de stock

La entrada **incrementa** el stock de la sucursal destino por cada producto, creando la fila en `stock_sucursal` si no existía.

### Cancelación con reversión

Flujo en `EntradaModel::cancelar()`:

1. Valida que la entrada exista y esté en estado `confirmado` (no permite cancelar si ya está cancelada ni en otro estado).
2. Marca la entrada como `cancelado`.
3. Por cada partida **resta** el stock con la condición `cantidad >= :qty` (solo descuenta si hay stock suficiente). Si la fila afectada es 0 (no había stock suficiente para revertir), lanza error indicando que se cancele manualmente con ajuste de inventario, y revierte toda la transacción. Esto protege contra dejar stock negativo al cancelar una entrada cuya mercancía ya salió.

---

## 2. Salidas

Archivos: `modules/salidas/SalidaModel.php`, `modules/salidas/SalidaController.php`.

### Validación de stock disponible por sucursal

En `SalidaController::nueva()`:

1. Requiere permiso `salidas.crear`.
2. Verifica que la sucursal exista y esté activa antes de continuar.
3. Carga los **mecánicos filtrados por la sucursal actual** (`WHERE activo=1 AND sucursal_id = ?`) y todos los servicios activos.
4. El stock que se muestra durante la captura se consulta por la sucursal seleccionada a través de `api/productos_buscar.php` pasando `sucursal_id`. El disponible mostrado al usuario es **stock actual − stock en tránsito** (ver sección 6).

En `SalidaModel::verificarStockLocked()`:

- Para cada partida lee el stock de la sucursal con `SELECT ... FOR UPDATE` (bloqueo de fila para evitar condiciones de carrera), y compara contra la cantidad solicitada.
- Acumula problemas con el formato "Stock insuficiente para «nombre»: disponible X, requerido Y." y devuelve la lista (vacía si todo está OK).

### Salida con opción de forzar

En `SalidaController::nueva()` la opción `forzar` solo se activa si llega `forzar_stock` en el POST **y** el usuario tiene el permiso `salidas.forzar`.

En `SalidaModel::confirmar($datos, $partidas, $forzar)` (transacción atómica):

1. Si no hay partidas, error.
2. Si **no** se fuerza, ejecuta `verificarStockLocked`; si hay problemas, hace rollback y lanza el error concatenado.
3. Genera folio `SAL-AÑO-00001` e inserta la cabecera con `tipo='salida'`, `estado='confirmado'`, guardando `mecanico_id`, `servicio_id`, `referencia_factura`.
4. Por cada partida inserta el detalle y **descuenta** el stock. La lógica de descuento es deliberada para no falsear el inventario: si la fila no existe, el INSERT crea la cantidad **negativa** (`-cantidad`); si existe, el UPDATE resta (`cantidad - cantidad`). Con `forzar=true` el stock puede quedar negativo.
5. Commit y devuelve el id; ante excepción, rollback.

Al confirmar, el controlador añade la nota " (stock forzado)" y registra la auditoría como `confirmar_salida_forzada` cuando aplica.

### Cancelación con reversión

En `SalidaController::cancelar()` se valida que la salida exista y esté en estado `confirmado`. Luego `SalidaModel::cancelarMovimiento()` (transacción):

1. Marca el movimiento como `cancelado`.
2. Por cada partida **suma** de vuelta el stock a la sucursal de la salida (`cantidad = cantidad + :qty`).
3. Commit; ante excepción, rollback.

---

## 3. Traspasos entre sucursales

Archivos: `modules/traspasos/TraspasoModel.php`, `modules/traspasos/TraspasoController.php`.

Modelo de datos: un traspaso se compone de un movimiento de salida (`tipo='traspaso_salida'`) en la sucursal origen y, al recibirse, un movimiento de entrada (`tipo='traspaso_entrada'`) en la sucursal destino, enlazados por la tabla `traspasos`. Ambos movimientos comparten el prefijo de folio `TRP` y se cuentan juntos para evitar colisión de folio (lógica en `generarFolio`).

### Envío (creación)

En `TraspasoController::nuevo()` se requiere permiso `traspasos.crear`. La sucursal origen sale del POST o de la sucursal actual; se capturan `sucursal_dest_id`, `notas` y partidas (`producto_id[]`, `cantidad[]`).

En `TraspasoModel::crear()` (transacción):

1. Valida que haya al menos una partida y que origen ≠ destino.
2. Por cada partida verifica el stock en origen con `SELECT ... FOR UPDATE`; si es insuficiente, lanza error con el nombre del producto.
3. Genera folio `TRP-AÑO-00001`, inserta el movimiento `traspaso_salida` con `estado='confirmado'`, origen y destino.
4. Por cada partida inserta el detalle (precio 0) y **descuenta** el stock en origen.
5. Inserta el registro en `traspasos` con `estado='en_transito'`.
6. Commit; devuelve el id del traspaso.

Tras enviar, el sistema indica que "la sucursal destino debe confirmar la recepción".

### Recepción parcial (confirmación)

En `TraspasoController::confirmarRecepcion()` se requiere permiso `traspasos.confirmar`. El usuario captura la cantidad recibida por producto (arreglo `recibido[producto_id]`).

En `TraspasoModel::confirmarRecepcion($traspaso_id, $cantidadesRecibidas, $usuario_id)` (transacción):

1. Re-lee el traspaso con `... FOR UPDATE` para serializar contra doble confirmación o cancelación concurrente. Verifica que exista y esté `en_transito`.
2. Genera folio de entrada `TRP-AÑO-...`, e inserta el movimiento `traspaso_entrada` con `estado='confirmado'` en la sucursal **destino**.
3. Por cada partida enviada:
   - Determina la cantidad recibida: si no se capturó, asume la cantidad enviada completa. Si es negativa, la fija en 0. **Si la recibida supera la enviada, lanza error** ("No puedes recibir más de lo enviado…").
   - Si la cantidad recibida > 0: inserta detalle de entrada y **suma** esa cantidad al stock del **destino**.
   - Calcula `devuelta = enviada − recibida`. Si es > 0, **regresa** esa cantidad al stock de la **sucursal origen** (lo no recibido vuelve a origen) y marca que hubo devolución.
4. Si hubo faltante, deja constancia en las notas del movimiento de entrada ("Recepción parcial: lo no recibido se devolvió al stock de origen").
5. Actualiza el traspaso a `estado='recibido'`, guarda `movimiento_entrada_id` y `fecha_recepcion=NOW()`.
6. Commit.

### Vista comparativa enviada / recibida / devuelta

`TraspasoModel::getPartidasComparadas()` enriquece cada partida con:

- `enviada`: cantidad del movimiento de salida.
- `recibida`: cantidad del movimiento de entrada (es `null` mientras siga en tránsito; 0 si no se recibió nada de ese producto).
- `devuelta`: `max(0, enviada − recibida)` (es `null` mientras esté en tránsito).

### Cancelación

En `TraspasoController::cancelar()` (requiere permiso `traspasos.crear`). `TraspasoModel::cancelar()` re-lee con `... FOR UPDATE`, valida que el traspaso esté **en tránsito** (solo se pueden cancelar traspasos en tránsito), **regresa** todas las cantidades al stock de origen y marca el traspaso como `cancelado`.

---

## 4. Facturas de servicio

Archivos: `modules/facturas/FacturaModel.php`, `modules/facturas/FacturaController.php`.

Ciclo de estados: **borrador → emitida → pagada** (con la rama alterna **cancelada**).

Nota: el constructor del modelo ejecuta una migración tolerante a errores que agrega la columna `descuento_pct DECIMAL(5,2) DEFAULT 0.00` si no existe.

### Guardar borrador

`FacturaController::guardar()` (permiso `facturas.crear`) lee:

- Cabecera: sucursal, datos del cliente (`cliente_nombre`, `cliente_tel`), vehículo (`vh_marca`, `vh_modelo`, `vh_anio` con default al año actual, `vh_placas`), `mecanico_id`, `servicio_id`, `mano_obra`, `mano_obra_desc`, `referencia_proneg` (Proneg), `notas`.
- Partidas de productos (`producto_id[]`, `cantidad[]`, `precio_unitario[]`).

**Mecánicos filtrados por sucursal:** el helper `catalogos()` carga todos los mecánicos activos junto con su `sucursal_id`; el filtrado por la sucursal elegida en la factura se realiza del lado del cliente (en el navegador) al cambiar la sucursal.

**Mano de obra desde el catálogo de servicios:** los servicios se cargan con su `precio`; la mano de obra se prellena desde ese precio y es **editable** (campo `mano_obra`). Se guarda además una descripción libre (`mano_obra_desc`).

**Descuento porcentual opcional:** `FacturaModel::guardar()` acota `descuento_pct` al rango 0–100. El cálculo de totales es:

- `subtotal` = suma de `cantidad × precio_unitario` de las partidas de producto.
- `bruto` = `subtotal + mano_obra`.
- `total` = si hay descuento, `round(bruto × (1 − descuento_pct/100), 2)`; si no, `bruto`.

El folio se genera por **sucursal + año** con `generarFolioFactura()`, usando un contador en `facturas_folios` (`ON DUPLICATE KEY UPDATE ... LAST_INSERT_ID(ultimo+1)`). Formato: `FAC-{sucursal_id}-{año}-00001` (incluir la sucursal evita colisión del índice UNIQUE entre sucursales). Al editar, solo se permite si el estado es `borrador`; se reemplaza el detalle (DELETE + re-INSERT).

### Emitir (genera la salida de inventario)

`FacturaModel::emitir()`:

1. Valida que la factura exista, esté en `borrador` y tenga partidas.
2. Dentro de una transacción, instancia `SalidaModel` y llama a `confirmar()` con los datos de la factura (sucursal, mecánico, servicio, `referencia_factura = folio`, nota "Factura: …"). Esto **descuenta inventario** generando un movimiento de salida real (con su folio `SAL`).
3. Actualiza la factura a `estado='emitida'`, guarda `movimiento_id` y `fecha_emision=NOW()`.
4. Commit; ante error, rollback.

### Marcar pagada

`FacturaModel::marcarPagada()`: solo si la factura está `emitida`; la pasa a `pagada` y registra `fecha_pago=NOW()`.

### Cancelar

`FacturaModel::cancelar()`:

- Si está en `borrador`: simplemente la marca `cancelada` (no toca inventario).
- Si tiene un `movimiento_id` asociado (ya fue emitida): en transacción, **suma de vuelta** el stock de cada partida a la sucursal, marca el movimiento de salida como `cancelado` y la factura como `cancelada`.
- En cualquier otro caso, solo marca `cancelada`.

### Impresión

`FacturaController::imprimir()` renderiza la vista `imprimir.php` sin layout para impresión directa.

---

## 5. Reportes

Archivos: `modules/reportes/ReporteModel.php`, `modules/reportes/ReporteController.php`. Todas las acciones requieren permiso `reportes.ver`.

### Stock actual (expandible por sucursal + en tránsito)

`ReporteModel::getStock()` devuelve **una fila por (producto, sucursal)** (no agrupada), para que la vista pueda mostrar un desglose expandible por sucursal. Filtra por `activo=1` y opcionalmente por sucursal, categoría y texto de búsqueda (código o nombre).

`ReporteModel::getTransitoActivo()` devuelve los traspasos **en tránsito** activos agrupados/indexados por `producto_id`, con origen, destino, folio y cantidad sumada. Si se pasa sucursal, filtra los traspasos que salen O entran a esa sucursal.

`ReporteController::stock()` carga stock + tránsito + categorías y renderiza. El stock total por producto y el detalle por sucursal y por tránsito se arman en la vista (mismo algoritmo de agrupación que la exportación XLSX).

### Exportación XLSX (filas contraíbles)

`ReporteController::exportarStockXlsx()` (con `?exportar_xlsx`):

1. Agrupa por producto: suma `stock_total` y arma la lista de sucursales y la lista de tránsito.
2. Usa `XlsxWriter` con columnas: Código, Producto, Categoría, Unidad, Stock total, Stock mínimo, Estado, Sucursal / Tránsito, Cantidad.
3. Por cada producto escribe una fila resumen marcada como **fila padre con detalle contraíble** (`writeRow(..., 0, $hasDet, true)`) cuyo Estado es "Bajo mínimo" si `stock_total <= stock_minimo`, o "OK".
4. Debajo escribe filas hijas (nivel 1, contraíbles): una por cada sucursal con su cantidad, y una por cada traspaso en tránsito con el texto "🚚 En tránsito → {destino} ({folio})" y la cantidad.
5. Descarga el archivo `stock_actual_AAAAMMDD_HHMMSS.xlsx`.

### Exportación CSV

`ReporteController::exportarCsv()` (con `?exportar`): genera CSV con separador `;`, escribe BOM UTF-8 (`\xEF\xBB\xBF`) para que Excel en español lo lea correctamente, usa las claves del primer registro como cabecera y vuelca todas las filas. Si no hay datos, muestra advertencia. Se usa tanto en stock como en movimientos.

### Movimientos (con filtros)

`ReporteModel::getMovimientos()` filtra por rango de fechas (`created_at BETWEEN desde 00:00:00 AND hasta 23:59:59`), y opcionalmente por **sucursal, tipo, estado y producto** (este último vía `EXISTS` sobre el detalle, buscando por nombre o código). Resultado paginado (30 por página). El controlador usa por defecto desde = primer día del mes y hasta = hoy. Exportable a CSV.

### Kardex

`ReporteModel::getKardex()` lista los movimientos **confirmados** de un producto en un rango de fechas (opcionalmente por sucursal), ordenados ascendentemente. Marca como **entrada** los tipos `entrada` y `traspaso_entrada`, y como **salida** los tipos `salida` y `traspaso_salida`. El controlador valida que el producto exista y esté activo antes de consultar.

### Alertas (stock bajo mínimo)

`ReporteModel::getAlertasStock()` lista los productos activos cuyo `stock_sucursal.cantidad <= stock_minimo`, ordenados por la diferencia ascendente (lo más crítico primero), opcionalmente filtrados por sucursal.

---

## 6. Stock en tránsito (cálculo y descuento del disponible)

Definido en `api/productos_buscar.php` mediante la función `stockEnTransitoSQL($sid)`:

- El **stock en tránsito** de un producto para una sucursal es la suma de las cantidades de los detalles de movimientos `traspaso_salida` **enviados desde esa sucursal** (`m2.sucursal_id = sucursal`) cuyo traspaso aún está `en_transito`:

  ```
  SUM(md2.cantidad) de movimientos_detalle
  JOIN movimientos (tipo='traspaso_salida', sucursal_id = :sid)
  JOIN traspasos (estado='en_transito')
  ```

- El **stock disponible** que se devuelve al cliente es:

  ```
  stock_disponible = max(0, stock_actual − stock_en_transito)
  ```

Es decir, la mercancía ya descontada del origen pero todavía no recibida en destino no se ofrece como disponible para nuevas salidas/facturas/traspasos desde el origen. Si no hay sucursal definida, el tránsito se considera 0.

Consulta de stock por sucursal en `api/productos_buscar.php`:

- El parámetro `sucursal_id` por GET es respetado **solo para administradores**; para usuarios no administradores se ignora el GET y se fuerza su propia sucursal (`Auth::sucursalFiltro()`), evitando que un almacenista consulte el stock de otra sucursal manipulando la URL.
- Con sucursal definida, el stock actual es `stock_sucursal.cantidad` de esa sucursal. Sin sucursal (admin sin filtro), suma el stock de todas las sucursales.
- Dos modos: búsqueda **exacta por código** (`?codigo=`, para el escáner de barras; busca por `codigo` o `codigo_alterno`, solo productos activos, devuelve precios y stock) y **sugerencias** (`?q=`, autocompletar por código/código alterno/nombre, hasta 10 resultados). Ambos modos devuelven `stock_actual`, `stock_en_transito` y `stock_disponible`. Requiere usuario autenticado.

Este endpoint es el que alimenta la captura de partidas en entradas, salidas, traspasos y facturas (escáner y autocompletar).

---

## 7. Exportación e impresión en catálogos

Los catálogos (productos, proveedores, categorías, unidades, servicios, mecánicos) exponen en su barra de acciones:

- **Exportar CSV:** enlace a `?modulo={catálogo}&accion=exportar_csv`. Ejemplo en productos (`ProductoController::exportarCsv()`): obtiene todos los registros respetando el filtro de sucursal, genera CSV con BOM UTF-8 y separador `;`, usando las claves del primer registro como encabezado, con nombre de archivo tipo `productos_AAAA-MM-DD.csv`. Si no hay datos, muestra advertencia y redirige.
- **Imprimir:** botón que ejecuta `window.print()` del navegador sobre la vista del listado.

(El catálogo de empresa, gestionado por `EmpresaController`, es un formulario único de datos de la compañía —nombre, RFC, dirección, contacto, logo, pie de factura— restringido a administrador, no un listado con CSV/impresión.)
