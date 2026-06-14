# Manual de Usuario — Inventario Taller

Este manual está dirigido al personal del taller. Explica, paso a paso y sin tecnicismos, cómo usar el sistema de inventario.

---

## Contenido

1. Introducción
2. Acceso al sistema
3. Roles y permisos
4. La pantalla principal
5. Catálogos
6. Datos de la empresa
7. Entradas de mercancía
8. Salidas
9. Traspasos entre sucursales
10. Concepto de stock en tránsito
11. Facturas de servicio
12. Reportes
13. Exportar e imprimir
14. Preguntas frecuentes

---

## 1. Introducción

**Inventario Taller** es el sistema que usa el taller para llevar el control de las refacciones, herramientas y materiales que entran y salen del almacén. Con él puede saber, en todo momento, cuánto hay de cada producto, qué se ha movido y qué está por agotarse.

Principales usos del sistema:

- **Control multi-sucursal.** El taller trabaja con varias sucursales. El sistema lleva el inventario de cada una por separado y, si usted es administrador, le permite ver todas al mismo tiempo.
- **Lectura con escáner de código de barras.** Al dar de alta o editar un producto puede capturar el código con un lector de código de barras, sin teclearlo a mano.
- **Facturación.** El sistema permite registrar y emitir facturas, y personalizar los datos de la empresa y el texto que aparece al pie de cada factura impresa.
- **Movimientos de almacén.** Registra entradas, salidas y traspasos entre sucursales, y guarda un historial de movimientos.
- **Catálogos.** Mantiene listas organizadas de productos, proveedores, mecánicos, servicios, categorías y unidades de medida.
- **Reportes y alertas.** Muestra el stock actual, el historial de movimientos y avisos cuando un producto baja de su nivel mínimo.

> El menú que usted vea depende de su rol (ver la sección "Roles y permisos"). Es normal que no todos los usuarios vean las mismas opciones.

---

## 2. Acceso al sistema

### Iniciar sesión

1. Abra el sistema en su navegador. Aparecerá la pantalla **Iniciar sesión** con el nombre del sistema y el texto "Control de inventario".
2. En el campo **Correo electrónico**, escriba su correo (por ejemplo, `usuario@taller.com`).
3. En el campo **Contraseña**, escriba su contraseña. Los caracteres se mostrarán ocultos.
4. Haga clic en el botón amarillo **Ingresar**.
5. Si los datos son correctos, entrará directamente al Dashboard. Si hay un error, aparecerá un aviso rojo en la parte superior indicando el problema; corrija sus datos e intente de nuevo.

### Cerrar sesión

1. En la barra superior, a la derecha, haga clic en el botón con su nombre y el icono de persona.
2. Se abrirá un menú que muestra su correo y su rol.
3. Haga clic en **Cerrar sesión** (texto en rojo, al final del menú).
4. Volverá a la pantalla de inicio de sesión.

> Cierre sesión siempre que termine de trabajar, sobre todo si comparte la computadora.

### Recuperación de contraseña

El sistema no tiene una opción para restablecer la contraseña por su cuenta. Si olvidó su contraseña o no puede entrar:

1. Contacte al **administrador** del sistema.
2. El administrador podrá reestablecer su acceso desde el módulo de Usuarios.

---

## 3. Roles y permisos

Cada usuario tiene un **rol** que determina qué puede hacer. Hay tres roles:

- **Administrador (admin):** acceso total al sistema.
- **Almacenista:** opera el almacén día a día (entradas, salidas, traspasos, facturas y edición de algunos catálogos).
- **Consulta:** solo puede ver información, sin modificar nada.

La siguiente tabla resume qué puede hacer cada rol:

| Acción / Módulo | Administrador | Almacenista | Consulta |
|---|:---:|:---:|:---:|
| Ver Dashboard | Sí | Sí | Sí |
| Ver Productos | Sí | Sí | Sí |
| Crear / editar Productos | Sí | Sí | No |
| Eliminar Productos | Sí | No | No |
| Ver Categorías | Sí | Sí | No |
| Ver Unidades | Sí | Sí | No |
| Ver Proveedores | Sí | Sí | No |
| Crear / editar / eliminar Proveedores | Sí | No | No |
| Ver Mecánicos | Sí | Sí | No |
| Crear / editar Mecánicos | Sí | Sí | No |
| Ver Servicios | Sí | Sí | No |
| Crear / editar Servicios | Sí | No | No |
| Entradas (ver y crear) | Sí | Sí | No |
| Salidas (ver y crear) | Sí | Sí | No |
| Traspasos (ver, crear y confirmar) | Sí | Sí | No |
| Facturas (ver, crear y emitir) | Sí | Sí | No |
| Ver Reportes | Sí | Sí | Sí |
| Sucursales (administración) | Sí | No | No |
| Usuarios (administración) | Sí | No | No |
| Datos de empresa | Sí | No | No |
| Cambiar entre sucursales / "Todas las sucursales" | Sí | No | No |

> El administrador es el único que ve el selector de sucursales y las opciones de Sucursales, Usuarios y Datos de empresa. El almacenista trabaja siempre sobre su sucursal asignada. El usuario de consulta solo puede mirar el Dashboard, los Productos y los Reportes.

