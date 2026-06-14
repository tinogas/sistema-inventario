# Esquema de Base de Datos — Sistema de Inventario (Taller de Muelles y Suspensiones)

> Documentación generada a partir de los scripts y modelos reales del proyecto:
> - `public_html/inventario/install.sql` (instalación base, MySQL 8.0)
> - `public_html/inventario/migrate_facturas.sql` (módulo de facturación)
> - `public_html/inventario/modules/facturas/FacturaModel.php` (migración en runtime de `descuento_pct`)
> - `public_html/inventario/modules/empresa/EmpresaModel.php` (tabla `empresa` clave/valor)
>
> Motor: **InnoDB**. Charset: **utf8mb4** / Collation: **utf8mb4_unicode_ci** (salvo `empresa`, ver nota).
> Todas las tablas usan `CREATE TABLE IF NOT EXISTS`.

---

## 1. Resumen de tablas

| Categoría | Tablas |
|---|---|
| Catálogos base | `sucursales`, `categorias`, `unidades`, `proveedores`, `usuarios`, `mecanicos`, `servicios` |
| Productos | `productos`, `servicios_productos` |
| Stock | `stock_sucursal` |
| Movimientos de inventario | `movimientos`, `movimientos_detalle` |
| Traspasos | `traspasos` |
| Auditoría | `auditoria` |
| Facturación (migración) | `facturas`, `facturas_detalle`, `facturas_folios` |
| Configuración (runtime) | `empresa` (creada por `EmpresaModel`) |

> Nota: `facturas` recibe además la columna `descuento_pct` mediante un `ALTER TABLE` ejecutado en tiempo de ejecución por el constructor de `FacturaModel` (ver sección 5).

---

## 2. Catálogos base

### `sucursales`

| Columna | Tipo | Nulabilidad | Default | Llaves / Notas |
|---|---|---|---|---|
| `id` | TINYINT UNSIGNED | NOT NULL | AUTO_INCREMENT | **PK** |
| `nombre` | VARCHAR(100) | NOT NULL | — | |
| `ciudad` | VARCHAR(80) | NOT NULL | — | |
| `direccion` | VARCHAR(255) | NULL | — | |
| `telefono` | VARCHAR(20) | NULL | — | |
| `activa` | TINYINT(1) | NOT NULL | 1 | bandera booleana |
| `created_at` | DATETIME | NOT NULL | CURRENT_TIMESTAMP | |

**Datos iniciales:** `('Sucursal Principal', 'Ciudad Principal', ...)` y `('Sucursal Norte', 'Ciudad Norte', ...)`.

---

### `categorias`

| Columna | Tipo | Nulabilidad | Default | Llaves / Notas |
|---|---|---|---|---|
| `id` | SMALLINT UNSIGNED | NOT NULL | AUTO_INCREMENT | **PK** |
| `nombre` | VARCHAR(80) | NOT NULL | — | **UNIQUE** `uq_cat_nombre` |
| `descripcion` | VARCHAR(255) | NULL | — | |
| `activa` | TINYINT(1) | NOT NULL | 1 | |

**Datos iniciales:** Muelles, Amortiguadores, Bujes y silentblocks, Tornillería, Aceites y lubricantes, Consumibles, Refacciones generales.

---

### `unidades`

| Columna | Tipo | Nulabilidad | Default | Llaves / Notas |
|---|---|---|---|---|
| `id` | TINYINT UNSIGNED | NOT NULL | AUTO_INCREMENT | **PK** |
| `clave` | VARCHAR(10) | NOT NULL | — | **UNIQUE** `uq_uni_clave` |
| `nombre` | VARCHAR(40) | NOT NULL | — | |

**Datos iniciales:** PZA (Pieza), KIT (Kit), PAR (Par), JGO (Juego), LT (Litro), KG (Kilogramo), MT (Metro), CJA (Caja).

---

### `proveedores`

| Columna | Tipo | Nulabilidad | Default | Llaves / Notas |
|---|---|---|---|---|
| `id` | INT UNSIGNED | NOT NULL | AUTO_INCREMENT | **PK** |
| `razon_social` | VARCHAR(200) | NOT NULL | — | |
| `rfc` | VARCHAR(15) | NULL | — | **KEY** `idx_prov_rfc` |
| `contacto` | VARCHAR(120) | NULL | — | |
| `telefono` | VARCHAR(20) | NULL | — | |
| `email` | VARCHAR(150) | NULL | — | |
| `notas` | TEXT | NULL | — | |
| `activo` | TINYINT(1) | NOT NULL | 1 | |
| `created_at` | DATETIME | NOT NULL | CURRENT_TIMESTAMP | |

