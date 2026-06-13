-- ============================================================
-- Sistema de Inventario - Taller de Muelles y Suspensiones
-- Script de instalación MySQL 8.0
-- ============================================================

SET NAMES utf8mb4;
SET foreign_key_checks = 0;

-- ------------------------------------------------------------
-- CATÁLOGOS BASE
-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS sucursales (
    id          TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre      VARCHAR(100)     NOT NULL,
    ciudad      VARCHAR(80)      NOT NULL,
    direccion   VARCHAR(255)     NULL,
    telefono    VARCHAR(20)      NULL,
    activa      TINYINT(1)       NOT NULL DEFAULT 1,
    created_at  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categorias (
    id          SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre      VARCHAR(80)       NOT NULL,
    descripcion VARCHAR(255)      NULL,
    activa      TINYINT(1)        NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_cat_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS unidades (
    id      TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    clave   VARCHAR(10)      NOT NULL,
    nombre  VARCHAR(40)      NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_uni_clave (clave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS proveedores (
    id           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    razon_social VARCHAR(200)  NOT NULL,
    rfc          VARCHAR(15)   NULL,
    contacto     VARCHAR(120)  NULL,
    telefono     VARCHAR(20)   NULL,
    email        VARCHAR(150)  NULL,
    notas        TEXT          NULL,
    activo       TINYINT(1)    NOT NULL DEFAULT 1,
    created_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_prov_rfc (rfc)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS usuarios (
    id            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    nombre        VARCHAR(120)     NOT NULL,
    email         VARCHAR(150)     NOT NULL,
    password_hash VARCHAR(255)     NOT NULL,
    rol           ENUM('admin','almacenista','consulta') NOT NULL DEFAULT 'consulta',
    sucursal_id   TINYINT UNSIGNED NULL,
    activo        TINYINT(1)       NOT NULL DEFAULT 1,
    ultimo_acceso DATETIME         NULL,
    created_at    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_usr_email (email),
    KEY idx_usr_sucursal (sucursal_id),
    CONSTRAINT fk_usr_suc FOREIGN KEY (sucursal_id) REFERENCES sucursales(id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mecanicos (
    id          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    nombre      VARCHAR(120)     NOT NULL,
    sucursal_id TINYINT UNSIGNED NOT NULL,
    telefono    VARCHAR(20)      NULL,
    activo      TINYINT(1)       NOT NULL DEFAULT 1,
    created_at  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_mec_sucursal (sucursal_id),
    CONSTRAINT fk_mec_suc FOREIGN KEY (sucursal_id) REFERENCES sucursales(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS servicios (
    id          SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre      VARCHAR(120)      NOT NULL,
    descripcion TEXT              NULL,
    precio      DECIMAL(12,2)     NOT NULL DEFAULT 0.00,
    activo      TINYINT(1)        NOT NULL DEFAULT 1,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- PRODUCTOS
-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS productos (
    id               INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    codigo           VARCHAR(60)       NOT NULL,
    codigo_alterno   VARCHAR(60)       NULL,
    nombre           VARCHAR(200)      NOT NULL,
    descripcion      TEXT              NULL,
    categoria_id     SMALLINT UNSIGNED NULL,
    unidad_id        TINYINT UNSIGNED  NOT NULL DEFAULT 1,
    proveedor_id     INT UNSIGNED      NULL,
    precio_costo     DECIMAL(12,2)     NOT NULL DEFAULT 0.00,
    precio_venta     DECIMAL(12,2)     NOT NULL DEFAULT 0.00,
    stock_minimo     DECIMAL(10,3)     NOT NULL DEFAULT 1.000,
    activo           TINYINT(1)        NOT NULL DEFAULT 1,
    created_at       DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_prod_codigo (codigo),
    KEY idx_prod_codigo_alt (codigo_alterno),
    KEY idx_prod_categoria (categoria_id),
    KEY idx_prod_nombre (nombre),
    CONSTRAINT fk_prod_cat  FOREIGN KEY (categoria_id) REFERENCES categorias(id)  ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_prod_uni  FOREIGN KEY (unidad_id)    REFERENCES unidades(id)    ON UPDATE CASCADE,
    CONSTRAINT fk_prod_prov FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS servicios_productos (
    servicio_id SMALLINT UNSIGNED NOT NULL,
    producto_id INT UNSIGNED      NOT NULL,
    cantidad    DECIMAL(10,3)     NOT NULL DEFAULT 1.000,
    PRIMARY KEY (servicio_id, producto_id),
    CONSTRAINT fk_sp_ser FOREIGN KEY (servicio_id) REFERENCES servicios(id)  ON DELETE CASCADE,
    CONSTRAINT fk_sp_pro FOREIGN KEY (producto_id) REFERENCES productos(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- STOCK POR SUCURSAL
-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS stock_sucursal (
    producto_id INT UNSIGNED      NOT NULL,
    sucursal_id TINYINT UNSIGNED  NOT NULL,
    cantidad    DECIMAL(10,3)     NOT NULL DEFAULT 0.000,
    updated_at  DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (producto_id, sucursal_id),
    CONSTRAINT fk_ss_prod FOREIGN KEY (producto_id) REFERENCES productos(id)   ON DELETE CASCADE,
    CONSTRAINT fk_ss_suc  FOREIGN KEY (sucursal_id) REFERENCES sucursales(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- MOVIMIENTOS DE INVENTARIO
-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS movimientos (
    id                 INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    tipo               ENUM('entrada','salida','traspaso_salida','traspaso_entrada','ajuste') NOT NULL,
    folio              VARCHAR(30)      NOT NULL,
    sucursal_id        TINYINT UNSIGNED NOT NULL,
    sucursal_dest_id   TINYINT UNSIGNED NULL,
    proveedor_id       INT UNSIGNED     NULL,
    mecanico_id        INT UNSIGNED     NULL,
    servicio_id        SMALLINT UNSIGNED NULL,
    referencia_factura VARCHAR(80)      NULL,
    uuid_cfdi          CHAR(36)         NULL,
    notas              TEXT             NULL,
    estado             ENUM('borrador','confirmado','cancelado') NOT NULL DEFAULT 'confirmado',
    usuario_id         INT UNSIGNED     NOT NULL,
    created_at         DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_mov_folio (folio),
    KEY idx_mov_tipo_fecha (tipo, created_at),
    KEY idx_mov_sucursal   (sucursal_id),
    KEY idx_mov_mecanico   (mecanico_id),
    KEY idx_mov_uuid       (uuid_cfdi),
    CONSTRAINT fk_mov_suc1 FOREIGN KEY (sucursal_id)      REFERENCES sucursales(id),
    CONSTRAINT fk_mov_suc2 FOREIGN KEY (sucursal_dest_id) REFERENCES sucursales(id),
    CONSTRAINT fk_mov_prov FOREIGN KEY (proveedor_id)     REFERENCES proveedores(id),
    CONSTRAINT fk_mov_mec  FOREIGN KEY (mecanico_id)      REFERENCES mecanicos(id),
    CONSTRAINT fk_mov_ser  FOREIGN KEY (servicio_id)      REFERENCES servicios(id),
    CONSTRAINT fk_mov_usr  FOREIGN KEY (usuario_id)       REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS movimientos_detalle (
    id              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    movimiento_id   INT UNSIGNED  NOT NULL,
    producto_id     INT UNSIGNED  NOT NULL,
    cantidad        DECIMAL(10,3) NOT NULL,
    precio_unitario DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    numero_serie    VARCHAR(80)   NULL,
    lote            VARCHAR(40)   NULL,
    notas           VARCHAR(255)  NULL,
    PRIMARY KEY (id),
    KEY idx_det_mov  (movimiento_id),
    KEY idx_det_prod (producto_id),
    CONSTRAINT fk_det_mov  FOREIGN KEY (movimiento_id) REFERENCES movimientos(id) ON DELETE CASCADE,
    CONSTRAINT fk_det_prod FOREIGN KEY (producto_id)   REFERENCES productos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- TRASPASOS
-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS traspasos (
    id                    INT UNSIGNED NOT NULL AUTO_INCREMENT,
    movimiento_salida_id  INT UNSIGNED NOT NULL,
    movimiento_entrada_id INT UNSIGNED NULL,
    estado                ENUM('en_transito','recibido','cancelado') NOT NULL DEFAULT 'en_transito',
    fecha_envio           DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_recepcion       DATETIME     NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_tr_salida  (movimiento_salida_id),
    UNIQUE KEY uq_tr_entrada (movimiento_entrada_id),
    CONSTRAINT fk_tr_sal FOREIGN KEY (movimiento_salida_id)  REFERENCES movimientos(id),
    CONSTRAINT fk_tr_ent FOREIGN KEY (movimiento_entrada_id) REFERENCES movimientos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- AUDITORÍA
-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS auditoria (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id  INT UNSIGNED    NULL,
    accion      VARCHAR(80)     NOT NULL,
    tabla_ref   VARCHAR(60)     NULL,
    registro_id INT UNSIGNED    NULL,
    ip          VARCHAR(45)     NULL,
    descripcion TEXT            NULL,
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_aud_usuario (usuario_id),
    KEY idx_aud_accion  (accion),
    KEY idx_aud_fecha   (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET foreign_key_checks = 1;

-- ------------------------------------------------------------
-- DATOS INICIALES
-- ------------------------------------------------------------

INSERT INTO sucursales (nombre, ciudad, direccion, telefono) VALUES
    ('Sucursal Principal', 'Ciudad Principal', 'Calle Principal #1', '000-000-0001'),
    ('Sucursal Norte',     'Ciudad Norte',     'Calle Norte #1',     '000-000-0002');

INSERT INTO unidades (clave, nombre) VALUES
    ('PZA', 'Pieza'),
    ('KIT', 'Kit'),
    ('PAR', 'Par'),
    ('JGO', 'Juego'),
    ('LT',  'Litro'),
    ('KG',  'Kilogramo'),
    ('MT',  'Metro'),
    ('CJA', 'Caja');

INSERT INTO categorias (nombre) VALUES
    ('Muelles'),
    ('Amortiguadores'),
    ('Bujes y silentblocks'),
    ('Tornillería'),
    ('Aceites y lubricantes'),
    ('Consumibles'),
    ('Refacciones generales');

-- Usuario admin creado por setup.php (ver instrucciones en README.md)
