-- Migración: Catálogo de Clientes, Unidades y Bitácora de Servicio
-- Compatible con MariaDB 10.4+
-- Ejecutar una sola vez en la base de datos inventario_taller.

-- Catálogo de clientes
CREATE TABLE IF NOT EXISTS clientes (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  nombre      VARCHAR(120) NOT NULL,
  rfc         VARCHAR(15),
  telefono    VARCHAR(20),
  email       VARCHAR(80),
  direccion   VARCHAR(200),
  notas       TEXT,
  activo      TINYINT(1) NOT NULL DEFAULT 1,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Unidades (vehículos/equipos) por cliente
CREATE TABLE IF NOT EXISTS clientes_unidades (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id    INT NOT NULL,
  marca         VARCHAR(50) NOT NULL,
  modelo        VARCHAR(80) NOT NULL,
  anio          YEAR,
  placas        VARCHAR(20),
  numero_serie  VARCHAR(50),
  color         VARCHAR(30),
  notas         TEXT,
  activo        TINYINT(1) NOT NULL DEFAULT 1,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (cliente_id) REFERENCES clientes(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Agregar columnas a facturas si aún no existen (ADD COLUMN IF NOT EXISTS sí es compatible con 10.4)
ALTER TABLE facturas
  ADD COLUMN IF NOT EXISTS cliente_id INT NULL AFTER estado,
  ADD COLUMN IF NOT EXISTS unidad_id  INT NULL AFTER cliente_id;

-- Agregar FK a facturas (solo si las columnas existen y las FK aún no están).
-- En 10.4 no hay IF NOT EXISTS para CONSTRAINT, así que usamos un bloque condicional.
SET @fk1 = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = DATABASE()
               AND TABLE_NAME        = 'facturas'
               AND CONSTRAINT_NAME   = 'fk_facturas_cliente');
SET @fk2 = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = DATABASE()
               AND TABLE_NAME        = 'facturas'
               AND CONSTRAINT_NAME   = 'fk_facturas_unidad');

SET @sql1 = IF(@fk1 = 0,
  'ALTER TABLE facturas ADD CONSTRAINT fk_facturas_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id)',
  'SELECT 1');
SET @sql2 = IF(@fk2 = 0,
  'ALTER TABLE facturas ADD CONSTRAINT fk_facturas_unidad FOREIGN KEY (unidad_id) REFERENCES clientes_unidades(id)',
  'SELECT 1');

PREPARE stmt1 FROM @sql1; EXECUTE stmt1; DEALLOCATE PREPARE stmt1;
PREPARE stmt2 FROM @sql2; EXECUTE stmt2; DEALLOCATE PREPARE stmt2;

-- Bitácora de servicio por unidad
-- Nota: factura_id y mecanico_id deben ser UNSIGNED para coincidir con sus tablas padre.
CREATE TABLE IF NOT EXISTS bitacoras_servicio (
  id                  INT AUTO_INCREMENT PRIMARY KEY,
  unidad_id           INT NOT NULL,
  factura_id          INT(10) UNSIGNED NOT NULL,
  fecha_servicio      DATE NOT NULL,
  mecanico_id         INT(10) UNSIGNED NULL,
  descripcion         TEXT,
  trabajos_realizados TEXT,
  productos_snapshot  LONGTEXT,
  mano_obra           DECIMAL(12,2) NOT NULL DEFAULT 0,
  subtotal            DECIMAL(12,2) NOT NULL DEFAULT 0,
  total               DECIMAL(12,2) NOT NULL DEFAULT 0,
  created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (unidad_id)   REFERENCES clientes_unidades(id),
  FOREIGN KEY (factura_id)  REFERENCES facturas(id),
  FOREIGN KEY (mecanico_id) REFERENCES mecanicos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