---

### `usuarios`

| Columna | Tipo | Nulabilidad | Default | Llaves / Notas |
|---|---|---|---|---|
| `id` | INT UNSIGNED | NOT NULL | AUTO_INCREMENT | **PK** |
| `nombre` | VARCHAR(120) | NOT NULL | — | |
| `email` | VARCHAR(150) | NOT NULL | — | **UNIQUE** `uq_usr_email` |
| `password_hash` | VARCHAR(255) | NOT NULL | — | |
| `rol` | ENUM('admin','almacenista','consulta') | NOT NULL | 'consulta' | |
| `sucursal_id` | TINYINT UNSIGNED | NULL | — | **FK** → `sucursales(id)`; **KEY** `idx_usr_sucursal` |
| `activo` | TINYINT(1) | NOT NULL | 1 | |
| `ultimo_acceso` | DATETIME | NULL | — | |
| `created_at` | DATETIME | NOT NULL | CURRENT_TIMESTAMP | |

**FK:** `fk_usr_suc` → `sucursales(id)` `ON UPDATE CASCADE ON DELETE SET NULL`.

> El usuario administrador no se crea en el SQL; se genera mediante `setup.php` (ver README).

---

### `mecanicos`

| Columna | Tipo | Nulabilidad | Default | Llaves / Notas |
|---|---|---|---|---|
| `id` | INT UNSIGNED | NOT NULL | AUTO_INCREMENT | **PK** |
| `nombre` | VARCHAR(120) | NOT NULL | — | |
| `sucursal_id` | TINYINT UNSIGNED | NOT NULL | — | **FK** → `sucursales(id)`; **KEY** `idx_mec_sucursal` |
| `telefono` | VARCHAR(20) | NULL | — | |
| `activo` | TINYINT(1) | NOT NULL | 1 | |
| `created_at` | DATETIME | NOT NULL | CURRENT_TIMESTAMP | |

**FK:** `fk_mec_suc` → `sucursales(id)` (sin acciones referenciales explícitas; RESTRICT por defecto).

---

### `servicios`

| Columna | Tipo | Nulabilidad | Default | Llaves / Notas |
|---|---|---|---|---|
| `id` | SMALLINT UNSIGNED | NOT NULL | AUTO_INCREMENT | **PK** |
| `nombre` | VARCHAR(120) | NOT NULL | — | |
| `descripcion` | TEXT | NULL | — | |
| `precio` | DECIMAL(12,2) | NOT NULL | 0.00 | |
| `activo` | TINYINT(1) | NOT NULL | 1 | |

---

## 3. Productos

### `productos`

| Columna | Tipo | Nulabilidad | Default | Llaves / Notas |
|---|---|---|---|---|
| `id` | INT UNSIGNED | NOT NULL | AUTO_INCREMENT | **PK** |
| `codigo` | VARCHAR(60) | NOT NULL | — | **UNIQUE** `uq_prod_codigo` |
| `codigo_alterno` | VARCHAR(60) | NULL | — | **KEY** `idx_prod_codigo_alt` |
| `nombre` | VARCHAR(200) | NOT NULL | — | **KEY** `idx_prod_nombre` |
| `descripcion` | TEXT | NULL | — | |
| `categoria_id` | SMALLINT UNSIGNED | NULL | — | **FK** → `categorias(id)`; **KEY** `idx_prod_categoria` |
| `unidad_id` | TINYINT UNSIGNED | NOT NULL | 1 | **FK** → `unidades(id)` |
| `proveedor_id` | INT UNSIGNED | NULL | — | **FK** → `proveedores(id)` |
| `precio_costo` | DECIMAL(12,2) | NOT NULL | 0.00 | |
| `precio_venta` | DECIMAL(12,2) | NOT NULL | 0.00 | |
| `stock_minimo` | DECIMAL(10,3) | NOT NULL | 1.000 | umbral para alertas |
| `activo` | TINYINT(1) | NOT NULL | 1 | |
| `created_at` | DATETIME | NOT NULL | CURRENT_TIMESTAMP | |
| `updated_at` | DATETIME | NOT NULL | CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | se actualiza solo |

