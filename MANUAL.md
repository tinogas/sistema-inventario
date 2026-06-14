# Manual de Usuario — Inventario Taller

Manual para el personal del taller. Explica, paso a paso y sin tecnicismos, cómo usar el sistema.

## Contenido
1. Introducción
2. Acceso al sistema
3. Roles y permisos
4. La pantalla principal
5. Fotos (usuarios, mecánicos, sucursales)
6. Catálogos
7. Datos de la empresa
8. Entradas de mercancía
9. Salidas
10. Movimientos desde la ficha del producto
11. Traspasos entre sucursales (con recepción parcial)
12. Stock en tránsito
13. Facturas de servicio
14. Sucursales y mapa de ubicación
15. Reportes
16. Pedido de reabastecimiento
17. Respaldos de la base de datos
18. Base de datos (datos de ejemplo / empezar de cero)
19. Preguntas frecuentes

---

## 1. Introducción
Sistema de control de inventario multi-sucursal: productos, entradas, salidas, traspasos, facturación de servicios, reportes y respaldos. El menú que ve cada persona depende de su **rol**.

## 2. Acceso al sistema
**Entrar:** escriba su correo y contraseña y pulse **Ingresar**.
**Salir:** clic en su nombre (arriba a la derecha) → **Cerrar sesión**.
**Olvidó su contraseña:** el sistema no la recupera; un administrador la restablece desde **Usuarios**.

## 3. Roles y permisos
| | Administrador | Almacenista | Consulta |
|--|:--:|:--:|:--:|
| Dashboard / Productos / Reportes | ✓ | ✓ | ✓ (ver) |
| Entradas, Salidas, Traspasos, Facturas | ✓ | ✓ | — |
| Editar productos / mecánicos | ✓ | ✓ | — |
| Sucursales, Usuarios, Empresa, Respaldos, Base de datos | ✓ | — | — |
| Cambiar entre sucursales | ✓ | — | — |

## 4. La pantalla principal
- **Barra superior:** botón de menú, nombre del sistema, **selector de sucursal** (solo admin; incluye "Todas las sucursales"), campana de **alertas**, y su **foto** con el menú de usuario.
- **Menú lateral:** Inventario, Catálogos, Reportes y (admin) Administración — incluye **Respaldos BD** y **Base de datos**.
- **Dashboard:** indicadores, gráfica de movimientos de 7 días (entradas, salidas, traspasos, facturas), stock bajo mínimo y últimas actividades.

## 5. Fotos (usuarios, mecánicos, sucursales)
En los formularios de usuario, mecánico y sucursal hay un campo **Foto**:
1. Pulse **Seleccionar archivo** y elija una imagen (JPG/PNG/WEBP/GIF, máx. 4 MB).
2. La **vista previa** se actualiza al instante.
3. Guarde el registro. Si no sube foto, se muestra un **avatar con las iniciales**.
La foto del usuario logueado aparece en la barra superior.

## 6. Catálogos
Productos, proveedores, mecánicos, servicios, categorías y unidades. Para cada uno: **Nuevo**, **Editar** y baja. Los listados tienen **Imprimir** y **Exportar CSV**.
- **Productos:** código (con botón de **escáner**), código alterno, categoría, unidad, proveedor, precios, stock mínimo y foto.
- **Servicios:** pueden incluir productos (insumos) con el botón **Agregar producto**; al elegir un servicio en una factura, su precio se usa como mano de obra.

## 7. Datos de la empresa
**Administración → Datos de empresa:** nombre, RFC, dirección, ciudad, CP, teléfono, email y pie de página. Se usan en impresiones (facturas, pedido de reabastecimiento).

## 8. Entradas de mercancía
1. **Entradas → Nueva**. Elija sucursal y proveedor.
2. Escanee o busque el producto: se carga en los campos. Ajuste **cantidad** y **precio**.
3. Pulse **+Agregar** (se añade a la lista de abajo). Repita por cada producto.
4. (Opcional) **Importar CFDI XML** para prellenar.
5. **Confirmar entrada**: suma el stock en la sucursal.

## 9. Salidas
1. **Salidas → Nueva**. **Elija la sucursal primero** (define el stock que verá).
2. Mecánico, tipo de servicio y folio Proneg si aplica.
3. Busque el producto: verá **Stock disponible** (`disponible (actual − en tránsito)`). Ponga la cantidad y **+Agregar**. Si no hay suficiente, avisa.
4. Si falta stock y tiene permiso, puede marcar **Forzar stock** (quedará en negativo, se audita).
5. **Confirmar salida**: descuenta el stock.

