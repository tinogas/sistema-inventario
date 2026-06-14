-- ============================================================
-- MIGRACIÓN: Módulo de Facturación Interna
-- Ejecutar en phpMyAdmin sobre la BD inventario_taller
-- ============================================================
SET NAMES utf8mb4;
SET foreign_key_checks = 0;

CREATE TABLE IF NOT EXISTS facturas (
    id                INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    folio             VARCHAR(30)       NOT NULL,
    sucursal_id       TINYINT UNSIGNED  NOT NULL,
    estado            ENUM('borrador','emitida','pagada','cancelada') NOT NULL DEFAULT 'borrador',

    cliente_nombre    VARCHAR(150)      NOT NULL,
    cliente_tel       VARCHAR(25)       NULL,

    vh_marca          VARCHAR(60)       NOT NULL,
    vh_modelo         VARCHAR(80)       NOT NULL,
    vh_anio           SMALLINT UNSIGNED NOT NULL,
    vh_placas         VARCHAR(20)       NULL,

    mecanico_id       INT UNSIGNED      NULL,
    servicio_id       SMALLINT UNSIGNED NULL,

    mano_obra         DECIMAL(12,2)     NOT NULL DEFAULT 0.00,
    mano_obra_desc    VARCHAR(200)      NULL,

    subtotal          DECIMAL(12,2)     NOT NULL DEFAULT 0.00,
    total             DECIMAL(12,2)     NOT NULL DEFAULT 0.00,

    movimiento_id     INT UNSIGNED      NULL,
    referencia_proneg VARCHAR(80)       NULL,
    notas             TEXT              NULL,

    usuario_id        INT UNSIGNED      NOT NULL,
    fecha_emision     DATETIME          NULL,
    fecha_pago        DATETIME          NULL,
    created_at        DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_fac_folio   (folio),
    KEY idx_fac_sucursal      (sucursal_id),
    KEY idx_fac_estado        (estado),
    KEY idx_fac_fecha         (created_at),
    CONSTRAINT fk_fac_suc FOREIGN KEY (sucursal_id)  REFERENCES sucursales(id),
    CONSTRAINT fk_fac_mec FOREIGN KEY (mecanico_id)  REFERENCES mecanicos(id)  ON DELETE SET NULL,
    CONSTRAINT fk_fac_ser FOREIGN KEY (servicio_id)  REFERENCES servicios(id)  ON DELETE SET NULL,
    CONSTRAINT fk_fac_mov FOREIGN KEY (movimiento_id) REFERENCES movimientos(id) ON DELETE SET NULL,
    CONSTRAINT fk_fac_usr FOREIGN KEY (usuario_id)   REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS facturas_detalle (
    id              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    factura_id      INT UNSIGNED  NOT NULL,
    producto_id     INT UNSIGNED  NOT NULL,
    cantidad        DECIMAL(10,3) NOT NULL,
    precio_unitario DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    notas           VARCHAR(255)  NULL,
    PRIMARY KEY (id),
    KEY idx_fd_factura  (factura_id),
    KEY idx_fd_producto (producto_id),
    CONSTRAINT fk_fd_fac  FOREIGN KEY (factura_id)  REFERENCES facturas(id)  ON DELETE CASCADE,
    CONSTRAINT fk_fd_prod FOREIGN KEY (producto_id) REFERENCES productos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contador de folios por sucursal+año para numeración independiente
CREATE TABLE IF NOT EXISTS facturas_folios (
    sucursal_id TINYINT UNSIGNED  NOT NULL,
    anio        SMALLINT UNSIGNED NOT NULL,
    ultimo      INT UNSIGNED      NOT NULL DEFAULT 0,
    PRIMARY KEY (sucursal_id, anio),
    CONSTRAINT fk_ff_suc FOREIGN KEY (sucursal_id) REFERENCES sucursales(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET foreign_key_checks = 1;