**FKs:**
- `fk_prod_cat` → `categorias(id)` `ON UPDATE CASCADE ON DELETE SET NULL`
- `fk_prod_uni` → `unidades(id)` `ON UPDATE CASCADE`
- `fk_prod_prov` → `proveedores(id)` `ON UPDATE CASCADE ON DELETE SET NULL`

---

### `servicios_productos` (tabla puente N:M — receta/explosión de materiales del servicio)

| Columna | Tipo | Nulabilidad | Default | Llaves / Notas |
|---|---|---|---|---|
| `servicio_id` | SMALLINT UNSIGNED | NOT NULL | — | **PK compuesta** + **FK** → `servicios(id)` |
| `producto_id` | INT UNSIGNED | NOT NULL | — | **PK compuesta** + **FK** → `productos(id)` |
| `cantidad` | DECIMAL(10,3) | NOT NULL | 1.000 | |

**PK:** `(servicio_id, producto_id)`.
**FKs:**
- `fk_sp_ser` → `servicios(id)` `ON DELETE CASCADE`
- `fk_sp_pro` → `productos(id)` `ON DELETE CASCADE`

---

## 4. Stock por sucursal

### `stock_sucursal`

| Columna | Tipo | Nulabilidad | Default | Llaves / Notas |
|---|---|---|---|---|
| `producto_id` | INT UNSIGNED | NOT NULL | — | **PK compuesta** + **FK** → `productos(id)` |
| `sucursal_id` | TINYINT UNSIGNED | NOT NULL | — | **PK compuesta** + **FK** → `sucursales(id)` |
| `cantidad` | DECIMAL(10,3) | NOT NULL | 0.000 | existencia actual |
| `updated_at` | DATETIME | NOT NULL | CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | |

**PK:** `(producto_id, sucursal_id)`.
**FKs:**
- `fk_ss_prod` → `productos(id)` `ON DELETE CASCADE`
- `fk_ss_suc` → `sucursales(id)`

> **Modelo de stock por sucursal:** la existencia NO se guarda en `productos`. Cada combinación producto+sucursal tiene exactamente una fila (garantizado por la PK compuesta). Las existencias se actualizan con `INSERT ... ON DUPLICATE KEY UPDATE cantidad = cantidad + VALUES(cantidad)` (patrón "upsert" visible en `FacturaModel::cancelar`, que revierte la salida sumando de vuelta al stock). Así, una entrada suma, una salida resta, y un traspaso resta en la sucursal origen y suma en la destino.

---

## 5. Movimientos de inventario

### `movimientos` (encabezado)

| Columna | Tipo | Nulabilidad | Default | Llaves / Notas |
|---|---|---|---|---|
| `id` | INT UNSIGNED | NOT NULL | AUTO_INCREMENT | **PK** |
| `tipo` | ENUM('entrada','salida','traspaso_salida','traspaso_entrada','ajuste') | NOT NULL | — | **KEY** `idx_mov_tipo_fecha (tipo, created_at)` |
| `folio` | VARCHAR(30) | NOT NULL | — | **UNIQUE** `uq_mov_folio` |
| `sucursal_id` | TINYINT UNSIGNED | NOT NULL | — | sucursal origen; **FK**; **KEY** `idx_mov_sucursal` |
| `sucursal_dest_id` | TINYINT UNSIGNED | NULL | — | sucursal destino (traspasos); **FK** |
| `proveedor_id` | INT UNSIGNED | NULL | — | **FK** → `proveedores(id)` |
| `mecanico_id` | INT UNSIGNED | NULL | — | **FK** → `mecanicos(id)`; **KEY** `idx_mov_mecanico` |
| `servicio_id` | SMALLINT UNSIGNED | NULL | — | **FK** → `servicios(id)` |
| `referencia_factura` | VARCHAR(80) | NULL | — | folio de factura asociada |
| `uuid_cfdi` | CHAR(36) | NULL | — | UUID del CFDI; **KEY** `idx_mov_uuid` |
| `notas` | TEXT | NULL | — | |
| `estado` | ENUM('borrador','confirmado','cancelado') | NOT NULL | 'confirmado' | |
| `usuario_id` | INT UNSIGNED | NOT NULL | — | **FK** → `usuarios(id)` |
| `created_at` | DATETIME | NOT NULL | CURRENT_TIMESTAMP | |

