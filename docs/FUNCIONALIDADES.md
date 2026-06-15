# Funcionalidades y Flujos — Inventario Taller

Comportamiento real por módulo (según el código).

---

## 1. Dashboard
KPIs (productos, entradas/salidas de hoy, traspasos en tránsito, alertas de stock), gráfica de **movimientos últimos 7 días** (entradas, salidas, traspasos y facturas — siempre muestra los 7 días), tabla de **stock bajo mínimo** y **últimas actividades**.

## 2. Productos
- Catálogo con código de barras, código alterno, categoría, unidad, proveedor, precios y stock mínimo. Foto del producto opcional (escáner de código integrado en el formulario).
- **Ficha de producto** (`detalle`): información, precios, **stock por sucursal** con columna **Stock mínimo** y estado; **últimos movimientos**.
  - Por cada sucursal hay botones **Entrada** y **Salida** que llevan al formulario con el producto **precargado** y la sucursal **preseleccionada**. La **Salida se deshabilita** si esa sucursal tiene 0 existencias.
- Lista con buscar, **Exportar CSV** e **Imprimir**.

## 3. Entradas
Alta de compra: sucursal, proveedor, referencia/Proneg. Captura por escáner o búsqueda: se **carga** el producto en los campos (código, cantidad, precio) y se agrega a la lista con **+Agregar**. Importación de **CFDI XML** para prellenar partidas. Al confirmar genera folio `ENT-...` y **suma stock**. Cancelación revierte el stock. Acepta precarga `?producto_id&sucursal_id` desde la ficha del producto.

## 4. Salidas
Sucursal (elegir primero), mecánico, servicio, folio Proneg. El campo **Stock disponible** muestra `disponible (actual − en tránsito)` de esa sucursal; **valida** antes de agregar. Opción **Forzar stock** (permiso `salidas.forzar`) deja stock en negativo (auditado). Folio `SAL-...`, **descuenta stock**; cancelación revierte. Precarga desde la ficha del producto.

## 5. Traspasos
- **Envío**: origen → destino; descuenta del origen y queda **en tránsito**.
- **Recepción parcial**: se captura la **cantidad recibida** por producto (no puede exceder lo enviado). Lo **no recibido regresa automáticamente** al stock de origen. El detalle muestra **Enviada / Recibida / Devuelta a origen**.
- **Cancelación**: devuelve todo al origen. Folios `TRP-...` (salida y entrada comparten secuencia).

## 6. Facturas
Estados **borrador → emitida → pagada** (o cancelada). Al elegir un **servicio** del catálogo, autocompleta la **mano de obra** (editable). **Descuento %** opcional (checkbox). Los **mecánicos se filtran por la sucursal** seleccionada. Al **emitir** genera una salida de inventario (descuenta stock). Folio `FAC-{sucursal}-{anio}-#####`. Impresión con datos de la empresa.

## 7. Reportes
- **Stock actual**: filas **expandibles por producto** (botón +/−) mostrando desglose por sucursal y **unidades en tránsito** (enlace al traspaso). Filtros (categoría, búsqueda). Exportar **XLSX** (con grupos contraíbles) y **CSV**.
- **Movimientos**: filtros por **sucursal, estado, producto** y rango de fechas; exportación.
- **Kardex**: historial de un producto.
- **Alertas**: productos bajo mínimo. Botón **Generar pedido**.
- **Pedido de reabastecimiento** (`pedido`): documento imprimible con **encabezado de datos de la empresa**, proveedor, stock actual/mínimo, **cantidad a pedir** (mínimo − actual) e importe estimado, con líneas de firma. Botón **Imprimir** y **Exportar XLSX** formateado.

## 8. Fotos
Subida de imágenes (`core/Upload.php`, ≤4 MB, jpg/png/webp/gif) para:
- **Usuarios** (incluye administradores): foto en alta/edición; aparece en el **navbar** del usuario logueado y en el listado.
- **Mecánicos**: foto en alta/edición y miniatura en el listado.
- **Sucursales**: foto + **ubicación** (latitud/longitud) con **mapa embebido** (OpenStreetMap) y enlace a Google Maps; miniatura y botón de mapa en el listado.
Si no hay foto se muestra un **avatar de iniciales** generado localmente (SVG); las sucursales usan un placeholder local. Vista previa instantánea al seleccionar el archivo.

## 9. Catálogos (proveedores, mecánicos, servicios, categorías, unidades)
CRUD con baja lógica. Servicios pueden asociar productos (insumos) mediante un buscador modal. Botones de **Imprimir** y **Exportar CSV**.

## 10. Administración
- **Sucursales**: CRUD + foto + ubicación en mapa.
- **Usuarios**: CRUD + rol + sucursal + foto + **campo "Cuenta activa"** (al editar, el admin puede activar/desactivar la cuenta; el admin no puede desactivarse a sí mismo).
- **Empresa**: datos fiscales (nombre, RFC, dirección, ciudad, CP, teléfono, email, pie de factura) usados en impresiones.
- **Respaldos (Backups)**: generar respaldo `.sql` completo, **historial/log** (tamaño, tablas, registros, usuario, estado), **descargar** y **eliminar**. Restauración: importar el `.sql` en phpMyAdmin.
- **Base de datos**: 
  - **Guardar datos actuales como ejemplo** (seed `.sql`).
  - **Cargar datos de ejemplo** (restaura el seed; confirmación escribiendo `CARGAR`) — ideal para presentaciones.
  - **Vaciar base de datos** (empezar de cero; confirmación `VACIAR`): borra todo conservando el **usuario admin actual** y los **catálogos base** (sucursales, categorías, unidades).
  - Muestra el conteo de registros por tabla.

## 11. Seguridad y trazabilidad
Login con sesión segura y CSRF; permisos por rol; **auditoría** de acciones; cabeceras anti-caché; cookies `HttpOnly`/`SameSite`. Las carpetas `uploads/`, `backups/` y `data/` tienen protección.

**Sidebar condicional**: cada enlace del menú lateral se renderiza únicamente si `Auth::tienePermiso()` lo permite; los roles no ven opciones que no pueden usar.

**Impersonación de usuarios** (solo admin): el administrador puede asumir temporalmente la sesión de cualquier usuario no-admin desde "Usar como…" en el navbar. La sesión original se guarda en variables de sesión con prefijo `_imp_*`; el flujo de restauración es `Auth::terminarImpersonacion()`. Una barra naranja fija indica el estado de impersonación. No se puede impersonar a otro admin ni iniciar una impersonación mientras otra está activa.
