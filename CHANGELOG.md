# Changelog

Todas las mejoras y correcciones relevantes del sistema.

## 2026-06 — Impersonación de usuarios, UX por rol y correcciones de testeo

### Nuevas funcionalidades
- **Impersonación de usuarios** (solo admin): desde el menú de usuario del navbar, el administrador puede elegir "Usar como…" para operar el sistema con la sesión de cualquier almacenista o usuario consulta. Mientras está activa, una barra naranja fija indica el usuario impersonado y el rol. El botón "Volver Admin" (en la barra y en el dropdown) restaura la sesión original. Implementado con tres métodos en `Auth` (`estaImpersonando`, `iniciarImpersonacion`, `terminarImpersonacion`) y dos acciones en `AuthController` (`impersonar`, `terminarImpersonacion`).
- **Sidebar condicional por rol**: cada elemento del menú lateral aparece solo si el rol tiene el permiso correspondiente (`Auth::tienePermiso()`). El usuario *consulta* ve únicamente Dashboard, Productos y Reportes; el almacenista ve todo excepto la sección Administración.
- **Campo "Cuenta activa"** en la edición de usuarios: el administrador puede activar/desactivar una cuenta directamente desde el formulario (sin necesidad de usar la baja lógica). El admin no puede desactivarse a sí mismo.
- **Fecha en español**: el encabezado del Dashboard muestra la fecha en castellano ("Lunes, 16 de junio de 2026").

### Correcciones (testeo por rol)
- **Traspasos / almacenista**: la sucursal propia del almacenista aparece deshabilitada en el selector de destino (antes solo mostraba un mensaje al intentar seleccionarla). El campo "Stock disponible" es ahora visualmente no editable (`disabled`).
- **Reporte de stock / almacenista**: se ocultan el botón "Expandir todo" y los toggles individuales (+/−) porque el almacenista ya ve únicamente su sucursal y el desglose no añade información.
- **Movimientos / almacenista**: el filtro por sucursal muestra solo la del usuario (campo bloqueado con `hidden` + texto deshabilitado); el admin mantiene el selector completo.

---

## 2026-06 — Fotos, mantenimiento de BD y mejoras de operación

### Nuevas funcionalidades
- **Fotos**: usuarios (incl. administradores), mecánicos y sucursales. Avatar de iniciales local (SVG) cuando no hay foto; la foto del usuario aparece en el navbar. Vista previa instantánea al seleccionar el archivo.
- **Sucursales**: foto y **ubicación en mapa** (latitud/longitud) con mapa embebido (OpenStreetMap) y enlace a Google Maps.
- **Ficha de producto**: botones de **Entrada/Salida por sucursal** (con producto precargado y sucursal preseleccionada), columna **Stock mínimo**, y **Salida deshabilitada** si la sucursal tiene 0 existencias.
- **Pedido de reabastecimiento** (desde Alertas): documento imprimible con encabezado de datos de la empresa, proveedor y cantidad a pedir; exportable a **Excel .xlsx**.
- **Módulo Respaldos (Backups)**: genera respaldos `.sql` completos en PHP puro, con historial/log, descarga protegida y eliminación.
- **Módulo Base de datos**: guardar un **seed de ejemplo** con los datos actuales, **cargarlo** (para presentaciones) y **vaciar** la base para empezar de cero (conserva admin y catálogos base).

### Correcciones
- Foto del admin aparecía en usuarios sin foto al editar: `render()` reescribe `$usuario` con el usuario logueado; el formulario ahora usa solo `$datos['foto']`.
- Cabeceras **anti-caché** en páginas dinámicas.

---

## 2026-06 — Auditoría, correcciones de inventario y funcionalidades base

Ronda de auditoría, correcciones de inventario y nuevas funcionalidades.

### Nuevas funcionalidades