**FKs:**
- `fk_mov_suc1` → `sucursales(id)` (origen)
- `fk_mov_suc2` → `sucursales(id)` (destino)
- `fk_mov_prov` → `proveedores(id)`
- `fk_mov_mec` → `mecanicos(id)`
- `fk_mov_ser` → `servicios(id)`
- `fk_mov_usr` → `usuarios(id)`

---

### `movimientos_detalle` (partidas)

| Columna | Tipo | Nulabilidad | Default | Llaves / Notas |
|---|---|---|---|---|
| `id` | INT UNSIGNED | NOT NULL | AUTO_INCREMENT | **PK** |
| `movimiento_id` | INT UNSIGNED | NOT NULL | — | **FK** → `movimientos(id)`; **KEY** `idx_det_mov` |
| `producto_id` | INT UNSIGNED | NOT NULL | — | **FK** → `productos(id)`; **KEY** `idx_det_prod` |
| `cantidad` | DECIMAL(10,3) | NOT NULL | — | sin default |
| `precio_unitario` | DECIMAL(12,2) | NOT NULL | 0.00 | |
| `numero_serie` | VARCHAR(80) | NULL | — | |
| `lote` | VARCHAR(40) | NULL | — | |
| `notas` | VARCHAR(255) | NULL | — | |

**FKs:**
- `fk_det_mov` → `movimientos(id)` `ON DELETE CASCADE`
- `fk_det_prod` → `productos(id)`

---

## 6. Traspasos

### `traspasos`

| Columna | Tipo | Nulabilidad | Default | Llaves / Notas |
|---|---|---|---|---|
| `id` | INT UNSIGNED | NOT NULL | AUTO_INCREMENT | **PK** |
| `movimiento_salida_id` | INT UNSIGNED | NOT NULL | — | **UNIQUE** `uq_tr_salida`; **FK** → `movimientos(id)` |
| `movimiento_entrada_id` | INT UNSIGNED | NULL | — | **UNIQUE** `uq_tr_entrada`; **FK** → `movimientos(id)` |
| `estado` | ENUM('en_transito','recibido','cancelado') | NOT NULL | 'en_transito' | |
| `fecha_envio` | DATETIME | NOT NULL | CURRENT_TIMESTAMP | |
| `fecha_recepcion` | DATETIME | NULL | — | |

**FKs:**
- `fk_tr_sal` → `movimientos(id)` (movimiento de salida del traspaso)
- `fk_tr_ent` → `movimientos(id)` (movimiento de entrada del traspaso)

> Un traspaso enlaza **dos** movimientos: uno de `tipo='traspaso_salida'` (en la sucursal origen) y, al recibirse, uno de `tipo='traspaso_entrada'` (en la destino). Ambos enlaces son **UNIQUE**, así que un mismo movimiento no puede pertenecer a dos traspasos. La entrada es nullable porque el traspaso arranca `en_transito` sin entrada confirmada.

---

## 7. Auditoría

### `auditoria`

| Columna | Tipo | Nulabilidad | Default | Llaves / Notas |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | NOT NULL | AUTO_INCREMENT | **PK** |
| `usuario_id` | INT UNSIGNED | NULL | — | **KEY** `idx_aud_usuario` (sin FK declarada) |
| `accion` | VARCHAR(80) | NOT NULL | — | **KEY** `idx_aud_accion` |
| `tabla_ref` | VARCHAR(60) | NULL | — | tabla afectada |
| `registro_id` | INT UNSIGNED | NULL | — | id del registro afectado |
| `ip` | VARCHAR(45) | NULL | — | soporta IPv6 |
| `descripcion` | TEXT | NULL | — | |
| `created_at` | DATETIME | NOT NULL | CURRENT_TIMESTAMP | **KEY** `idx_aud_fecha` |

> `auditoria` indexa `usuario_id` pero **no** declara FK (log histórico, debe sobrevivir al borrado de usuarios).

---

## 8. Módulo de facturación (`migrate_facturas.sql`)

### `facturas` (encabezado)

