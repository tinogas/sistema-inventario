-- Seed de datos de ejemplo — inventario_taller
-- Generado: 2026-06-14 15:56:20
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `auditoria`;
CREATE TABLE `auditoria` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(10) unsigned DEFAULT NULL,
  `accion` varchar(80) NOT NULL,
  `tabla_ref` varchar(60) DEFAULT NULL,
  `registro_id` int(10) unsigned DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_aud_usuario` (`usuario_id`),
  KEY `idx_aud_accion` (`accion`),
  KEY `idx_aud_fecha` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `auditoria` (`id`, `usuario_id`, `accion`, `tabla_ref`, `registro_id`, `ip`, `descripcion`, `created_at`) VALUES
('1', '1', 'login', 'usuarios', '1', '192.168.1.1', 'Inicio de sesion', '2026-01-10 08:00:00'),
('2', '2', 'entrada_crear', 'movimientos', '1', '192.168.1.5', 'ENT-2026-00001 confirmada', '2026-01-15 09:30:00'),
('3', '2', 'salida_crear', 'movimientos', '4', '192.168.1.5', 'SAL-2026-00001 Hilux TSN-456-B', '2026-02-05 11:00:00'),
('4', '2', 'traspaso_crear', 'traspasos', '1', '192.168.1.5', 'TRP-2026-00001 enviado a Nogales', '2026-03-15 16:00:00'),
('5', '3', 'traspaso_recibir', 'traspasos', '1', '10.0.0.20', 'TRP-2026-00002 recibido en Nogales', '2026-03-16 08:30:00'),
('6', '3', 'entrada_crear', 'movimientos', '3', '10.0.0.20', 'ENT-2026-00003 primera entrada Nogales', '2026-04-10 08:45:00'),
('7', '1', 'confirmar_entrada', 'movimientos', '13', '::1', NULL, '2026-06-13 22:35:29'),
('8', '1', 'crear_traspaso', 'traspasos', '3', '::1', NULL, '2026-06-13 22:38:00'),
('9', '1', 'cancelar_traspaso', 'traspasos', '3', '::1', NULL, '2026-06-13 22:42:05'),
('10', '1', 'crear_sucursal', 'sucursales', '3', '::1', NULL, '2026-06-13 22:52:01'),
('11', '1', 'crear_traspaso', 'traspasos', '4', '::1', NULL, '2026-06-13 22:54:14'),
('12', '1', 'confirmar_recepcion_traspaso', 'traspasos', '4', '::1', NULL, '2026-06-13 22:56:56'),
('13', '1', 'crear_traspaso', 'traspasos', '5', '::1', NULL, '2026-06-13 22:57:58'),
('14', '1', 'confirmar_recepcion_traspaso', 'traspasos', '5', '::1', NULL, '2026-06-13 22:58:00'),
('15', '1', 'crear_traspaso', 'traspasos', '6', '::1', NULL, '2026-06-13 22:58:46'),
('16', '1', 'confirmar_salida', 'movimientos', '20', '::1', NULL, '2026-06-14 00:11:21'),
('17', '1', 'confirmar_recepcion_traspaso', 'traspasos', '6', '::1', NULL, '2026-06-14 00:13:06'),
('18', '1', 'editar', 'mecanicos', '4', '::1', 'Mecánico: Francisco Javier Lopez Diaz', '2026-06-14 00:16:33'),
('19', '1', 'confirmar_recepcion_traspaso', 'traspasos', '2', '::1', NULL, '2026-06-14 00:30:04'),
('20', '1', 'editar', 'mecanicos', '4', '::1', 'Mecánico: Francisco Javier Lopez Diaz', '2026-06-14 11:25:39'),
('21', '1', 'editar', 'mecanicos', '2', '::1', 'Mecánico: Jose Luis Espinoza Garcia', '2026-06-14 11:25:50'),
('22', '1', 'editar', 'mecanicos', '1', '::1', 'Mecánico: Miguel Angel Rodriguez Soto', '2026-06-14 11:26:08'),
('23', '1', 'editar_sucursal', 'sucursales', '3', '::1', NULL, '2026-06-14 11:32:40'),
('24', '1', 'editar_sucursal', 'sucursales', '3', '::1', NULL, '2026-06-14 11:33:09'),
('25', '1', 'editar', 'usuarios', '1', '::1', 'Usuario: admin@tallermuellessonora.mx rol: admin', '2026-06-14 11:39:20'),
('26', '1', 'confirmar_entrada', 'movimientos', '25', '::1', NULL, '2026-06-14 11:48:05'),
('27', '1', 'confirmar_entrada', 'movimientos', '26', '::1', NULL, '2026-06-14 11:49:13'),
('28', '1', 'crear_backup', 'backups_log', NULL, '::1', 'Respaldo: backup_inventario_taller_20260614_121640.sql', '2026-06-14 12:16:40');

DROP TABLE IF EXISTS `backups_log`;
CREATE TABLE `backups_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `archivo` varchar(255) NOT NULL,
  `tamano_bytes` bigint(20) unsigned NOT NULL DEFAULT 0,
  `num_tablas` int(10) unsigned NOT NULL DEFAULT 0,
  `num_registros` int(10) unsigned NOT NULL DEFAULT 0,
  `usuario_id` int(10) unsigned DEFAULT NULL,
  `usuario_nombre` varchar(120) DEFAULT NULL,
  `estado` enum('completado','error') NOT NULL DEFAULT 'completado',
  `notas` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_bk_fecha` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `backups_log` (`id`, `archivo`, `tamano_bytes`, `num_tablas`, `num_registros`, `usuario_id`, `usuario_nombre`, `estado`, `notas`, `created_at`) VALUES
('1', 'backup_inventario_taller_20260614_121640.sql', '44980', '19', '285', '1', 'Administrador General', 'completado', NULL, '2026-06-14 12:16:40');

DROP TABLE IF EXISTS `categorias`;
CREATE TABLE `categorias` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(80) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cat_nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `categorias` (`id`, `nombre`, `descripcion`, `activa`) VALUES
('1', 'Muelles', NULL, '1'),
('2', 'Amortiguadores', NULL, '1'),
('3', 'Bujes y silentblocks', NULL, '1'),
('4', 'Tornillería', NULL, '1'),
('5', 'Aceites y lubricantes', NULL, '1'),
('6', 'Consumibles', NULL, '1'),
('7', 'Refacciones generales', NULL, '1'),
('8', 'TEST_Categoria_Prueba', 'Categoría de prueba automatizada', '0');

DROP TABLE IF EXISTS `empresa`;
CREATE TABLE `empresa` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `clave` varchar(60) NOT NULL,
  `valor` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_empresa_clave` (`clave`)
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `empresa` (`id`, `clave`, `valor`) VALUES
('1', 'nombre', 'Taller Muelles Sonora'),
('2', 'rfc', ''),
('3', 'direccion', ''),
('4', 'ciudad', 'Hermosillo'),
('5', 'cp', ''),
('6', 'telefono', ''),
('7', 'email', ''),
('8', 'logo_path', ''),
('9', 'pie_factura', 'Gracias por su preferencia. Garantia de 3 meses en mano de obra.');