- **Módulo Empresa**: mantenimiento de datos de la empresa (nombre, RFC, dirección, ciudad, CP, teléfono, email, pie de factura). Accesible solo para administradores. Tabla `empresa` (clave/valor) creada automáticamente.
- **Reporte de Stock expandible**: cada producto se muestra contraído con su stock total y se expande para ver el desglose por sucursal y las unidades **en tránsito** (con enlace al traspaso).
- **Exportación a XLSX**: el reporte de stock se exporta a `.xlsx` real (OOXML) con **filas agrupadas contraíbles** por producto/sucursal. Generado con un escritor propio (`core/XlsxWriter.php`) sin dependencias ni `ZipArchive`.
- **Exportación CSV e impresión** en los catálogos (productos, proveedores, mecánicos, servicios).
- **Filtros en el reporte de Movimientos**: por sucursal, por estado y por producto, además del rango de fechas.
- **Factura**:
  - La **mano de obra** se toma del catálogo de servicios al elegir el servicio (campo de costo **editable**).
  - **Descuento porcentual** opcional (checkbox + porcentaje) aplicado al total.
  - Los **mecánicos se filtran** según la sucursal seleccionada.
- **Traspaso con recepción parcial**: al recibir se captura la **cantidad recibida** por producto; lo no recibido **regresa automáticamente** al stock de la sucursal de origen. El detalle muestra **Enviada / Recibida / Devuelta a origen**.
- **Dashboard**: la gráfica de "Movimientos últimos 7 días" incluye entradas, salidas, **traspasos y facturas**, y siempre muestra los 7 días.

### Correcciones

- **Stock por sucursal en Salida/Factura**: `getSucursalId()` capturaba el selector de sucursal del **navbar** en lugar del formulario (colisión de `name="sucursal_id"`); ahora se limita a su propio formulario. El stock mostrado es el de la sucursal seleccionada (no la suma de todas) y descuenta lo que está en tránsito.
- **Stock disponible** = stock actual − unidades en tránsito; se valida antes de agregar el producto y se muestra coherente en la tabla.
- **Stock negativo**: la salida forzada de un producto sin existencias en la sucursal dejaba el stock en positivo; ahora queda correctamente negativo.
- **Transacciones anidadas** (`core/Model`): emitir una factura (que internamente genera una salida) fallaba con "There is already an active transaction". Se implementó anidación con contador estático compartido.
- **Folios**: se revirtió un `LOCK TABLES` que rompía las transacciones; los folios de traspaso (`TRP`) ya no colisionan entre salida y entrada; los folios de factura incluyen la sucursal (`FAC-{sucursal}-{año}-{n}`) para evitar choques entre sucursales.
- **Recepción de traspasos**: el alias de estado faltaba en `getById`, por lo que los botones de "Confirmar recepción"/"Cancelar" nunca aparecían; corregido.
- **Cerrar sesión**: el logout exige POST + CSRF (seguridad), pero el enlace del navbar era GET y no cerraba sesión; ahora es un formulario POST con token.
- **Dashboard**: la gráfica no se dibujaba porque el script corría antes de cargar Chart.js; se difiere a `DOMContentLoaded`, con mensaje de respaldo si la librería no carga.
- **Servicios / Productos**: el botón "+Agregar producto" y el escáner instanciaban el modal de Bootstrap antes de que la librería cargara; ahora se inicializan de forma diferida (`getOrCreateInstance`).
- **Flujo de captura** en Entradas/Salidas/Facturas/Traspasos: al buscar un producto se **carga** en los campos (código, cantidad, precio) y solo se agrega a la lista al pulsar **+Agregar** (antes se agregaba al instante de encontrarlo).
- **Campos de precio/número**: al enfocarlos se selecciona el contenido para sobrescribir el `0.00` sin tener que borrarlo.
- **Parámetros PDO duplicados** (`ProductoModel`): se corrigió el error `HY093` en las búsquedas (`listar`, `buscarPorCodigo`, `buscarSugerencias`).
- **Auditoría de seguridad**: escape de salida (XSS) en vistas, verificación de permisos faltantes, validación CSRF, endurecimiento de la importación de CFDI (XXE) y validaciones varias en controladores.
- **API `productos_buscar`**: para usuarios no administradores se ignora el `sucursal_id` recibido por URL y se fuerza su propia sucursal (evita ver stock de otra sucursal).

---

> Las nueve observaciones originales del README (stock expandible, exportación XLSX, filtros de movimientos, +Agregar en servicios, selección de precio, orden de captura en entrada/salida, validación de stock, exportación de catálogos y módulo de empresa) quedaron implementadas en esta ronda.