| Columna | Tipo | Nulabilidad | Default | Llaves / Notas |
|---|---|---|---|---|
| `id` | INT UNSIGNED | NOT NULL | AUTO_INCREMENT | **PK** |
| `folio` | VARCHAR(30) | NOT NULL | — | **UNIQUE** `uq_fac_folio` |
| `sucursal_id` | TINYINT UNSIGNED | NOT NULL | — | **FK** → `sucursales(id)`; **KEY** `idx_fac_sucursal` |
| `estado` | ENUM('borrador','emitida','pagada','cancelada') | NOT NULL | 'borrador' | **KEY** `idx_fac_estado` |
| `cliente_nombre` | VARCHAR(150) | NOT NULL | — | |
| `cliente_tel` | VARCHAR(25) | NULL | — | |
| `vh_marca` | VARCHAR(60) | NOT NULL | — | datos del vehículo |
| `vh_modelo` | VARCHAR(80) | NOT NULL | — | |
| `vh_anio` | SMALLINT UNSIGNED | NOT NULL | — | |
| `vh_placas` | VARCHAR(20) | NULL | — | |
| `mecanico_id` | INT UNSIGNED | NULL | — | **FK** → `mecanicos(id)` |
| `servicio_id` | SMALLINT UNSIGNED | NULL | — | **FK** → `servicios(id)` |
| `mano_obra` | DECIMAL(12,2) | NOT NULL | 0.00 | |
| `mano_obra_desc` | VARCHAR(200) | NULL | — | |
| `subtotal` | DECIMAL(12,2) | NOT NULL | 0.00 | calculado |
| `total` | DECIMAL(12,2) | NOT NULL | 0.00 | calculado (subtotal + mano de obra, menos descuento) |
| `movimiento_id` | INT UNSIGNED | NULL | — | **FK** → `movimientos(id)`; salida de inventario al emitir |
| `referencia_proneg` | VARCHAR(80) | NULL | — | referencia al sistema Proneg |
| `notas` | TEXT | NULL | — | |
| `usuario_id` | INT UNSIGNED | NOT NULL | — | **FK** → `usuarios(id)` |
| `fecha_emision` | DATETIME | NULL | — | se llena al emitir (`NOW()`) |
| `fecha_pago` | DATETIME | NULL | — | se llena al marcar pagada |
| `created_at` | DATETIME | NOT NULL | CURRENT_TIMESTAMP | **KEY** `idx_fac_fecha` |
| `updated_at` | DATETIME | NOT NULL | CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | |
| **`descuento_pct`** ⚙️ | DECIMAL(5,2) | NOT NULL | 0.00 | **Añadida en runtime** por `FacturaModel` (no está en el SQL) |

**FKs:**
- `fk_fac_suc` → `sucursales(id)`
- `fk_fac_mec` → `mecanicos(id)` `ON DELETE SET NULL`
- `fk_fac_ser` → `servicios(id)` `ON DELETE SET NULL`
- `fk_fac_mov` → `movimientos(id)` `ON DELETE SET NULL`
- `fk_fac_usr` → `usuarios(id)`

> ⚙️ **Columna `descuento_pct` (migración en runtime).** No existe en `migrate_facturas.sql`. El **constructor** de `FacturaModel` la agrega de forma idempotente:
> ```sql
> ALTER TABLE facturas ADD COLUMN descuento_pct DECIMAL(5,2) NOT NULL DEFAULT 0.00
> ```
> La sentencia se ejecuta dentro de un `try/catch (PDOException)`; si la columna ya existe, el error se ignora (compatible con MySQL y MariaDB). El porcentaje se valida en PHP al rango `0–100` y el total se calcula como `round((subtotal + mano_obra) * (1 - descuento_pct/100), 2)`.

---

### `facturas_detalle` (partidas de producto)

| Columna | Tipo | Nulabilidad | Default | Llaves / Notas |
|---|---|---|---|---|
| `id` | INT UNSIGNED | NOT NULL | AUTO_INCREMENT | **PK** |
| `factura_id` | INT UNSIGNED | NOT NULL | — | **FK** → `facturas(id)`; **KEY** `idx_fd_factura` |
| `producto_id` | INT UNSIGNED | NOT NULL | — | **FK** → `productos(id)`; **KEY** `idx_fd_producto` |
| `cantidad` | DECIMAL(10,3) | NOT NULL | — | sin default |
| `precio_unitario` | DECIMAL(12,2) | NOT NULL | 0.00 | |
| `notas` | VARCHAR(255) | NULL | — | |