---

## 4. La pantalla principal

Tras iniciar sesión verá la pantalla principal, dividida en tres zonas: la barra superior, el menú lateral y el área de contenido (donde aparece el Dashboard).

### Barra superior (navbar)

De izquierda a derecha encontrará:

- **Botón de menú (icono de tres líneas):** muestra u oculta el menú lateral. Útil en pantallas pequeñas.
- **Nombre del sistema (con icono de engranaje):** al hacer clic regresa al Dashboard.
- **Selector de sucursal (solo administrador):** una lista desplegable para elegir la sucursal. Incluye la opción **Todas las sucursales** al inicio. Al elegir una opción, la pantalla se actualiza sola mostrando la información de esa sucursal. Si usted no es administrador, en lugar del selector verá una etiqueta gris con el nombre de su sucursal asignada.
- **Campana de alertas:** el icono de campana lleva directamente a las **Alertas de stock**. Si hay productos por debajo de su mínimo, aparece un punto rojo sobre la campana.
- **Botón de usuario (su nombre):** abre el menú con su correo, su rol y la opción **Cerrar sesión**.

### Menú lateral (sidebar)

El menú de la izquierda está organizado por secciones. Según su rol, verá unas u otras opciones:

- **Inventario:** Dashboard, Facturas, Entradas, Salidas, Traspasos.
- **Catálogos:** Productos, Proveedores, Mecánicos, Servicios, Categorías, Unidades.
- **Reportes:** Stock actual, Movimientos, Alertas.
- **Administración (solo administrador):** Sucursales, Usuarios, Datos de empresa.

La opción en la que se encuentra aparece resaltada.

### El Dashboard

El Dashboard es la pantalla de inicio y muestra un resumen del estado del almacén. En la parte superior derecha verá la fecha actual.

**Tarjetas de indicadores (KPIs).** Una fila de tarjetas con cifras clave:

1. **Productos:** total de productos registrados.
2. **Entradas hoy:** entradas registradas en el día.
3. **Salidas hoy:** salidas registradas en el día.
4. **En tránsito:** traspasos que aún no se confirman.
5. **Alertas stock:** cantidad de productos por debajo de su mínimo (la tarjeta se pone roja si hay alertas y verde si no hay).

**Movimientos últimos 7 días.** Una gráfica de barras que compara, por día, las Entradas, Salidas, Traspasos y Facturas de la última semana.

**Stock bajo mínimo.** Una tabla con los productos que están por debajo de su nivel mínimo, mostrando Producto, Stock, Mínimo y Sucursal. El botón **Ver todo** abre el reporte completo de alertas. Si no hay problemas, aparecerá el mensaje "Sin alertas de stock".

**Últimas actividades.** Una tabla con los últimos movimientos: Folio, Tipo, Sucursal, Usuario, Fecha y Estado.

---

## 5. Catálogos

Los catálogos son las listas base del sistema. A continuación se explica cómo trabajar con cada uno. En las listas suele encontrar dos botones de apoyo:

- **CSV (Exportar CSV):** descarga la lista en un archivo que puede abrir en Excel.
- **Imprimir (icono de impresora):** abre la ventana de impresión del navegador para imprimir la lista.

### Productos

**Ver y buscar:**

1. En el menú lateral, haga clic en **Productos**.
2. Verá la lista con: Código, Nombre, Categoría, Unidad, Stock, Precio venta, Activo y Acciones.
3. Para buscar, escriba en el campo **"Código, nombre o categoría…"**. La búsqueda se realiza sola al dejar de escribir, o puede pulsar **Buscar**. Para limpiar, use el botón **X**.
4. Los productos con stock bajo aparecen marcados en rojo con un triángulo de advertencia.

**Dar de alta un producto:**

1. En la lista, haga clic en **Nuevo producto**.
2. En la sección **Identificación**, llene el **Código** (obligatorio). Puede escribirlo o usar el escáner (ver más abajo). Opcionalmente capture un **Código alterno** (por ejemplo, otro código de barras) y escriba el **Nombre** (obligatorio) y una **Descripción**.
3. En la sección **Clasificación**, elija la **Categoría**, la **Unidad de medida** (obligatoria) y el **Proveedor**.
4. En la sección **Precios y stock**, capture el **Precio costo**, el **Precio venta** (obligatorio) y el **Stock mínimo** (nivel a partir del cual el sistema avisa de stock bajo).
5. Haga clic en **Crear producto**. Si falta algún dato obligatorio, el sistema se lo señalará.

**Usar el escáner de código de barras:**

1. En el formulario del producto, junto al campo **Código**, haga clic en el botón con el icono de código de barras.
2. Se abrirá la ventana **Escáner de código de barras** con un campo grande.
3. Coloque el cursor en ese campo y pase el producto por el lector. El código se llenará solo.
4. Pulse **Enter** o el botón **Usar este código** para colocarlo en el campo Código. Para salir sin capturar, use **Cancelar**.

**Editar un producto:**

1. En la lista, en la columna **Acciones**, haga clic en el botón de lápiz (Editar).
2. Modifique los datos necesarios.
3. Haga clic en **Guardar cambios**.