## 10. Movimientos desde la ficha del producto
Abra un producto (clic en su nombre). En **Stock por sucursal** verá, por cada sucursal, la cantidad, el **stock mínimo** y botones **Entrada** y **Salida**. Al pulsarlos, el formulario abre con el producto y la sucursal ya puestos. La **Salida** aparece deshabilitada si esa sucursal no tiene existencias.

## 11. Traspasos entre sucursales (con recepción parcial)
1. **Traspasos → Nuevo**. Elija **origen** y **destino**, agregue productos y **Enviar**. El stock sale del origen y queda **en tránsito**.
2. En el destino, abra el traspaso y use **Confirmar recepción**: indique la **cantidad recibida** de cada producto.
3. Si recibe **menos** de lo enviado, la diferencia **regresa automáticamente** al origen. El detalle muestra **Enviada / Recibida / Devuelta**.
4. **Cancelar** un traspaso en tránsito devuelve todo al origen.

## 12. Stock en tránsito
Lo que va en un traspaso aún no recibido está **en tránsito**: ya salió del origen pero no suma al destino. Por eso el **stock disponible** para salidas/facturas puede ser menor al físico (resta lo que está en camino).

## 13. Facturas de servicio
1. **Facturas → Nueva**. Sucursal, **mecánico** (se filtran por la sucursal), cliente y vehículo.
2. Elija el **tipo de servicio**: se llena la **mano de obra** (puede editarla).
3. Agregue las **partes/refacciones** usadas.
4. (Opcional) Marque **Descuento** e indique el porcentaje.
5. **Guardar borrador** → **Emitir** (descuenta inventario) → **Marcar pagada**. Puede **Imprimir** (sale con los datos de la empresa).

## 14. Sucursales y mapa de ubicación
**Administración → Sucursales.** Además de nombre, ciudad, dirección y teléfono, puede subir una **foto** y capturar **latitud/longitud**. Con el botón **Mostrar mapa** ve la ubicación (mapa embebido) y hay enlace a **Google Maps**. Tip: copie las coordenadas desde Google Maps (clic derecho sobre el punto).

## 15. Reportes
- **Stock actual:** cada producto se **expande** (+/−) para ver el desglose por sucursal y lo que está en tránsito. Filtros por categoría/búsqueda. **Exportar XLSX** (en Excel los grupos vienen contraídos) y **CSV**.
- **Movimientos:** filtros por sucursal, estado, producto y fechas.
- **Kardex:** historial de un producto.
- **Alertas:** productos por debajo del mínimo.

## 16. Pedido de reabastecimiento
En **Reportes → Alertas**, pulse **Generar pedido**. Se abre un documento con el **encabezado de la empresa**, los productos a reponer, su **proveedor**, la **cantidad a pedir** (mínimo − actual) y el importe estimado. Puede **Imprimir** o **Exportar a Excel (.xlsx)**.

## 17. Respaldos de la base de datos
**Administración → Respaldos BD:**
1. **Generar respaldo ahora**: crea un archivo `.sql` con toda la base.
2. El **historial** muestra fecha, tamaño, tablas, registros y estado.
3. **Descargue** cada respaldo y guárdelo en lugar seguro; puede **Eliminar** los que no necesite.
Para **restaurar**, importe el `.sql` desde phpMyAdmin.

## 18. Base de datos (datos de ejemplo / empezar de cero)
**Administración → Base de datos** (acciones delicadas; haga un respaldo antes):
- **Guardar datos actuales como ejemplo:** toma una "foto" de los datos de ahora como seed.
- **Cargar datos de ejemplo:** reemplaza TODO por ese seed (ideal para presentaciones). Confirme escribiendo **CARGAR**.
- **Vaciar base de datos:** deja el sistema limpio para empezar de cero, **conservando su usuario administrador** y los catálogos base (sucursales, categorías, unidades). Confirme escribiendo **VACIAR**.
- Abajo se ve el **conteo de registros** por tabla.

## 19. Preguntas frecuentes
- **No veo la foto que subí / veo una foto vieja:** pulse **Ctrl+F5** una vez (caché del navegador).
- **El stock para salida es menor al físico:** hay unidades **en tránsito** (en un traspaso sin recibir).
- **Un traspaso "regresó" stock:** se recibió menos de lo enviado; la diferencia volvió al origen.
- **No puedo cerrar sesión / contraseñas:** use el botón del menú de usuario; las contraseñas no se recuperan, un admin las **restablece**.
- **La gráfica del dashboard no aparece:** recargue; requiere conexión para la librería de gráficas.