**FKs:**
- `fk_fd_fac` → `facturas(id)` `ON DELETE CASCADE`
- `fk_fd_prod` → `productos(id)`

> Nota: el importe de cada partida (`cantidad * precio_unitario`) se calcula en la consulta `getDetalle`, no se almacena.

---

### `facturas_folios` (contador de folios por sucursal+año)

| Columna | Tipo | Nulabilidad | Default | Llaves / Notas |
|---|---|---|---|---|
| `sucursal_id` | TINYINT UNSIGNED | NOT NULL | — | **PK compuesta** + **FK** → `sucursales(id)` |
| `anio` | SMALLINT UNSIGNED | NOT NULL | — | **PK compuesta** |
| `ultimo` | INT UNSIGNED | NOT NULL | 0 | último consecutivo emitido |

**PK:** `(sucursal_id, anio)`.
**FK:** `fk_ff_suc` → `sucursales(id)`.

> Tabla auxiliar de numeración. Garantiza un consecutivo independiente por cada par sucursal+año (ver sección de folios).

---

## 9. Tabla `empresa` (clave/valor, creada por `EmpresaModel`)

No existe en ningún `.sql`. El constructor de `EmpresaModel` la crea en runtime (`ensureTable()`) y siembra las claves por defecto.

**DDL ejecutado:**
```sql
CREATE TABLE IF NOT EXISTS empresa (
    id    INT AUTO_INCREMENT,
    clave VARCHAR(60) NOT NULL,
    valor TEXT,
    PRIMARY KEY (id),
    UNIQUE KEY uq_empresa_clave (clave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
```

| Columna | Tipo | Nulabilidad | Default | Llaves / Notas |
|---|---|---|---|---|
| `id` | INT | (implícito NOT NULL) | AUTO_INCREMENT | **PK** |
| `clave` | VARCHAR(60) | NOT NULL | — | **UNIQUE** `uq_empresa_clave` |
| `valor` | TEXT | NULL (implícito) | — | |

> A diferencia del resto, este DDL **no especifica COLLATE** (solo `CHARSET=utf8mb4`), por lo que toma la collation por defecto de utf8mb4 del servidor.

**Patrón clave/valor:** cada fila es un par configurable. Tras crear la tabla se insertan las claves por defecto con `INSERT IGNORE` (no sobreescribe si ya existen):

| Clave | Valor por defecto |
|---|---|
| `nombre` | `Taller Muelles Sonora` |
| `rfc` | (vacío) |
| `direccion` | (vacío) |
| `ciudad` | `Hermosillo` |
| `cp` | (vacío) |
| `telefono` | (vacío) |
| `email` | (vacío) |
| `logo_path` | (vacío) |
| `pie_factura` | (vacío) |

> `get()` devuelve un mapa `['clave' => 'valor']`. `guardar()` usa `INSERT ... ON DUPLICATE KEY UPDATE valor = VALUES(valor)` y **solo** persiste claves presentes en la lista de defaults (lista blanca contra inyección de datos arbitrarios).

---

## 10. Relaciones (FKs) y diagrama

### Resumen de claves foráneas