**Ver detalle:** use el botón con icono de ojo en la columna Acciones.

**Eliminar un producto (solo administrador):**

1. En la columna **Acciones**, haga clic en el botón de papelera (Eliminar).
2. Confirme en el aviso que aparece. **Esta acción no se puede deshacer.**

### Proveedores

1. En el menú lateral, haga clic en **Proveedores**.
2. La lista muestra Razón social, RFC, Contacto, Teléfono y Activo.
3. Para buscar, use el campo **"Buscar por nombre o RFC…"** y el botón **Buscar** (o **X** para limpiar).
4. Para crear (solo administrador): haga clic en **Nuevo proveedor**, llene los datos y guarde.
5. Para editar (solo administrador): haga clic en el botón de lápiz de la fila.
6. Para dar de baja (solo administrador): haga clic en el botón de papelera y confirme en la ventana **Confirmar desactivación**. El proveedor no se borra: solo se marca como inactivo y deja de aparecer en las búsquedas activas.

### Mecánicos

1. En el menú lateral, haga clic en **Mecánicos**.
2. La lista muestra #, Nombre, Sucursal, Teléfono y Activo.
3. Para crear: haga clic en **Nuevo mecánico**, llene los datos y guarde.
4. Para editar: haga clic en el botón de lápiz de la fila.
5. Para dar de baja: haga clic en el botón con el icono de persona y confirme en la ventana **Confirmar baja**. El registro no se elimina: solo se marca como inactivo.

### Servicios

Un servicio puede tener asociados los productos (refacciones o materiales) que consume.

1. En el menú lateral, haga clic en **Servicios**.
2. Para crear uno nuevo, abra el formulario de servicio.
3. En **Datos generales**, escriba el **Nombre** (obligatorio), una **Descripción** y el **Precio** (obligatorio).
4. En **Productos que usa este servicio**, agregue las refacciones:
   1. Haga clic en **Agregar producto**.
   2. En la ventana **Buscar producto**, escriba al menos 2 caracteres del nombre o código.
   3. Haga clic en el producto deseado de la lista de resultados; se agregará a la tabla.
   4. En la columna **Cantidad**, ajuste la cantidad que consume el servicio.
   5. Para quitar un producto de la lista, use el botón **X** de su fila.
5. Haga clic en **Crear servicio** (o **Guardar cambios** si está editando). Si hay errores, el sistema los listará en la parte superior.

### Categorías

1. En el menú lateral, haga clic en **Categorías**.
2. Use el botón para crear una nueva categoría, captúrela y guárdela.
3. Para modificar o eliminar una categoría existente, use los botones de su fila.

> Las categorías se utilizan para clasificar los productos en el campo **Categoría** del formulario de producto.

### Unidades

1. En el menú lateral, haga clic en **Unidades**.
2. Cree las unidades de medida que necesite (cada una tiene una clave y un nombre).
3. Para modificar o eliminar una unidad existente, use los botones de su fila.

> Las unidades se utilizan en el campo **Unidad de medida** del formulario de producto.

---

## 6. Datos de la empresa

Este módulo permite registrar los datos del taller que aparecen en el sistema y en las facturas impresas. **Solo el administrador puede acceder.**

1. En el menú lateral, en la sección **Administración**, haga clic en **Datos de empresa**.
2. Complete los campos según corresponda:
   - **Nombre de la empresa** (obligatorio).
   - **RFC.**
   - **Dirección.**
   - **Ciudad.**
   - **Código postal.**
   - **Teléfono.**
   - **Correo electrónico.**
   - **Ruta del logo:** ubicación del archivo del logo dentro del sistema.
   - **Pie de página para impresión de facturas:** texto que aparecerá al final de cada factura impresa (por ejemplo, un mensaje de agradecimiento o de garantía).
3. Haga clic en **Guardar** para conservar los cambios, o en **Cancelar** para salir sin guardar.

---

Este manual explica, paso a paso, cómo registrar movimientos de inventario en el sistema del taller: **entradas de mercancía**, **salidas**, **traspasos entre sucursales** y el concepto de **stock en tránsito**.

En todas las pantallas de captura funciona igual el cuadro de productos:

- Puede **escanear** el código de barras con la pistola lectora, o
- **escribir** el código o el nombre del producto y elegirlo de la lista de sugerencias que aparece debajo.

---

## 7. Entradas de mercancía

Una **entrada** se usa para registrar una **compra** o recepción de mercancía. Al confirmarla, el sistema **suma** las cantidades al stock de la sucursal indicada.

### Cómo llegar
Menú **Entradas** → botón verde **Nueva entrada** (arriba a la derecha).

### Paso a paso

1. **Elija la sucursal** (campo obligatorio, marcado con asterisco rojo).
   - Si su usuario está asignado a una sola sucursal, aparecerá ya fijada y no se puede cambiar.
   - Si es administrador, elíjala en la lista desplegable.

2. **Seleccione el proveedor** en la lista. Si no aplica, deje **"— Sin proveedor —"**.

3. (Opcional) Escriba la **Referencia / Folio factura (Proneg)**, por ejemplo `FAC-2025-001`. Sirve para identificar la factura del proveedor.