DROP TABLE IF EXISTS `facturas`;
CREATE TABLE `facturas` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `folio` varchar(30) NOT NULL,
  `sucursal_id` tinyint(3) unsigned NOT NULL,
  `estado` enum('borrador','emitida','pagada','cancelada') NOT NULL DEFAULT 'borrador',
  `cliente_nombre` varchar(150) NOT NULL,
  `cliente_tel` varchar(25) DEFAULT NULL,
  `vh_marca` varchar(60) NOT NULL,
  `vh_modelo` varchar(80) NOT NULL,
  `vh_anio` smallint(5) unsigned NOT NULL,
  `vh_placas` varchar(20) DEFAULT NULL,
  `mecanico_id` int(10) unsigned DEFAULT NULL,
  `servicio_id` smallint(5) unsigned DEFAULT NULL,
  `mano_obra` decimal(12,2) NOT NULL DEFAULT 0.00,
  `mano_obra_desc` varchar(200) DEFAULT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `movimiento_id` int(10) unsigned DEFAULT NULL,
  `referencia_proneg` varchar(80) DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `usuario_id` int(10) unsigned NOT NULL,
  `fecha_emision` datetime DEFAULT NULL,
  `fecha_pago` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `descuento_pct` decimal(5,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_fac_folio` (`folio`),
  KEY `idx_fac_sucursal` (`sucursal_id`),
  KEY `idx_fac_estado` (`estado`),
  KEY `idx_fac_fecha` (`created_at`),
  KEY `fk_fac_mec` (`mecanico_id`),
  KEY `fk_fac_ser` (`servicio_id`),
  KEY `fk_fac_mov` (`movimiento_id`),
  KEY `fk_fac_usr` (`usuario_id`),
  CONSTRAINT `fk_fac_mec` FOREIGN KEY (`mecanico_id`) REFERENCES `mecanicos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_fac_mov` FOREIGN KEY (`movimiento_id`) REFERENCES `movimientos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_fac_ser` FOREIGN KEY (`servicio_id`) REFERENCES `servicios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_fac_suc` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`id`),
  CONSTRAINT `fk_fac_usr` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `facturas` (`id`, `folio`, `sucursal_id`, `estado`, `cliente_nombre`, `cliente_tel`, `vh_marca`, `vh_modelo`, `vh_anio`, `vh_placas`, `mecanico_id`, `servicio_id`, `mano_obra`, `mano_obra_desc`, `subtotal`, `total`, `movimiento_id`, `referencia_proneg`, `notas`, `usuario_id`, `fecha_emision`, `fecha_pago`, `created_at`, `updated_at`, `descuento_pct`) VALUES
('1', 'FAC-1-2026-00001', '1', 'emitida', 'Juan Carlos Perez Lopez', '662-310-4521', 'Ford', 'F-150 XL', '2020', 'ABN-123-K', '2', '3', '650.00', 'Cambio amortiguadores delanteros', '3150.00', '3150.00', '5', NULL, NULL, '2', '2026-06-08 14:00:00', NULL, '2026-06-08 14:00:00', '2026-06-13 23:28:28', '0.00'),
('2', 'FAC-1-2026-00002', '1', 'pagada', 'Maria Guadalupe Garcia Flores', '662-447-8830', 'Toyota', 'Hilux 4x4', '2017', 'XYZ-789-A', '3', '7', '2200.00', 'Cambio kit suspension completo', '10920.00', '10920.00', '6', NULL, NULL, '2', '2026-06-09 10:30:00', '2026-06-09 18:00:00', '2026-06-09 10:30:00', '2026-06-13 23:28:28', '0.00'),
('3', 'FAC-1-2026-00003', '1', 'pagada', 'Oscar Sanchez Bustamante', '662-181-6647', 'Ford', 'Ranger Sport', '2021', 'FRD-654-Z', '1', '4', '600.00', 'Cambio amortiguadores traseros', '2855.00', '2855.00', '8', NULL, NULL, '2', '2026-06-11 11:00:00', '2026-06-11 17:30:00', '2026-06-11 11:00:00', '2026-06-13 23:28:28', '0.00'),
('4', 'FAC-2-2026-00001', '2', 'pagada', 'Pedro Antonio Armenta Ruiz', '631-209-5544', 'Toyota', 'Hilux SR5', '2018', 'NDG-321-C', '5', '2', '750.00', 'Cambio muelles traseros', '2920.00', '2920.00', '7', NULL, NULL, '3', '2026-06-10 14:30:00', '2026-06-10 19:00:00', '2026-06-10 14:30:00', '2026-06-13 23:28:28', '0.00'),
('5', 'FAC-2-2026-00002', '2', 'emitida', 'Lupita Montoya Esquer', '631-417-3390', 'Toyota', 'Hilux 4x4', '2020', 'SNL-987-M', '6', '1', '850.00', 'Cambio muelles delanteros', '3420.00', '3420.00', '9', NULL, NULL, '3', '2026-06-12 13:00:00', NULL, '2026-06-12 13:00:00', '2026-06-13 23:28:28', '0.00');

DROP TABLE IF EXISTS `facturas_detalle`;
CREATE TABLE `facturas_detalle` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `factura_id` int(10) unsigned NOT NULL,
  `producto_id` int(10) unsigned NOT NULL,
  `cantidad` decimal(10,3) NOT NULL,
  `precio_unitario` decimal(12,2) NOT NULL DEFAULT 0.00,
  `notas` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_fd_factura` (`factura_id`),
  KEY `idx_fd_producto` (`producto_id`),
  CONSTRAINT `fk_fd_fac` FOREIGN KEY (`factura_id`) REFERENCES `facturas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_fd_prod` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `facturas_detalle` (`id`, `factura_id`, `producto_id`, `cantidad`, `precio_unitario`, `notas`) VALUES
('1', '1', '6', '2.000', '1250.00', NULL),
('2', '2', '1', '1.000', '1850.00', NULL),
('3', '2', '2', '1.000', '1450.00', NULL),
('4', '2', '6', '2.000', '1250.00', NULL),
('5', '2', '7', '2.000', '1100.00', NULL),
('6', '2', '11', '1.000', '520.00', NULL),
('7', '2', '15', '1.000', '200.00', NULL),
('8', '3', '7', '2.000', '1100.00', NULL),
('9', '3', '17', '0.250', '140.00', NULL),
('10', '3', '19', '1.000', '20.00', NULL),
('11', '4', '2', '1.000', '1450.00', NULL),
('12', '4', '11', '1.000', '520.00', NULL),
('13', '4', '15', '1.000', '200.00', NULL),
('14', '5', '1', '1.000', '1850.00', NULL),
('15', '5', '11', '1.000', '520.00', NULL),
('16', '5', '15', '1.000', '200.00', NULL);

DROP TABLE IF EXISTS `facturas_folios`;
CREATE TABLE `facturas_folios` (
  `sucursal_id` tinyint(3) unsigned NOT NULL,
  `anio` smallint(5) unsigned NOT NULL,
  `ultimo` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`sucursal_id`,`anio`),
  CONSTRAINT `fk_ff_suc` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `facturas_folios` (`sucursal_id`, `anio`, `ultimo`) VALUES
('1', '2026', '3'),
('2', '2026', '2');

DROP TABLE IF EXISTS `mecanicos`;
CREATE TABLE `mecanicos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) NOT NULL,
  `sucursal_id` tinyint(3) unsigned NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `foto` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_mec_sucursal` (`sucursal_id`),
  CONSTRAINT `fk_mec_suc` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `mecanicos` (`id`, `nombre`, `sucursal_id`, `telefono`, `activo`, `created_at`, `foto`) VALUES
('1', 'Miguel Angel Rodriguez Soto', '1', '662-445-1201', '1', '2026-06-13 18:58:03', 'uploads/fotos/mecanico_20260614_112608_95e33648.jpg'),
('2', 'Jose Luis Espinoza Garcia', '1', '662-445-1202', '1', '2026-06-13 18:58:03', 'uploads/fotos/mecanico_20260614_112550_c24de93a.jpg'),
('3', 'Roberto Hernandez Morales', '1', '662-445-1203', '1', '2026-06-13 18:58:03', NULL),
('4', 'Francisco Javier Lopez Diaz', '3', '662-445-1204', '1', '2026-06-13 18:58:03', 'uploads/fotos/mecanico_20260614_112539_826ce539.jpg'),
('5', 'Ernesto Contreras Valdez', '2', '631-214-0501', '1', '2026-06-13 18:58:03', NULL),
('6', 'Sergio Alejandro Ruiz Perez', '2', '631-214-0502', '1', '2026-06-13 18:58:03', NULL),
('7', 'Marco Antonio Torres Rios', '2', '631-214-0503', '1', '2026-06-13 18:58:03', NULL);

DROP TABLE IF EXISTS `movimientos`;
CREATE TABLE `movimientos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tipo` enum('entrada','salida','traspaso_salida','traspaso_entrada','ajuste') NOT NULL,
  `folio` varchar(30) NOT NULL,
  `sucursal_id` tinyint(3) unsigned NOT NULL,
  `sucursal_dest_id` tinyint(3) unsigned DEFAULT NULL,
  `proveedor_id` int(10) unsigned DEFAULT NULL,
  `mecanico_id` int(10) unsigned DEFAULT NULL,
  `servicio_id` smallint(5) unsigned DEFAULT NULL,
  `referencia_factura` varchar(80) DEFAULT NULL,
  `uuid_cfdi` char(36) DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `estado` enum('borrador','confirmado','cancelado') NOT NULL DEFAULT 'confirmado',
  `usuario_id` int(10) unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_mov_folio` (`folio`),
  KEY `idx_mov_tipo_fecha` (`tipo`,`created_at`),
  KEY `idx_mov_sucursal` (`sucursal_id`),
  KEY `idx_mov_mecanico` (`mecanico_id`),
  KEY `idx_mov_uuid` (`uuid_cfdi`),
  KEY `fk_mov_suc2` (`sucursal_dest_id`),
  KEY `fk_mov_prov` (`proveedor_id`),
  KEY `fk_mov_ser` (`servicio_id`),
  KEY `fk_mov_usr` (`usuario_id`),
  CONSTRAINT `fk_mov_mec` FOREIGN KEY (`mecanico_id`) REFERENCES `mecanicos` (`id`),
  CONSTRAINT `fk_mov_prov` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`),
  CONSTRAINT `fk_mov_ser` FOREIGN KEY (`servicio_id`) REFERENCES `servicios` (`id`),
  CONSTRAINT `fk_mov_suc1` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`id`),
  CONSTRAINT `fk_mov_suc2` FOREIGN KEY (`sucursal_dest_id`) REFERENCES `sucursales` (`id`),
  CONSTRAINT `fk_mov_usr` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `movimientos` (`id`, `tipo`, `folio`, `sucursal_id`, `sucursal_dest_id`, `proveedor_id`, `mecanico_id`, `servicio_id`, `referencia_factura`, `uuid_cfdi`, `notas`, `estado`, `usuario_id`, `created_at`) VALUES