| Tabla origen | Columna(s) | → Tabla destino | Acciones referenciales |
|---|---|---|---|
| `usuarios` | `sucursal_id` | `sucursales(id)` | ON UPDATE CASCADE / ON DELETE SET NULL |
| `mecanicos` | `sucursal_id` | `sucursales(id)` | — (RESTRICT) |
| `productos` | `categoria_id` | `categorias(id)` | ON UPDATE CASCADE / ON DELETE SET NULL |
| `productos` | `unidad_id` | `unidades(id)` | ON UPDATE CASCADE |
| `productos` | `proveedor_id` | `proveedores(id)` | ON UPDATE CASCADE / ON DELETE SET NULL |
| `servicios_productos` | `servicio_id` | `servicios(id)` | ON DELETE CASCADE |
| `servicios_productos` | `producto_id` | `productos(id)` | ON DELETE CASCADE |
| `stock_sucursal` | `producto_id` | `productos(id)` | ON DELETE CASCADE |
| `stock_sucursal` | `sucursal_id` | `sucursales(id)` | — |
| `movimientos` | `sucursal_id` | `sucursales(id)` | — |
| `movimientos` | `sucursal_dest_id` | `sucursales(id)` | — |
| `movimientos` | `proveedor_id` | `proveedores(id)` | — |
| `movimientos` | `mecanico_id` | `mecanicos(id)` | — |
| `movimientos` | `servicio_id` | `servicios(id)` | — |
| `movimientos` | `usuario_id` | `usuarios(id)` | — |
| `movimientos_detalle` | `movimiento_id` | `movimientos(id)` | ON DELETE CASCADE |
| `movimientos_detalle` | `producto_id` | `productos(id)` | — |
| `traspasos` | `movimiento_salida_id` | `movimientos(id)` | — (UNIQUE) |
| `traspasos` | `movimiento_entrada_id` | `movimientos(id)` | — (UNIQUE) |
| `facturas` | `sucursal_id` | `sucursales(id)` | — |
| `facturas` | `mecanico_id` | `mecanicos(id)` | ON DELETE SET NULL |
| `facturas` | `servicio_id` | `servicios(id)` | ON DELETE SET NULL |
| `facturas` | `movimiento_id` | `movimientos(id)` | ON DELETE SET NULL |
| `facturas` | `usuario_id` | `usuarios(id)` | — |
| `facturas_detalle` | `factura_id` | `facturas(id)` | ON DELETE CASCADE |
| `facturas_detalle` | `producto_id` | `productos(id)` | — |
| `facturas_folios` | `sucursal_id` | `sucursales(id)` | — |

> `auditoria.usuario_id` y la tabla `empresa` **no** participan en FKs.

### Diagrama de relaciones (ASCII)

```
                                  ┌──────────────┐
                                  │  sucursales  │ (catálogo raíz)
                                  └──────┬───────┘
        ┌────────────┬────────────┬──────┼───────────┬─────────────┬──────────────┐
        │            │            │      │           │             │              │
   usuarios     mecanicos   stock_sucursal│      movimientos    facturas    facturas_folios
        │            │            │      │      (orig/dest)        │         (PK: suc+año)
        │            │            │      │           │             │
        │            │            │  productos       │             │
        │            │            │      ▲           │             │
        │            │            └──────┤           │             │
        │            │                   │           │             │
        │            │            ┌───────┴────────┐ │             │
        │            │            │                │ │             │
        │            │      categorias        unidades             │
        │            │      proveedores ──────────┘                │
        │            │                                             │
        │            │                                             │
   ── catálogos / FK directas a sucursales ──                      │
                                                                   │
  PRODUCTOS y receta de servicio:                                  │
        servicios ──< servicios_productos >── productos            │
                                                                   │
  MOVIMIENTOS (núcleo de inventario):                              │
        movimientos 1 ──< movimientos_detalle (CASCADE) >── productos
        movimientos ── (origen) sucursales
        movimientos ── (destino) sucursales        ← sólo traspasos
        movimientos ── proveedores / mecanicos / servicios / usuarios
                                                                   │
  TRASPASOS (enlazan 2 movimientos):                               │
        traspasos.movimiento_salida_id  ──► movimientos (UNIQUE)   │
        traspasos.movimiento_entrada_id ──► movimientos (UNIQUE)   │
                                                                   │
  FACTURACIÓN:                                                     │
        facturas 1 ──< facturas_detalle (CASCADE) >── productos    │
        facturas ── sucursales / mecanicos / servicios / usuarios  │
        facturas.movimiento_id ──► movimientos  (salida al emitir) │
        facturas_folios ── sucursales  ◄───────────────────────────┘  (numera folios)

  AUDITORÍA / CONFIG (sin FK):
        auditoria  (log; indexa usuario_id, sin FK)
        empresa    (tabla clave/valor, creada por EmpresaModel)
```

Notación: `1 ──< N` = uno a muchos; `>── ` = muchos a uno; `──►` = referencia 1:1 (UNIQUE).

---

## 11. Notas de diseño

### Folios