4. (Opcional) Escriba **Notas** u observaciones.

5. **Capture los productos** en el cuadro **"Captura de productos"** (cabecera verde, con la etiqueta **"Escáner activo"**). Tiene dos formas de trabajar:

   **A) Con escáner (rápido):**
   - Coloque el cursor en el campo **"Código de barras / Buscar producto"** y dispare el lector.
   - El producto se **agrega directamente** a la tabla con la cantidad que tenga puesta y el precio de costo registrado.

   **B) Búsqueda manual (buscar → ajustar → +Agregar):**
   1. Escriba al menos 2 letras del código o nombre; aparecerá una lista de sugerencias.
   2. Haga clic en el producto deseado. **Importante:** al elegirlo de la lista, el producto **solo se carga** en los campos; **todavía no se agrega** a la tabla.
   3. El campo **"Precio unitario ($)"** se rellena con el precio de costo del producto; puede modificarlo si la compra trae otro precio.
   4. Ajuste la **"Cantidad"** (admite decimales).
   5. Pulse el botón verde **+Agregar** (o la tecla **Enter**) para pasarlo a la tabla **"Partidas de la entrada"**.

   > **Nota:** Si captura el mismo producto dos veces, el sistema **suma** las cantidades en la misma partida.

6. **Revise la tabla "Partidas de la entrada"**. Para cada renglón puede:
   - Cambiar la **cantidad** directamente en la casilla.
   - Eliminar la partida con el botón de la **papelera** (rojo).
   - Al pie verá el **Total** general de la compra.

### Importar CFDI XML (opcional)

En lugar de capturar producto por producto, puede cargar el XML del CFDI del proveedor:

1. En **"Importar CFDI XML (opcional)"**, pulse y seleccione el archivo `.xml`.
2. Pulse el botón **Importar**.
3. El sistema lee el XML y **pre-llena las partidas** automáticamente (código, descripción, cantidad y precio).

> **Advertencia:** Los conceptos del XML que **no existan** en el catálogo de productos serán **omitidos**. El sistema le avisará cuántos conceptos no se encontraron; esos debe agregarlos manualmente si los necesita.

### Confirmar la entrada

1. Cuando la tabla tenga al menos una partida, se habilita el botón verde **Confirmar entrada**.
2. Púlselo para guardar.

> **Qué pasa con el stock:** al confirmar, las cantidades capturadas **se suman** al stock de la sucursal elegida. La entrada queda registrada en la lista de **Entradas** con su folio, proveedor, referencia, sucursal, número de partidas, fecha y estado.

---

## 8. Salidas

Una **salida** registra el consumo o venta de productos (por ejemplo, refacciones usadas en un servicio). Al confirmarla, el sistema **descuenta** las cantidades del stock de la sucursal.

### Cómo llegar
Menú **Salidas** → botón rojo **Nueva salida**.

### Paso a paso

1. **Elija PRIMERO la sucursal** (campo obligatorio, asterisco rojo).

   > **MUY IMPORTANTE:** seleccione la sucursal **antes** de buscar productos. El **stock que se muestra depende de la sucursal elegida**. Si intenta buscar un producto sin haber elegido sucursal, el sistema le pedirá: *"Selecciona la sucursal primero para ver el stock correcto de esa sucursal."*
   >
   > Si su usuario tiene una sola sucursal, ya aparece fijada.

2. (Opcional) Seleccione el **Mecánico** que solicita las refacciones.

3. (Opcional) Seleccione el **Tipo de servicio**.

4. (Opcional, recomendado) Escriba el **Folio Proneg / Orden de servicio** (por ejemplo `OS-2025-0123` o el número de factura de Proneg). Sirve para la **trazabilidad** con Proneg.

5. (Opcional) Escriba **Notas**.

6. **Capture los productos** en el cuadro **"Captura de productos"** (cabecera roja):

   1. Escanee el código, o escriba al menos 2 letras y elija el producto de la lista de sugerencias.
   2. Al cargar el producto, el campo **"Stock disponible"** muestra cuánto hay para usar en **esa sucursal**.
      - Si hay mercancía comprometida en traspasos, lo verá desglosado así: **`disponible (actual - en tránsito)`**, por ejemplo `12 (15 - 3 en tránsito)`. Es decir: hay 15 físicos, pero 3 están reservados/en camino, por lo que **solo 12 están realmente disponibles** para esta salida.
   3. Ajuste la **Cantidad**.
   4. Pulse **+Agregar** (o **Enter**).

7. **Validación de stock al agregar:** si la cantidad solicitada es mayor que el stock disponible, el sistema **no agrega** la partida y muestra una advertencia indicando lo disponible y lo requerido. Tiene dos opciones:
   - **Ajustar la cantidad** para que no rebase lo disponible, o
   - usar **"Forzar stock"** (ver más abajo).

8. **Revise la tabla "Partidas de la salida"**. Para cada renglón verá el **Stock disp.** y la **Cantidad**. Los renglones con cantidad mayor al disponible se marcan en **amarillo** con la etiqueta **"Stock bajo"**. Puede cambiar la cantidad en la casilla o eliminar con la **papelera**.