('1', 'entrada', 'ENT-2026-00001', '1', NULL, '1', NULL, NULL, 'A2026-0115', NULL, 'Compra inicial muelles y amortiguadores Hermosillo', 'confirmado', '2', '2026-06-07 09:30:00'),
('2', 'entrada', 'ENT-2026-00002', '1', NULL, '2', NULL, NULL, 'KYB-2026-0220', NULL, 'Reposicion KYB bujes y consumibles', 'confirmado', '2', '2026-06-09 10:15:00'),
('3', 'entrada', 'ENT-2026-00003', '2', NULL, '1', NULL, NULL, 'A2026-0410', NULL, 'Primera compra sucursal Nogales', 'confirmado', '3', '2026-06-10 08:45:00'),
('4', 'salida', 'SAL-2026-00001', '1', NULL, NULL, '1', '1', NULL, NULL, 'Hilux 2019 placas TSN-456-B', 'confirmado', '2', '2026-06-07 11:00:00'),
('5', 'salida', 'SAL-2026-00002', '1', NULL, NULL, '2', '3', NULL, NULL, 'F-150 2020 placas ABN-123-K', 'confirmado', '2', '2026-06-08 13:30:00'),
('6', 'salida', 'SAL-2026-00003', '1', NULL, NULL, '3', '7', NULL, NULL, 'Hilux 4x4 2017 placas XYZ-789-A kit suspension', 'confirmado', '2', '2026-06-09 09:00:00'),
('7', 'salida', 'SAL-2026-00004', '2', NULL, NULL, '5', '2', NULL, NULL, 'Hilux 2018 placas NDG-321-C', 'confirmado', '3', '2026-06-10 14:00:00'),
('8', 'salida', 'SAL-2026-00005', '1', NULL, NULL, '1', '4', NULL, NULL, 'Ranger 2021 placas FRD-654-Z', 'confirmado', '2', '2026-06-11 10:30:00'),
('9', 'salida', 'SAL-2026-00006', '2', NULL, NULL, '6', '1', NULL, NULL, 'Hilux 2020 placas SNL-987-M', 'confirmado', '3', '2026-06-12 12:15:00'),
('10', 'traspaso_salida', 'TRP-2026-00001', '1', '2', NULL, NULL, NULL, NULL, NULL, 'Traspaso muelles F-150 a Nogales', 'confirmado', '2', '2026-06-09 16:00:00'),
('11', 'traspaso_entrada', 'TRP-2026-00002', '2', NULL, NULL, NULL, NULL, NULL, NULL, 'Recepcion muelles F-150 de Hermosillo', 'confirmado', '3', '2026-06-10 08:30:00'),
('12', 'traspaso_salida', 'TRP-2026-00003', '1', '2', NULL, NULL, NULL, NULL, NULL, 'Traspaso amortiguadores KYB en transito', 'confirmado', '2', '2026-06-13 10:00:00'),
('13', 'entrada', 'ENT-2026-00004', '1', NULL, '5', NULL, NULL, NULL, NULL, NULL, 'confirmado', '1', '2026-06-13 22:35:29'),
('14', 'traspaso_salida', 'TRP-2026-00004', '1', '2', NULL, NULL, NULL, NULL, NULL, 'ocupan', 'confirmado', '1', '2026-06-13 22:38:00'),
('15', 'traspaso_salida', 'TRP-2026-00005', '1', '3', NULL, NULL, NULL, NULL, NULL, 'nueva tienda', 'confirmado', '1', '2026-06-13 22:54:14'),
('16', 'traspaso_entrada', 'TRP-2026-00006', '3', NULL, NULL, NULL, NULL, NULL, NULL, 'Recepción de traspaso', 'confirmado', '1', '2026-06-13 22:56:56'),
('17', 'traspaso_salida', 'TRP-2026-00007', '2', '1', NULL, NULL, NULL, NULL, NULL, 'escasez', 'confirmado', '1', '2026-06-13 22:57:58'),
('18', 'traspaso_entrada', 'TRP-2026-00008', '1', NULL, NULL, NULL, NULL, NULL, NULL, 'Recepción de traspaso', 'confirmado', '1', '2026-06-13 22:58:00'),
('19', 'traspaso_salida', 'TRP-2026-00009', '3', '1', NULL, NULL, NULL, NULL, NULL, NULL, 'confirmado', '1', '2026-06-13 22:58:46'),
('20', 'salida', 'SAL-2026-00007', '3', NULL, NULL, '4', '2', NULL, NULL, NULL, 'confirmado', '1', '2026-06-14 00:11:21'),
('21', 'traspaso_entrada', 'TRP-2026-00010', '1', NULL, NULL, NULL, NULL, NULL, NULL, 'Recepción de traspaso', 'confirmado', '1', '2026-06-14 00:13:06'),
('24', 'traspaso_entrada', 'TRP-2026-00011', '2', NULL, NULL, NULL, NULL, NULL, NULL, 'Recepción parcial: lo no recibido se devolvió al stock de origen', 'confirmado', '1', '2026-06-14 00:30:04'),
('25', 'entrada', 'ENT-2026-00005', '3', NULL, '5', NULL, NULL, NULL, NULL, NULL, 'confirmado', '1', '2026-06-14 11:48:05'),
('26', 'entrada', 'ENT-2026-00006', '3', NULL, '5', NULL, NULL, NULL, NULL, NULL, 'confirmado', '1', '2026-06-14 11:49:13');