- **`movimientos.folio`** — `VARCHAR(30) NOT NULL`, con índice **UNIQUE** `uq_mov_folio`: cada movimiento de inventario tiene folio único en todo el sistema.
- **`facturas.folio`** — `VARCHAR(30) NOT NULL`, **UNIQUE** `uq_fac_folio`. Se genera en `FacturaModel::generarFolioFactura()` con el formato:
  ```
  FAC-{sucursal_id}-{anio}-{consecutivo a 5 dígitos}
  ```
  Ejemplo: `FAC-2-2026-00007` (sucursal 2, año 2026, folio 7). En PHP: `sprintf('FAC-%d-%d-%05d', $sucursal_id, $anio, $ultimo)`.
- **Atomicidad del consecutivo:** el contador se incrementa con
  ```sql
  INSERT INTO facturas_folios (sucursal_id, anio, ultimo)
  VALUES (:sid, :anio, 1)
  ON DUPLICATE KEY UPDATE ultimo = LAST_INSERT_ID(ultimo + 1)
  ```
  y luego se lee con `SELECT LAST_INSERT_ID()`. Como `facturas_folios` tiene PK `(sucursal_id, anio)`, el contador es **independiente por sucursal y por año**. Incluir la sucursal en el folio es obligatorio: sin ella, dos sucursales generarían el mismo número y colisionarían contra el UNIQUE de `facturas.folio` (documentado en el propio código del modelo).

### Enums

| Tabla.columna | Valores | Default |
|---|---|---|
| `usuarios.rol` | `admin`, `almacenista`, `consulta` | `consulta` |
| `movimientos.tipo` | `entrada`, `salida`, `traspaso_salida`, `traspaso_entrada`, `ajuste` | (sin default) |
| `movimientos.estado` | `borrador`, `confirmado`, `cancelado` | `confirmado` |
| `traspasos.estado` | `en_transito`, `recibido`, `cancelado` | `en_transito` |
| `facturas.estado` | `borrador`, `emitida`, `pagada`, `cancelada` | `borrador` |

### Ciclo de vida de una factura (según `FacturaModel`)

`borrador` → `emitida` → `pagada`, o bien → `cancelada`.

- **Guardar/editar:** solo es posible en estado `borrador` (`UPDATE ... WHERE id=:fid AND estado='borrador'`). Al editar se borran y reinsertan todas las partidas.
- **Emitir** (`emitir`): solo desde `borrador`. Crea un movimiento de **salida** de inventario (vía `SalidaModel::confirmar`), guarda su id en `facturas.movimiento_id`, fija `estado='emitida'` y `fecha_emision=NOW()`.
- **Pagar** (`marcarPagada`): solo desde `emitida`; fija `estado='pagada'` y `fecha_pago=NOW()`.
- **Cancelar** (`cancelar`): si está en `borrador`, solo cambia el estado. Si tiene `movimiento_id` (ya emitida), **revierte el stock** sumando de vuelta cada partida a `stock_sucursal` (`ON DUPLICATE KEY UPDATE cantidad = cantidad + VALUES(cantidad)`) y marca el movimiento como `cancelado`.

### Modelado del stock por sucursal (resumen)

- El stock vive exclusivamente en **`stock_sucursal`** con **PK compuesta `(producto_id, sucursal_id)`**: una fila única por producto y sucursal.
- Las existencias se modifican con upsert (`INSERT ... ON DUPLICATE KEY UPDATE cantidad = cantidad ± VALUES(cantidad)`).
- `productos.stock_minimo` define el umbral de reorden por producto (global, no por sucursal).
- Los traspasos mueven existencias entre sucursales mediante el par `movimientos` (`traspaso_salida` en origen + `traspaso_entrada` en destino), enlazados por la tabla `traspasos`.

### Tipos y convenciones generales

- Cantidades: `DECIMAL(10,3)` (soporta fracciones, p. ej. litros/metros). Importes y precios: `DECIMAL(12,2)`. Porcentaje de descuento: `DECIMAL(5,2)`.
- Banderas booleanas: `TINYINT(1)` con default `1` (`activa`/`activo`).
- Marcas de tiempo: `created_at` con `DEFAULT CURRENT_TIMESTAMP`; `updated_at` con `DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` en `productos`, `stock_sucursal` y `facturas`.
- `uuid_cfdi CHAR(36)` en `movimientos` para asociar el UUID del CFDI; `referencia_factura` y `referencia_proneg` enlazan con documentos externos (factura fiscal y sistema Proneg).