### Opción "Forzar stock"

Si alguna partida tiene stock insuficiente, aparece una **alerta amarilla** con la casilla:

- **"Continuar de todas formas (el stock quedará en negativo, se registrará en auditoría)"**

> **Advertencia:** marque **Forzar stock** solo cuando sea estrictamente necesario. El stock del producto **quedará en negativo** y el movimiento **se registra en auditoría**.

### Confirmar la salida

1. Con al menos una partida, se habilita el botón rojo **Confirmar salida**.
2. Púlselo para guardar.

> **Qué pasa con el stock:** al confirmar, las cantidades **se restan** del stock de la sucursal seleccionada. La salida queda en la lista de **Salidas** con folio, mecánico, servicio, referencia Proneg, sucursal, partidas, fecha y estado.

---

## 9. Traspasos entre sucursales

Un **traspaso** mueve mercancía de una sucursal (**origen**) a otra (**destino**). El proceso tiene **dos momentos**:

1. **Envío:** se descuenta del origen y la mercancía queda **EN TRÁNSITO**.
2. **Recepción:** la sucursal destino confirma cuánto recibió, y entonces ingresa a su stock.

### 3.1 Crear (enviar) un traspaso

**Cómo llegar:** Menú **Traspasos** → botón **Nuevo traspaso**.

1. **Sucursal origen** (obligatoria). Si su usuario tiene una sola sucursal, ya viene fija; si es administrador, elíjala.

2. **Sucursal destino** (obligatoria). No puede ser igual al origen (el sistema bloquea esa opción automáticamente).

3. (Opcional) Escriba el **motivo** del traspaso en **Notas**.

4. **Agregue los productos** en el cuadro **"Agregar productos al traspaso"** (cabecera azul):
   1. Escanee, o escriba y elija el producto de las sugerencias.
   2. Al cargarlo, el campo **"Stock disponible"** muestra cuánto hay en la **sucursal origen**. Si hay mercancía comprometida en otros traspasos, aparece entre paréntesis cuánto está en tránsito, por ejemplo `10.000 (−2.000 en tránsito)`.
   3. Ajuste la **Cantidad**.
   4. Pulse **+Agregar** (o **Enter**).

   > **Nota:** no puede traspasar más de lo disponible en el origen. Si la cantidad supera el stock disponible, el sistema muestra una advertencia y no agrega la partida.
   >
   > **Nota:** si cambia la sucursal origen, los productos cargados y el stock mostrado se limpian, porque el stock es distinto en cada sucursal.

5. Revise la tabla **"Productos a traspasar"** (puede ajustar cantidad o quitar partidas).

6. Pulse el botón azul **Enviar traspaso**.

> **Qué pasa con el stock al enviar:** las cantidades **se descuentan del origen de inmediato** y el traspaso queda en estado **En tránsito**. Esa mercancía **todavía no entra** al destino: está reservada en camino.

### 3.2 Confirmar la RECEPCIÓN

Cuando el traspaso está **En tránsito**, la sucursal destino debe confirmar lo que recibió.

**Cómo llegar:** Menú **Traspasos** → botón del ojo (**Ver detalle**) del traspaso correspondiente.

En el detalle verá los datos del traspaso (folio, origen, destino, estado, quién lo envió y fecha) y la tabla de productos con la columna **Enviada**.

1. En la columna **"Cantidad recibida"**, escriba para cada producto cuánto llegó realmente.
   - Por defecto viene la cantidad enviada completa.
   - No puede capturar **más** de lo enviado (ese es el máximo permitido).
2. Pulse el botón verde **Confirmar recepción**.

> **MUY IMPORTANTE — recepción parcial:** si recibe **menos** de lo enviado en algún producto, la diferencia **REGRESA automáticamente al stock de la sucursal de origen**. No se pierde mercancía: lo recibido entra al destino y lo no recibido vuelve al origen.

### 3.3 Cómo leer un traspaso ya recibido

Una vez confirmada la recepción (estado **Recibido**), el detalle muestra por producto:

- **Enviada:** lo que salió del origen.
- **Recibida:** lo que efectivamente entró al destino.
- **Devuelta a origen:** la diferencia que regresó al origen.

Los renglones con devolución se resaltan en **amarillo** con un ícono de retorno. Si hubo diferencias, aparece el aviso:

> *"Recepción parcial: las cantidades no recibidas se devolvieron automáticamente al stock de la sucursal de origen."*

### 3.4 Cancelar un traspaso

Mientras el traspaso esté **En tránsito**, puede cancelarse desde su detalle con el botón **Cancelar traspaso**.

1. Pulse **Cancelar traspaso**.
2. Confirme el mensaje: *"¿Cancelar este traspaso? Se revertirá el stock en origen."*

> **Qué pasa con el stock al cancelar:** se **devuelve todo** al stock de la sucursal de origen. Nada llega al destino.

### Estados de un traspaso (lista)

En la lista de **Traspasos** verá cada movimiento con su **folio, origen, destino, partidas, fecha de envío, fecha de recepción y estado** (En tránsito / Recibido, etc.). Use el botón del ojo para abrir el detalle.

---