DROP TABLE IF EXISTS `movimientos_detalle`;
CREATE TABLE `movimientos_detalle` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `movimiento_id` int(10) unsigned NOT NULL,
  `producto_id` int(10) unsigned NOT NULL,
  `cantidad` decimal(10,3) NOT NULL,
  `precio_unitario` decimal(12,2) NOT NULL DEFAULT 0.00,
  `numero_serie` varchar(80) DEFAULT NULL,
  `lote` varchar(40) DEFAULT NULL,
  `notas` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_det_mov` (`movimiento_id`),
  KEY `idx_det_prod` (`producto_id`),
  CONSTRAINT `fk_det_mov` FOREIGN KEY (`movimiento_id`) REFERENCES `movimientos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_det_prod` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `movimientos_detalle` (`id`, `movimiento_id`, `producto_id`, `cantidad`, `precio_unitario`, `numero_serie`, `lote`, `notas`) VALUES
('1', '1', '1', '10.000', '1250.00', NULL, NULL, NULL),
('2', '1', '2', '8.000', '980.00', NULL, NULL, NULL),
('3', '1', '3', '6.000', '1450.00', NULL, NULL, NULL),
('4', '1', '4', '6.000', '1200.00', NULL, NULL, NULL),
('5', '1', '6', '16.000', '850.00', NULL, NULL, NULL),
('6', '1', '7', '14.000', '750.00', NULL, NULL, NULL),
('7', '2', '8', '10.000', '920.00', NULL, NULL, NULL),
('8', '2', '9', '10.000', '820.00', NULL, NULL, NULL),
('9', '2', '10', '12.000', '680.00', NULL, NULL, NULL),
('10', '2', '11', '12.000', '320.00', NULL, NULL, NULL),
('11', '2', '12', '8.000', '380.00', NULL, NULL, NULL),
('12', '2', '13', '12.000', '180.00', NULL, NULL, NULL),
('13', '2', '14', '10.000', '160.00', NULL, NULL, NULL),
('14', '2', '15', '20.000', '120.00', NULL, NULL, NULL),
('15', '2', '16', '15.000', '95.00', NULL, NULL, NULL),
('16', '2', '17', '5.000', '85.00', NULL, NULL, NULL),
('17', '2', '18', '4.000', '75.00', NULL, NULL, NULL),
('18', '2', '19', '30.000', '12.00', NULL, NULL, NULL),
('19', '2', '20', '10.000', '45.00', NULL, NULL, NULL),
('20', '3', '1', '6.000', '1250.00', NULL, NULL, NULL),
('21', '3', '2', '4.000', '980.00', NULL, NULL, NULL),
('22', '3', '5', '4.000', '1100.00', NULL, NULL, NULL),
('23', '3', '6', '8.000', '850.00', NULL, NULL, NULL),
('24', '3', '7', '8.000', '750.00', NULL, NULL, NULL),
('25', '3', '8', '6.000', '920.00', NULL, NULL, NULL),
('26', '3', '9', '6.000', '820.00', NULL, NULL, NULL),
('27', '3', '11', '6.000', '320.00', NULL, NULL, NULL),
('28', '3', '15', '15.000', '120.00', NULL, NULL, NULL),
('29', '3', '19', '20.000', '12.00', NULL, NULL, NULL),
('30', '4', '1', '1.000', '1850.00', NULL, NULL, NULL),
('31', '4', '11', '1.000', '520.00', NULL, NULL, NULL),
('32', '4', '15', '1.000', '200.00', NULL, NULL, NULL),
('33', '5', '6', '2.000', '1250.00', NULL, NULL, NULL),
('34', '5', '19', '1.000', '20.00', NULL, NULL, NULL),
('35', '6', '1', '1.000', '1850.00', NULL, NULL, NULL),
('36', '6', '2', '1.000', '1450.00', NULL, NULL, NULL),
('37', '6', '6', '2.000', '1250.00', NULL, NULL, NULL),
('38', '6', '7', '2.000', '1100.00', NULL, NULL, NULL),
('39', '6', '11', '1.000', '520.00', NULL, NULL, NULL),
('40', '6', '15', '1.000', '200.00', NULL, NULL, NULL),
('41', '7', '2', '1.000', '1450.00', NULL, NULL, NULL),
('42', '7', '11', '1.000', '520.00', NULL, NULL, NULL),
('43', '7', '15', '1.000', '200.00', NULL, NULL, NULL),
('44', '8', '7', '2.000', '1100.00', NULL, NULL, NULL),
('45', '8', '17', '0.250', '140.00', NULL, NULL, NULL),
('46', '8', '19', '1.000', '20.00', NULL, NULL, NULL),
('47', '9', '1', '1.000', '1850.00', NULL, NULL, NULL),
('48', '9', '11', '1.000', '520.00', NULL, NULL, NULL),
('49', '9', '15', '1.000', '200.00', NULL, NULL, NULL),
('50', '10', '3', '2.000', '1450.00', NULL, NULL, NULL),
('51', '10', '4', '2.000', '1200.00', NULL, NULL, NULL),
('52', '11', '3', '2.000', '1450.00', NULL, NULL, NULL),
('53', '11', '4', '2.000', '1200.00', NULL, NULL, NULL),
('54', '12', '6', '4.000', '850.00', NULL, NULL, NULL),
('55', '12', '7', '4.000', '750.00', NULL, NULL, NULL),
('56', '13', '16', '5.000', '95.00', NULL, NULL, NULL),
('57', '14', '15', '10.000', '0.00', NULL, NULL, NULL),
('58', '15', '15', '18.000', '0.00', NULL, NULL, NULL),
('59', '16', '15', '18.000', '0.00', NULL, NULL, NULL),
('60', '17', '15', '3.000', '0.00', NULL, NULL, NULL),
('61', '18', '15', '3.000', '0.00', NULL, NULL, NULL),
('62', '19', '15', '3.000', '0.00', NULL, NULL, NULL),
('63', '20', '15', '2.000', '200.00', NULL, NULL, NULL),
('64', '21', '15', '2.000', '0.00', NULL, NULL, NULL),
('67', '24', '6', '2.000', '0.00', NULL, NULL, NULL),
('68', '24', '7', '3.000', '0.00', NULL, NULL, NULL),
('69', '25', '20', '4.000', '45.00', NULL, NULL, NULL),
('70', '26', '20', '2.000', '45.00', NULL, NULL, NULL);

DROP TABLE IF EXISTS `productos`;
CREATE TABLE `productos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(60) NOT NULL,
  `codigo_alterno` varchar(60) DEFAULT NULL,
  `nombre` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `categoria_id` smallint(5) unsigned DEFAULT NULL,
  `unidad_id` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `proveedor_id` int(10) unsigned DEFAULT NULL,
  `precio_costo` decimal(12,2) NOT NULL DEFAULT 0.00,
  `precio_venta` decimal(12,2) NOT NULL DEFAULT 0.00,
  `stock_minimo` decimal(10,3) NOT NULL DEFAULT 1.000,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_prod_codigo` (`codigo`),
  KEY `idx_prod_codigo_alt` (`codigo_alterno`),
  KEY `idx_prod_categoria` (`categoria_id`),
  KEY `idx_prod_nombre` (`nombre`),
  KEY `fk_prod_uni` (`unidad_id`),
  KEY `fk_prod_prov` (`proveedor_id`),
  CONSTRAINT `fk_prod_cat` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_prod_prov` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_prod_uni` FOREIGN KEY (`unidad_id`) REFERENCES `unidades` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `productos` (`id`, `codigo`, `codigo_alterno`, `nombre`, `descripcion`, `categoria_id`, `unidad_id`, `proveedor_id`, `precio_costo`, `precio_venta`, `stock_minimo`, `activo`, `created_at`, `updated_at`) VALUES
('1', 'MUE-HLX-DEL-15', 'MUE001', 'Muelle Delantero Toyota Hilux 2015-2023', 'Par de muelles delanteros para Hilux 4x4/4x2 2015-2023', '1', '3', '1', '1250.00', '1850.00', '2.000', '1', '2026-06-13 18:58:03', '2026-06-13 18:58:03'),
('2', 'MUE-HLX-TRA-15', 'MUE002', 'Muelle Trasero Toyota Hilux 2015-2023', 'Par de muelles traseros para Hilux 4x4/4x2 2015-2023', '1', '3', '1', '980.00', '1450.00', '2.000', '1', '2026-06-13 18:58:03', '2026-06-13 18:58:03'),
('3', 'MUE-F150-DEL-15', 'MUE003', 'Muelle Delantero Ford F-150 2015-2022', 'Par de muelles delanteros para F-150 gasolina 2015-2022', '1', '3', '1', '1450.00', '2100.00', '2.000', '1', '2026-06-13 18:58:03', '2026-06-13 18:58:03'),
('4', 'MUE-F150-TRA-15', 'MUE004', 'Muelle Trasero Ford F-150 2015-2022', 'Par de muelles traseros para F-150 2015-2022', '1', '3', '1', '1200.00', '1750.00', '2.000', '1', '2026-06-13 18:58:03', '2026-06-13 18:58:03'),
('5', 'MUE-NP300-DEL-16', 'MUE005', 'Muelle Delantero Nissan NP300 2016-2023', 'Par de muelles delanteros para NP300 Frontier 2016-2023', '1', '3', '4', '1100.00', '1600.00', '2.000', '1', '2026-06-13 18:58:03', '2026-06-13 18:58:03'),
('6', 'AMO-HLX-DEL-KYB', 'AMO001', 'Amortiguador Delantero KYB Toyota Hilux 2015-2023', 'KYB Excel-G para Hilux 2015-2023 por pieza', '2', '1', '2', '850.00', '1250.00', '4.000', '1', '2026-06-13 18:58:03', '2026-06-13 18:58:03'),
('7', 'AMO-HLX-TRA-KYB', 'AMO002', 'Amortiguador Trasero KYB Toyota Hilux 2015-2023', 'KYB Excel-G para Hilux 2015-2023 por pieza', '2', '1', '2', '750.00', '1100.00', '4.000', '1', '2026-06-13 18:58:03', '2026-06-13 18:58:03'),
('8', 'AMO-RNG-DEL-MON', 'AMO003', 'Amortiguador Delantero Monroe Ford Ranger 2019-2023', 'Monroe OESpectrum para Ranger 2019-2023', '2', '1', '3', '920.00', '1350.00', '4.000', '1', '2026-06-13 18:58:03', '2026-06-13 18:58:03'),
('9', 'AMO-RNG-TRA-MON', 'AMO004', 'Amortiguador Trasero Monroe Ford Ranger 2019-2023', 'Monroe OESpectrum para Ranger 2019-2023', '2', '1', '3', '820.00', '1200.00', '4.000', '1', '2026-06-13 18:58:03', '2026-06-13 18:58:03'),
('10', 'AMO-NP300-TRA-GAB', 'AMO005', 'Amortiguador Trasero Gabriel Nissan NP300 2016-2023', 'Gabriel Ultra para NP300 Frontier 2016-2023', '2', '1', '4', '680.00', '980.00', '4.000', '1', '2026-06-13 18:58:03', '2026-06-13 18:58:03'),
('11', 'BUJ-HLX-KIT', 'BUJ001', 'Kit de Bujes de Muelle Toyota Hilux (8 pzas)', '8 bujes de poliuretano para muelles Hilux 2005-2023', '3', '2', '1', '320.00', '520.00', '3.000', '1', '2026-06-13 18:58:03', '2026-06-13 18:58:03'),
('12', 'BUJ-F150-KIT', 'BUJ002', 'Kit de Bujes de Muelle Ford F-150 (8 pzas)', '8 bujes para muelles F-150 2004-2022', '3', '2', '1', '380.00', '580.00', '3.000', '1', '2026-06-13 18:58:03', '2026-06-13 18:58:03'),
('13', 'SLB-HLX-HOJ', 'SLB001', 'Silentblock Hojas Toyota Hilux (par)', 'Par de silentblocks de hojas para Hilux 2005-2023', '3', '3', '1', '180.00', '280.00', '4.000', '1', '2026-06-13 18:58:03', '2026-06-13 18:58:03'),
('14', 'SLB-NP300-HOJ', 'SLB002', 'Silentblock Hojas Nissan NP300 (par)', 'Par silentblocks para NP300 Frontier 2016-2023', '3', '3', '4', '160.00', '250.00', '4.000', '1', '2026-06-13 18:58:03', '2026-06-13 18:58:03'),
('15', 'TOR-UNI-20', 'TOR001', 'Kit Tornillos y Tuercas Muelle Universal (20 pzas)', '20 pzas tornillos tuercas y arandelas alta resistencia', '4', '2', '5', '120.00', '200.00', '5.000', '1', '2026-06-13 18:58:03', '2026-06-13 18:58:03'),
('16', 'TOR-HLX-PER', 'TOR002', 'Perno de Muelle Toyota Hilux (par)', 'Par de pernos centrales de muelle Hilux 2005-2023', '4', '3', '5', '95.00', '160.00', '5.000', '1', '2026-06-13 18:58:03', '2026-06-13 18:58:03'),
('17', 'LUB-GRS-KG', 'LUB001', 'Grasa de Alta Temperatura para Muelles (kg)', 'Grasa de calcio para bujes y hojas soporta 300 C', '5', '6', '5', '85.00', '140.00', '3.000', '1', '2026-06-13 18:58:03', '2026-06-13 18:58:03'),
('18', 'LUB-ANT-LT', 'LUB002', 'Lubricante Antioxidante para Suspension (lt)', 'Lubricante penetrante antioxidante para tornilleria', '5', '5', '5', '75.00', '120.00', '3.000', '1', '2026-06-13 18:58:03', '2026-06-13 18:58:03'),
('19', 'CON-LIJ-240', 'CON001', 'Lija de Agua 240', 'Lija de agua grano 240 para preparacion de superficies', '6', '1', '5', '12.00', '20.00', '10.000', '1', '2026-06-13 18:58:03', '2026-06-13 18:58:03'),
('20', 'CON-DES-AER', 'CON002', 'Desengrasante en Aerosol 500ml', 'Desengrasante multiusos en aerosol 500ml', '6', '1', '5', '45.00', '75.00', '5.000', '1', '2026-06-13 18:58:03', '2026-06-13 18:58:03');

DROP TABLE IF EXISTS `proveedores`;
CREATE TABLE `proveedores` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `razon_social` varchar(200) NOT NULL,
  `rfc` varchar(15) DEFAULT NULL,
  `contacto` varchar(120) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_prov_rfc` (`rfc`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `proveedores` (`id`, `razon_social`, `rfc`, `contacto`, `telefono`, `email`, `notas`, `activo`, `created_at`) VALUES
('1', 'Distribuidora de Refacciones del Norte S.A. de C.V.', 'DRN960101ABC', 'Lic. Raul Mendoza', '662-208-1100', 'ventas@drn.com.mx', 'Proveedor principal Hermosillo', '1', '2026-06-13 18:58:03'),
('2', 'KYB Mexico S.A. de C.V.', 'KYB031015XYZ', 'Ing. Patricia Soto', '55-5262-1200', 'distribuidores@kyb.com.mx', 'Pedido minimo 5000', '1', '2026-06-13 18:58:03'),
('3', 'Monroe Auto Equipment de Mexico S.A. de C.V.', 'MAE881220JKL', 'Sr. Armando Gutierrez', '81-8148-9000', 'agutierrez@monroe.com.mx', 'Amortiguadores Monroe', '1', '2026-06-13 18:58:03'),
('4', 'Partes y Suspensiones del Pacifico S. de R.L.', 'PSP050601DEF', 'Lic. Carmen Torres', '664-686-5500', 'ventas@psp.com.mx', 'Proveedor regional Sonora', '1', '2026-06-13 18:58:03'),
('5', 'Refaccionaria El Eje S.A. de C.V.', 'REE971215GHI', 'Sra. Diana Valenzuela', '662-290-3300', 'info@refaeje.com.mx', 'Consumibles y tornilleria', '1', '2026-06-13 18:58:03');

DROP TABLE IF EXISTS `servicios`;
CREATE TABLE `servicios` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(12,2) NOT NULL DEFAULT 0.00,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `servicios` (`id`, `nombre`, `descripcion`, `precio`, `activo`) VALUES
('1', 'Cambio de muelles delanteros', 'Desmontaje y montaje de muelles delanteros, incluye alineacion basica', '850.00', '1'),
('2', 'Cambio de muelles traseros', 'Desmontaje y montaje de muelles traseros', '750.00', '1'),
('3', 'Cambio de amortiguadores delanteros', 'Desmontaje y montaje de amortiguadores delanteros (par)', '650.00', '1'),
('4', 'Cambio de amortiguadores traseros', 'Desmontaje y montaje de amortiguadores traseros (par)', '600.00', '1'),
('5', 'Cambio de bujes de muelle', 'Sustitucion de bujes en muelles de hojas, limpieza y lubricacion', '450.00', '1'),
('6', 'Reparacion de muelle partido (hojas)', 'Sustitucion de hojas partidas en muelle de ballesta', '950.00', '1'),
('7', 'Cambio de kit de suspension completo', 'Sustitucion total: muelles, amortiguadores y bujes', '2200.00', '1'),
('8', 'Alineacion y balanceo post-suspension', 'Alineacion computarizada y balanceo 4 ruedas', '350.00', '1');

DROP TABLE IF EXISTS `servicios_productos`;
CREATE TABLE `servicios_productos` (
  `servicio_id` smallint(5) unsigned NOT NULL,
  `producto_id` int(10) unsigned NOT NULL,
  `cantidad` decimal(10,3) NOT NULL DEFAULT 1.000,
  PRIMARY KEY (`servicio_id`,`producto_id`),
  KEY `fk_sp_pro` (`producto_id`),
  CONSTRAINT `fk_sp_pro` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sp_ser` FOREIGN KEY (`servicio_id`) REFERENCES `servicios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `servicios_productos` (`servicio_id`, `producto_id`, `cantidad`) VALUES
('1', '1', '1.000'),
('1', '11', '1.000'),
('1', '15', '1.000'),
('1', '17', '0.250'),
('2', '2', '1.000'),
('2', '11', '1.000'),
('2', '15', '1.000'),
('2', '17', '0.250'),
('3', '6', '2.000'),
('3', '19', '1.000'),
('4', '7', '2.000'),
('4', '19', '1.000'),
('5', '11', '1.000'),
('5', '15', '1.000'),
('5', '17', '0.250'),
('7', '1', '1.000'),
('7', '2', '1.000'),
('7', '6', '2.000'),
('7', '7', '2.000'),
('7', '11', '1.000'),
('7', '15', '1.000'),
('7', '17', '0.500');

DROP TABLE IF EXISTS `stock_sucursal`;
CREATE TABLE `stock_sucursal` (
  `producto_id` int(10) unsigned NOT NULL,
  `sucursal_id` tinyint(3) unsigned NOT NULL,
  `cantidad` decimal(10,3) NOT NULL DEFAULT 0.000,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`producto_id`,`sucursal_id`),
  KEY `fk_ss_suc` (`sucursal_id`),
  CONSTRAINT `fk_ss_prod` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ss_suc` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `stock_sucursal` (`producto_id`, `sucursal_id`, `cantidad`, `updated_at`) VALUES
('1', '1', '8.000', '2026-06-13 18:58:03'),
('1', '2', '5.000', '2026-06-13 18:58:03'),
('2', '1', '7.000', '2026-06-13 18:58:03'),
('2', '2', '3.000', '2026-06-13 18:58:03'),
('3', '1', '4.000', '2026-06-13 18:58:03'),
('3', '2', '2.000', '2026-06-13 18:58:03'),
('4', '1', '4.000', '2026-06-13 18:58:03'),
('4', '2', '2.000', '2026-06-13 18:58:03'),
('5', '1', '0.000', '2026-06-13 18:58:03'),
('5', '2', '4.000', '2026-06-13 18:58:03'),
('6', '1', '10.000', '2026-06-14 00:30:04'),
('6', '2', '10.000', '2026-06-14 00:30:04'),
('7', '1', '7.000', '2026-06-14 00:30:04'),
('7', '2', '11.000', '2026-06-14 00:30:04'),
('8', '1', '10.000', '2026-06-13 18:58:03'),
('8', '2', '6.000', '2026-06-13 18:58:03'),
('9', '1', '10.000', '2026-06-13 18:58:03'),
('9', '2', '6.000', '2026-06-13 18:58:03'),
('10', '1', '12.000', '2026-06-13 18:58:03'),
('10', '2', '0.000', '2026-06-13 18:58:03'),
('11', '1', '10.000', '2026-06-13 18:58:03'),
('11', '2', '4.000', '2026-06-13 18:58:03'),
('12', '1', '8.000', '2026-06-13 18:58:03'),
('12', '2', '0.000', '2026-06-13 18:58:03'),
('13', '1', '12.000', '2026-06-13 18:58:03'),
('13', '2', '0.000', '2026-06-13 18:58:03'),
('14', '1', '10.000', '2026-06-13 18:58:03'),
('14', '2', '0.000', '2026-06-13 18:58:03'),
('15', '1', '5.000', '2026-06-14 00:13:06'),
('15', '2', '10.000', '2026-06-13 22:57:58'),
('15', '3', '13.000', '2026-06-14 00:11:21'),
('16', '1', '20.000', '2026-06-13 22:35:29'),
('16', '2', '0.000', '2026-06-13 18:58:03'),
('17', '1', '4.750', '2026-06-13 18:58:03'),
('17', '2', '0.000', '2026-06-13 18:58:03'),
('18', '1', '4.000', '2026-06-13 18:58:03'),
('18', '2', '0.000', '2026-06-13 18:58:03'),
('19', '1', '28.000', '2026-06-13 18:58:03'),
('19', '2', '20.000', '2026-06-13 18:58:03'),
('20', '1', '10.000', '2026-06-13 18:58:03'),
('20', '2', '0.000', '2026-06-13 18:58:03'),
('20', '3', '6.000', '2026-06-14 11:49:13');

DROP TABLE IF EXISTS `sucursales`;
CREATE TABLE `sucursales` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `ciudad` varchar(80) NOT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `foto` varchar(255) DEFAULT NULL,
  `latitud` decimal(10,7) DEFAULT NULL,
  `longitud` decimal(10,7) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `sucursales` (`id`, `nombre`, `ciudad`, `direccion`, `telefono`, `activa`, `created_at`, `foto`, `latitud`, `longitud`) VALUES
('1', 'Taller Muelles Sonora - Hermosillo', 'Hermosillo', 'Blvd. Luis Encinas Johnson 2450, Col. Industrial', '662-212-4500', '1', '2026-06-13 13:09:05', NULL, NULL, NULL),
('2', 'Taller Muelles Sonora - Nogales', 'Nogales', 'Av. Alvaro Obregon 821, Col. Centro', '631-312-1100', '1', '2026-06-13 13:09:05', NULL, NULL, NULL),
('3', 'Matriz', 'Navojoa', 'Periferico', '6424222088', '1', '2026-06-13 22:52:01', 'uploads/fotos/sucursal_20260614_113240_a371c698.png', '27.0534204', '-109.4676422');

DROP TABLE IF EXISTS `traspasos`;
CREATE TABLE `traspasos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `movimiento_salida_id` int(10) unsigned NOT NULL,
  `movimiento_entrada_id` int(10) unsigned DEFAULT NULL,
  `estado` enum('en_transito','recibido','cancelado') NOT NULL DEFAULT 'en_transito',
  `fecha_envio` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_recepcion` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tr_salida` (`movimiento_salida_id`),
  UNIQUE KEY `uq_tr_entrada` (`movimiento_entrada_id`),
  CONSTRAINT `fk_tr_ent` FOREIGN KEY (`movimiento_entrada_id`) REFERENCES `movimientos` (`id`),
  CONSTRAINT `fk_tr_sal` FOREIGN KEY (`movimiento_salida_id`) REFERENCES `movimientos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `traspasos` (`id`, `movimiento_salida_id`, `movimiento_entrada_id`, `estado`, `fecha_envio`, `fecha_recepcion`) VALUES
('1', '10', '11', 'recibido', '2026-06-09 16:00:00', '2026-06-10 08:30:00'),
('2', '12', '24', 'recibido', '2026-06-13 10:00:00', '2026-06-14 00:30:04'),
('3', '14', NULL, 'cancelado', '2026-06-13 22:38:00', NULL),
('4', '15', '16', 'recibido', '2026-06-13 22:54:14', '2026-06-13 22:56:56'),
('5', '17', '18', 'recibido', '2026-06-13 22:57:58', '2026-06-13 22:58:00'),
('6', '19', '21', 'recibido', '2026-06-13 22:58:46', '2026-06-14 00:13:06');

DROP TABLE IF EXISTS `unidades`;
CREATE TABLE `unidades` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `clave` varchar(10) NOT NULL,
  `nombre` varchar(40) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_uni_clave` (`clave`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `unidades` (`id`, `clave`, `nombre`) VALUES
('1', 'PZA', 'Pieza'),
('2', 'KIT', 'Kit'),
('3', 'PAR', 'Par'),
('4', 'JGO', 'Juego'),
('5', 'LT', 'Litro'),
('6', 'KG', 'Kilogramo'),
('7', 'MT', 'Metro'),
('8', 'CJA', 'Caja');

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `rol` enum('admin','almacenista','consulta') NOT NULL DEFAULT 'consulta',
  `sucursal_id` tinyint(3) unsigned DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `ultimo_acceso` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `foto` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_usr_email` (`email`),
  KEY `idx_usr_sucursal` (`sucursal_id`),
  CONSTRAINT `fk_usr_suc` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password_hash`, `rol`, `sucursal_id`, `activo`, `ultimo_acceso`, `created_at`, `foto`) VALUES
('1', 'Administrador General', 'admin@tallermuellessonora.mx', '$2y$12$PbIuZlyyWus0zFJoLqQOa.9vYQTfBI3DxcaK6DHeIxakVxGfElDa.', 'admin', NULL, '1', '2026-06-14 00:24:15', '2026-06-13 18:58:03', 'uploads/fotos/usuario_20260614_113920_f1fc8577.jpg'),
('2', 'Carlos Grijalva Moreno', 'almacen.hmo@tallermuellessonora.mx', '$2y$12$sR2qkUXG5Ipy2eV3Tsfx2uxvPEgsGQ7uQZH5ryDnCKsbz/.6wVH2O', 'almacenista', '1', '1', NULL, '2026-06-13 18:58:03', NULL),
('3', 'Rosa Maria Salazar Lopez', 'almacen.ngl@tallermuellessonora.mx', '$2y$12$sR2qkUXG5Ipy2eV3Tsfx2uxvPEgsGQ7uQZH5ryDnCKsbz/.6wVH2O', 'almacenista', '2', '1', NULL, '2026-06-13 18:58:03', NULL),
('4', 'Roberto Fuentes Acosta', 'consulta@tallermuellessonora.mx', '$2y$12$SfSEsiA6rBmSkb.qFcCuDOTiTQXrifyxSoDvxvT/vrMOqZ424EJh.', 'consulta', NULL, '1', NULL, '2026-06-13 18:58:03', NULL),
('5', 'Ing. Martin Acuna Felix', 'gerente@tallermuellessonora.mx', '$2y$12$R2n7umZka30u9W.W59lacOMZlrE397Z5q5ESPBJSPw/ifUUvOfQO2', 'admin', '1', '1', NULL, '2026-06-13 18:58:03', NULL);

SET FOREIGN_KEY_CHECKS = 1;
