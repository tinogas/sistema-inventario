# Esquema de Base de Datos — Inventario Taller

Base `inventario_taller`. Motor **InnoDB**, charset **utf8mb4** (`utf8mb4_unicode_ci`). Las tablas/columnas se definen en `install.sql`, `migrate_facturas.sql` y, varias, en **migraciones de runtime** dentro del constructor de los modelos (se autocrean con `CREATE TABLE IF NOT EXISTS` o `ALTER TABLE ... ADD COLUMN` en `try/catch`).

---

## 1. Tablas por categoría

| Categoría | Tablas |
|-----------|--------|
| Catálogos base | `sucursales`, `categorias`, `unidades`, `proveedores`, `usuarios`, `mecanicos`, `servicios` |
| Productos | `productos`, `servicios_productos` |
| Stock | `stock_sucursal` |
| Movimientos | `movimientos`, `movimientos_detalle` |
| Traspasos | `traspasos` |
| Facturación | `facturas`, `facturas_detalle`, `facturas_folios` |
| Auditoría | `auditoria` |
| Configuración (runtime) | `empresa` |
| Respaldos (runtime) | `backups_log` |

---

## 2. Estructura (resumen de columnas)

**sucursales**: `id` (TINYINT PK), `nombre`, `ciudad`, `direccion`, `telefono`, `activa`, `created_at`, **`foto`**, **`latitud` DECIMAL(10,7)**, **`longitud` DECIMAL(10,7)** *(las 3 últimas autocreadas por `SucursalModel`)*.

**categorias**: `id` (SMALLINT PK), `nombre` (UNIQUE), `descripcion`, `activa`.

**unidades**: `id` (TINYINT PK), `clave` (UNIQUE), `nombre`.

**proveedores**: `id` (INT PK), `razon_social`, `rfc`, `contacto`, `telefono`, `email`, `notas`, `activo`, `created_at`.

**usuarios**: `id` (INT PK), `nombre`, `email` (UNIQUE), `password_hash` (bcrypt), `rol` ENUM(admin,almacenista,consulta), `sucursal_id` (FK), `activo`, `ultimo_acceso`, `created_at`, **`foto`** *(autocreada por `UsuarioModel`)*.

**mecanicos**: `id` (INT PK), `nombre`, `sucursal_id` (FK), `telefono`, `activo`, `created_at`, **`foto`** *(autocreada por `MecanicoModel`)*.

**servicios**: `id` (SMALLINT PK), `nombre`, `descripcion`, `precio` DECIMAL(12,2), `activo`.

**productos**: `id` (INT PK), `codigo` (UNIQUE), `codigo_alterno`, `nombre`, `descripcion`, `categoria_id` (FK), `unidad_id` (FK), `proveedor_id` (FK), `precio_costo`, `precio_venta`, `stock_minimo` DECIMAL(10,3), `activo`, `created_at`, `updated_at`.

**servicios_productos**: PK (`servicio_id`, `producto_id`), `cantidad`. FKs CASCADE.

**stock_sucursal**: PK (`producto_id`, `sucursal_id`), `cantidad` DECIMAL(10,3), `updated_at`.

**movimientos**: `id` (INT PK), `tipo` ENUM(entrada,salida,traspaso_salida,traspaso_entrada,ajuste), `folio` (UNIQUE), `sucursal_id`, `sucursal_dest_id`, `proveedor_id`, `mecanico_id`, `servicio_id`, `referencia_factura`, `uuid_cfdi`, `notas`, `estado` ENUM(borrador,confirmado,cancelado), `usuario_id`, `created_at`.

**movimientos_detalle**: `id` (INT PK), `movimiento_id` (FK CASCADE), `producto_id` (FK), `cantidad`, `precio_unitario`, `numero_serie`, `lote`, `notas`.