## 10. Concepto de stock en tránsito

El **stock en tránsito** es la mercancía que ya **salió de una sucursal en un traspaso**, pero que **todavía no ha sido recibida** en la sucursal destino. Está "en camino" o pendiente de confirmación de recepción.

### ¿Por qué existe?

Cuando se **envía** un traspaso, la mercancía se descuenta del origen al instante. Hasta que el destino **confirme la recepción**, esas unidades quedan **comprometidas**: ya no están físicamente libres para usarse.

### ¿Por qué se descuenta del disponible?

Para evitar errores, el sistema separa dos números:

- **Stock actual:** lo que figura físicamente en la sucursal.
- **En tránsito:** lo que está comprometido en traspasos enviados sin recibir.

El número que importa para operar es el **Stock disponible**, que se calcula así:

> **Stock disponible = Stock actual − En tránsito**

Por eso, en **Salidas** y al armar nuevos **traspasos**, el campo **"Stock disponible"** ya descuenta lo que está en tránsito y, cuando aplica, lo muestra desglosado, por ejemplo **`12 (15 - 3 en tránsito)`**.

> **En resumen:** aunque haya 15 unidades físicas, si 3 están comprometidas en un traspaso, **solo puede disponer de 12** para salidas o nuevos traspasos. Esto evita comprometer o facturar mercancía que en realidad ya está apartada para otra sucursal.

---

---

## 11. Facturas de servicio

En esta sección se crean las facturas de los servicios que realiza el taller. Cada factura guarda los datos del cliente y su vehículo, las partes/refacciones utilizadas y la mano de obra, y al emitirse descuenta automáticamente las refacciones del inventario.

### Estados de una factura

Toda factura pasa por uno de estos cuatro estados. Se identifican por el color de la etiqueta:

- **Borrador** (etiqueta amarilla): la factura está en captura. Todavía **no** descuenta inventario. Se puede editar libremente.
- **Emitida** (etiqueta azul): la factura ya se emitió. En ese momento el sistema **descuenta del inventario** las partes capturadas. Ya se puede imprimir.
- **Pagada** (etiqueta verde): se registró que el cliente ya pagó. Se puede imprimir.
- **Cancelada** (etiqueta gris): la factura se anuló. Si la factura ya estaba emitida o pagada, al cancelarla el **stock se devuelve al inventario** automáticamente.

> Recuerde: el inventario se mueve únicamente al **emitir** (descuenta) y al **cancelar** una factura ya emitida/pagada (regresa). Mientras esté en borrador, el inventario no cambia.

### 1.1 Crear una factura nueva

1. Entre al módulo **Facturas de servicio**.
2. Haga clic en el botón **Nueva factura** (arriba a la derecha).

### 1.2 Elegir sucursal y mecánico

1. **Sucursal**: seleccione la sucursal donde se realiza el servicio.
   - Si su usuario solo tiene una sucursal asignada, esta aparecerá ya fijada y no podrá cambiarla.
2. **Mecánico**: abra la lista y elija al mecánico.
   - **Importante**: la lista de mecánicos se filtra según la sucursal elegida. Solo verá a los mecánicos de esa sucursal.
   - Si cambia de sucursal después de haber elegido un mecánico que no pertenece a la nueva sucursal, el sistema limpiará esa selección. Vuelva a elegir el mecánico.
   - El mecánico es opcional: puede dejar **— Sin mecánico —**.

### 1.3 Elegir el tipo de servicio (autocompleta la mano de obra)

1. Abra la lista **Tipo de servicio** y elija el servicio realizado. Junto a cada nombre se muestra su precio, si lo tiene.
2. Al elegir un servicio con precio, el sistema **autocompleta el monto de la mano de obra** en el campo correspondiente.
3. Ese monto **es editable**: puede cambiarlo si el cobro real fue distinto. El texto "Editable aunque venga del servicio" se lo recuerda.
4. La **descripción** de la mano de obra **no** se rellena sola; escríbala usted en el campo "Descripción de mano de obra" (por ejemplo: "Cambio de muelles delanteros, alineación").

### 1.4 Capturar los datos del cliente y el vehículo

Llene los campos del recuadro **Datos del cliente y vehículo**. Los campos con asterisco rojo (*) son obligatorios:

1. **Nombre del cliente** (obligatorio).
2. **Teléfono** (opcional).
3. **Marca**, **Modelo** y **Año** del vehículo (obligatorios).
4. **Placas** (opcional).
5. **Ref. Proneg** (opcional): folio de Proneg si aplica.

### 1.5 Capturar las partes / refacciones usadas

En el recuadro **Partes / Refacciones utilizadas** (con el escáner activo):

1. Coloque el cursor en el campo **Código o nombre del producto**.
2. Capture la refacción de cualquiera de estas formas:
   - **Escanear** el código de barras con la pistola lectora.
   - **Escribir** el código y presionar **Enter**.
   - **Escribir parte del nombre** (2 letras o más) y elegir de la lista de sugerencias que aparece.
3. Al cargar el producto, el sistema trae su **precio de venta**. Ajuste si es necesario:
   - **Cantidad**: indique cuántas piezas se usaron.
   - **Precio venta ($)**: puede modificarlo.
4. Haga clic en **Agregar** (o presione Enter) para pasar la parte a la tabla.
5. Repita para cada refacción. El contador "Partes" muestra cuántas lleva.

Notas sobre las partes:
- Si agrega un producto que ya está en la tabla, el sistema **suma** la cantidad a la partida existente.
- Si la cantidad supera el stock disponible, el sistema avisa **"Stock insuficiente"** y no agrega la parte hasta que ajuste la cantidad.
- Para cambiar una cantidad ya capturada, edítela directamente en la columna **Cantidad** de la tabla.
- Para quitar una parte, use el botón rojo de bote de basura de esa fila.

### 1.6 Aplicar descuento porcentual

1. En el campo **Descuento**, marque la **casilla** de la izquierda para activarlo.
2. Escriba el **porcentaje** (de 0 a 100).
3. El descuento se aplica sobre el total (partes + mano de obra). En el recuadro de totales verá el renglón "Descuento (%)" en rojo con el monto descontado.
4. Si desmarca la casilla, el descuento se desactiva y vuelve a 0.

El recuadro de **totales** se actualiza solo y muestra: Partes, Mano de obra, Descuento (si aplica) y **Total**.

### 1.7 Guardar como borrador

1. Cuando termine la captura, agregue cualquier observación en el campo **Notas** (opcional).
2. Haga clic en **Guardar borrador**.
3. La factura queda en estado **Borrador**. **Todavía no descuenta inventario** y puede seguir editándola.

### 1.8 Emitir la factura (descuenta el inventario)

1. Abra la factura en borrador (módulo Facturas → clic en el ícono del ojo de la fila).
2. Revise que todo esté correcto. Si necesita corregir, use **Editar**.
3. Haga clic en **Emitir factura**.
4. Al emitir, el sistema **descuenta del inventario** las partes capturadas y registra el movimiento de salida. La factura pasa a estado **Emitida**.

> Una vez emitida, la factura ya no se edita. Si hay un error, deberá cancelarla (el stock regresa) y crear una nueva.

### 1.9 Marcar como pagada

1. Abra una factura en estado **Emitida**.
2. Haga clic en **Marcar como pagada**.
3. La factura pasa a estado **Pagada** y se registra la fecha de pago.

### 1.10 Imprimir

1. Abra una factura que esté **Emitida** o **Pagada** (el botón de imprimir solo aparece en esos estados).
2. Haga clic en **Imprimir**. Se abrirá la versión para imprimir en una pestaña nueva.

### 1.11 Cancelar una factura

1. Abra la factura (puede cancelarse si está en borrador, emitida o pagada).
2. Haga clic en **Cancelar**.
3. El sistema pedirá confirmación.
   - Si la factura ya estaba emitida o pagada, el aviso indica que **"El stock será revertido"**: las partes regresan al inventario.
4. Confirme. La factura pasa a estado **Cancelada**.

### 1.12 Buscar facturas existentes

En la lista de facturas puede:
1. Filtrar por **estado** (Todos, Borrador, Emitida, Pagada, Cancelada).
2. Buscar por **folio, cliente o placas** en el campo de búsqueda.
3. Hacer clic en **Filtrar**. Use **Limpiar** para quitar los filtros.

---

## 12. Reportes

El módulo de reportes permite consultar el inventario y los movimientos desde distintos ángulos.

### 2.1 Stock actual

Muestra el stock de todos los productos. Es una **vista expandible**: cada producto puede abrirse para ver el detalle por sucursal y lo que está en tránsito.

**Cómo usar la vista expandible:**
1. En la columna izquierda de cada producto verá un botón de flecha (chevron `›`).
2. Haga clic en él para **expandir** ese producto y ver:
   - El stock que tiene en **cada sucursal**.
   - Las cantidades **En tránsito** (refacciones que van de una sucursal a otra dentro de un traspaso activo), con un enlace al folio del traspaso.
3. Vuelva a hacer clic en la flecha para **contraer**.
4. El botón **Expandir todo** (arriba) abre todos los productos a la vez; al volver a presionarlo cambia a **Colapsar todo**.

**Indicadores que verá:**
- **Stock total** del producto sumando todas las sucursales.
- Una etiqueta azul **"X en tránsito"** cuando hay unidades viajando entre sucursales.
- Estado **OK** (verde) o **Bajo mínimo** (amarillo) según el stock mínimo. Las filas bajo mínimo se resaltan en amarillo.

**Filtros:**
1. **Buscar producto**: escriba nombre o código.
2. **Categoría**: elija una categoría de la lista.
3. Haga clic en **Filtrar**. Use **Limpiar** para reiniciar.

**Exportar:**
- Botón **Exportar XLSX**: genera un Excel con los productos. En el Excel, el detalle por sucursal viene en **grupos contraíbles** (botones +/− de Excel). Ver la sección 3.
- Botón **CSV**: genera un archivo de texto separado por comas, sin grupos.

### 2.2 Movimientos

Muestra el historial de movimientos de inventario (entradas, salidas, traspasos y ajustes).