**traspasos**: `id` (INT PK), `movimiento_salida_id` (UNIQUE, FK), `movimiento_entrada_id` (UNIQUE, FK), `estado` ENUM(en_transito,recibido,cancelado), `fecha_envio`, `fecha_recepcion`.

**auditoria**: `id` (BIGINT PK), `usuario_id`, `accion`, `tabla_ref`, `registro_id`, `ip`, `descripcion`, `created_at`.

**facturas**: `id` (INT PK), `folio` (UNIQUE), `sucursal_id`, `estado` ENUM(borrador,emitida,pagada,cancelada), datos cliente/vehículo, `mecanico_id`, `servicio_id`, `mano_obra`, `mano_obra_desc`, `subtotal`, `total`, `movimiento_id`, `referencia_proneg`, `notas`, `usuario_id`, `fecha_emision`, `fecha_pago`, `created_at`, `updated_at`, **`descuento_pct` DECIMAL(5,2)** *(autocreada por `FacturaModel`)*.

**facturas_detalle**: `id` (INT PK), `factura_id` (FK CASCADE), `producto_id`, `cantidad`, `precio_unitario`, `notas`.

**facturas_folios**: PK (`sucursal_id`, `anio`), `ultimo`.

**empresa** *(autocreada por `EmpresaModel`)*: `id` (PK), `clave` (UNIQUE), `valor`. Claves por defecto: nombre, rfc, direccion, ciudad, cp, telefono, email, logo_path, pie_factura.

**backups_log** *(autocreada por `BackupModel`)*: `id` (PK), `archivo`, `tamano_bytes`, `num_tablas`, `num_registros`, `usuario_id`, `usuario_nombre`, `estado` ENUM(completado,error), `notas`, `created_at`.

---

## 3. Columnas/tablas añadidas en runtime (migraciones defensivas)

No están en los scripts `.sql`; se aplican al instanciar el modelo (idempotente, ignora si ya existen):

| Estructura | Origen |
|------------|--------|
| `facturas.descuento_pct` | `FacturaModel` |
| `mecanicos.foto` | `MecanicoModel` |
| `usuarios.foto` | `UsuarioModel` |
| `sucursales.foto`, `.latitud`, `.longitud` | `SucursalModel` |
| tabla `empresa` | `EmpresaModel` |
| tabla `backups_log` | `BackupModel` |

> Un respaldo o el seed de ejemplo (vía `SHOW CREATE TABLE`) ya incluyen estas estructuras, porque para entonces existen físicamente.

---

## 4. Relaciones (FKs) — diagrama

```
sucursales ──< usuarios, mecanicos, stock_sucursal, movimientos(sucursal_id/dest), facturas, facturas_folios
categorias ──< productos        unidades ──< productos        proveedores ──< productos, movimientos
productos  ──< stock_sucursal, movimientos_detalle, facturas_detalle, servicios_productos
servicios  ──< servicios_productos, movimientos, facturas
movimientos ──< movimientos_detalle (CASCADE), traspasos (salida/entrada)
facturas   ──< facturas_detalle (CASCADE)
usuarios   ──< movimientos, facturas
Sin FK: auditoria, empresa, backups_log
```

---

## 5. Folios y enums

- **Movimientos** (`folio` UNIQUE): `ENT/SAL/TRP/AJU-AAAA-#####`. Traspaso salida+entrada comparten `TRP` y se cuentan juntos.
- **Facturas** (`folio` UNIQUE): `FAC-{sucursal}-{anio}-#####`, contador atómico en `facturas_folios`.
- **Enums**: usuarios.rol; movimientos.tipo/estado; traspasos.estado; facturas.estado; backups_log.estado.

---

## 6. Datos de ejemplo y respaldos

- **`data/seed_ejemplo.sql`**: snapshot completo (DROP/CREATE/INSERT) de los datos de demostración, generado y cargado por el módulo **Base de datos**.
- **`backups/*.sql`**: respaldos generados por el módulo **Respaldos** (no versionados; carpeta protegida).