**Filtros disponibles:**
1. **Sucursal**: Todas o una en específico.
2. **Tipo**: Entrada, Salida, Traspaso salida, Traspaso entrada, Ajuste, o Todos.
3. **Estado**: Borrador, Confirmado, Cancelado, o Todos.
4. **Desde** y **Hasta**: rango de fechas.
5. **Producto**: nombre o código.
6. Haga clic en **Filtrar**. Use **Limpiar** para quitar todos los filtros.

Cada renglón muestra folio, tipo, sucursal, referencia de factura (si aplica), número de partidas, fecha y estado. El botón **Ver** abre el detalle del movimiento.

**Exportar:** el botón **Exportar CSV** (arriba a la derecha) descarga los movimientos con los filtros aplicados.

### 2.3 Kardex

Muestra el historial completo de **un solo producto**: cada entrada, cada salida y el saldo acumulado.

1. En el campo **Producto (código o nombre)**, escriba 2 letras o más y elija el producto de la lista de sugerencias.
2. Opcionalmente acote el rango con **Desde** y **Hasta**.
3. Haga clic en **Ver kardex**.
4. La tabla muestra, por cada movimiento: fecha, folio, tipo, sucursal, referencia, **Entrada**, **Salida** y **Saldo** acumulado. Al final se muestran los totales de entradas, salidas y el saldo final.

### 2.4 Alertas

Lista los productos cuyo **stock está por debajo del mínimo** establecido.

- Si no hay faltantes, verá el mensaje verde "No hay productos con stock bajo el mínimo".
- Si hay faltantes, cada renglón muestra: código, producto, categoría, unidad, **stock actual**, **mínimo**, **diferencia** (cuánto falta) y la sucursal.
- El número de productos en alerta aparece en la etiqueta roja del encabezado.
- Haga clic en el nombre del producto para ir a su detalle.

Use esta vista para saber qué hay que comprar o traspasar.

---

## 13. Exportar e imprimir

### 3.1 Dónde están los botones

- **En Reportes:**
  - *Stock actual*: botones **Exportar XLSX** y **CSV** (arriba a la derecha).
  - *Movimientos*: botón **Exportar CSV** (arriba a la derecha). La exportación respeta los filtros aplicados.
- **En catálogos** (productos y similares): busque el botón de exportar en la barra superior de cada listado.
- **Imprimir facturas:** dentro del detalle de una factura **Emitida** o **Pagada**, botón **Imprimir** (abre la versión para imprimir en una pestaña nueva).

### 3.2 Diferencia entre XLSX y CSV

- **XLSX (Excel)**: archivo de Excel listo para abrir. En el reporte de Stock incluye **grupos contraíbles** (+/−) que permiten desplegar u ocultar el detalle por sucursal dentro del propio Excel. Conserva mejor el formato.
- **CSV**: archivo de texto plano separado por comas. Es más sencillo y se abre en cualquier programa de hojas de cálculo, pero **no** trae grupos ni formato. Úselo si necesita pasar los datos a otro sistema.

### 3.3 Cómo abrir el XLSX en Excel

1. Haga clic en **Exportar XLSX**. El archivo se descargará (normalmente a la carpeta **Descargas**).
2. Ábralo con **doble clic**. Se abrirá en Excel.
3. En el reporte de Stock, los **grupos vienen contraídos** de inicio: verá solo la línea de cada producto.
4. Para ver el detalle por sucursal, haga clic en el botón **+** que aparece al lado izquierdo de las filas (en el margen de Excel). Para volver a ocultarlo, haga clic en **−**.
5. Los números **1** y **2** que aparecen arriba a la izquierda contraen o expanden todos los grupos a la vez.

---

## 14. Preguntas frecuentes

**¿Por qué al hacer una salida el stock muestra menos de lo que físicamente hay?**
Probablemente hay unidades **en tránsito**. Cuando se hace un traspaso entre sucursales, esas piezas salen de la sucursal de origen pero todavía no llegan a la de destino: durante ese tiempo aparecen como "en tránsito" y no se cuentan como disponibles en ninguna de las dos. En el reporte de **Stock actual**, expanda el producto (flecha `›`) para ver cuánto está en tránsito y en qué traspaso.

**¿Por qué un traspaso "regresó" stock?**
Si un traspaso se cancela, las piezas que habían salido **se devuelven** a la sucursal de origen. Lo mismo ocurre con las facturas: al **cancelar** una factura emitida o pagada, las refacciones regresan al inventario. Por eso puede ver que el stock "subió" sin haber hecho una entrada: corresponde a un movimiento revertido.

**No aparece la gráfica del dashboard, ¿qué hago?**
1. **Recargue la página** (tecla F5 o el botón de recargar del navegador).
2. Verifique que tenga **conexión a internet**: la gráfica usa una librería que se carga en línea, y sin internet no puede dibujarse.
3. Si después de recargar y con internet sigue sin verse, intente desde otro navegador o avise al administrador.

**Olvidé mi contraseña, ¿cómo la recupero?**
Las contraseñas **no se pueden recuperar** (no es posible verlas ni recordárselas). Solo un **administrador** puede **restablecerla**, asignándole una nueva contraseña con la que usted volverá a entrar. Solicítelo al administrador del sistema.
