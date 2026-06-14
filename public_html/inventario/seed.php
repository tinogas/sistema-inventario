<?php
/**
 * seed.php — Datos de prueba para Sistema de Inventario
 * ADVERTENCIA: Borra y reemplaza todos los datos existentes.
 * Ejecutar UNA VEZ. ELIMINAR después.
 */
define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/config/database.php';

set_time_limit(180);
header('Content-Type: text/html; charset=utf-8');

$db  = Database::getInstance();
$log = [];
$err = [];

function q(PDO $db, string $sql, array &$log, array &$err, string $desc): void {
    try {
        $db->exec($sql);
        $log[] = $desc;
    } catch (PDOException $e) {
        $err[] = "$desc — " . $e->getMessage();
    }
}

// ─── Hashes bcrypt (cost=12) ────────────────────────────────────────────────
$hAdmin   = password_hash('Admin123!',  PASSWORD_BCRYPT, ['cost' => 12]);
$hAlmacen = password_hash('Almacen1!',  PASSWORD_BCRYPT, ['cost' => 12]);
$hConsult = password_hash('Consulta1!', PASSWORD_BCRYPT, ['cost' => 12]);
$hGerente = password_hash('Gerente1!',  PASSWORD_BCRYPT, ['cost' => 12]);

// ─── Limpiar tablas (orden inverso a FKs) ───────────────────────────────────
$db->exec("SET foreign_key_checks = 0");
foreach (['auditoria','facturas_folios','facturas_detalle','facturas',
          'traspasos','movimientos_detalle','movimientos',
          'stock_sucursal','servicios_productos'] as $t) {
    q($db, "TRUNCATE TABLE `$t`", $log, $err, "TRUNCATE $t");
}
foreach (['usuarios','mecanicos','servicios','productos','proveedores'] as $t) {
    q($db, "DELETE FROM `$t`", $log, $err, "DELETE $t");
    q($db, "ALTER TABLE `$t` AUTO_INCREMENT = 1", $log, $err, "RESET AI $t");
}
$db->exec("SET foreign_key_checks = 1");

// ─── SUCURSALES (actualizar nombres) ────────────────────────────────────────
q($db, "UPDATE sucursales SET nombre='Taller Muelles Sonora - Hermosillo',
    ciudad='Hermosillo', direccion='Blvd. Luis Encinas Johnson 2450, Col. Industrial',
    telefono='662-212-4500' WHERE id=1", $log, $err, "UPDATE sucursal 1 Hermosillo");
q($db, "UPDATE sucursales SET nombre='Taller Muelles Sonora - Nogales',
    ciudad='Nogales', direccion='Av. Álvaro Obregón 821, Col. Centro',
    telefono='631-312-1100' WHERE id=2", $log, $err, "UPDATE sucursal 2 Nogales");

// ─── PROVEEDORES ────────────────────────────────────────────────────────────
q($db, "INSERT INTO proveedores (razon_social, rfc, contacto, telefono, email, notas) VALUES
('Distribuidora de Refacciones del Norte S.A. de C.V.','DRN960101ABC','Lic. Raúl Mendoza','662-208-1100','ventas@drn.com.mx','Proveedor principal, entrega en 24h en Hermosillo'),
('KYB México S.A. de C.V.','KYB031015XYZ','Ing. Patricia Soto','55-5262-1200','distribuidores@kyb.com.mx','Pedido mínimo $5,000, entrega semanal'),
('Monroe Auto Equipment de México S.A. de C.V.','MAE881220JKL','Sr. Armando Gutiérrez','81-8148-9000','agutiérrez@monroe.com.mx','Amortiguadores Monroe y Rancho'),
('Partes y Suspensiones del Pacífico S. de R.L.','PSP050601DEF','Lic. Carmen Torres','664-686-5500','ventas@psp.com.mx','Proveedor regional Sonora y Sinaloa'),
('Refaccionaria El Eje S.A. de C.V.','REE971215GHI','Sra. Diana Valenzuela','662-290-3300','info@refaeje.com.mx','Consumibles y tornillería, local Hermosillo')",
$log, $err, "INSERT proveedores (5)");

// ─── USUARIOS ───────────────────────────────────────────────────────────────
try {
    $db->prepare("INSERT INTO usuarios (nombre, email, password_hash, rol, sucursal_id) VALUES
        ('Administrador General','admin@tallermuellessonora.mx',?,'admin',NULL),
        ('Carlos Grijalva Moreno','almacen.hmo@tallermuellessonora.mx',?,'almacenista',1),
        ('Rosa María Salazar López','almacen.ngl@tallermuellessonora.mx',?,'almacenista',2),
        ('Roberto Fuentes Acosta','consulta@tallermuellessonora.mx',?,'consulta',NULL),
        ('Ing. Martín Acuña Félix','gerente@tallermuellessonora.mx',?,'admin',1)")
        ->execute([$hAdmin, $hAlmacen, $hAlmacen, $hConsult, $hGerente]);
    $log[] = "INSERT usuarios (5)";
} catch (PDOException $e) {
    $err[] = "INSERT usuarios — " . $e->getMessage();
}

// ─── MECÁNICOS ──────────────────────────────────────────────────────────────
q($db, "INSERT INTO mecanicos (nombre, sucursal_id, telefono) VALUES
('Miguel Ángel Rodríguez Soto',1,'662-445-1201'),
('José Luis Espinoza García',1,'662-445-1202'),
('Roberto Hernández Morales',1,'662-445-1203'),
('Francisco Javier López Díaz',1,'662-445-1204'),
('Ernesto Contreras Valdez',2,'631-214-0501'),
('Sergio Alejandro Ruiz Pérez',2,'631-214-0502'),
('Marco Antonio Torres Ríos',2,'631-214-0503')",
$log, $err, "INSERT mecánicos (7)");

// ─── SERVICIOS ──────────────────────────────────────────────────────────────
q($db, "INSERT INTO servicios (nombre, descripcion, precio) VALUES
('Cambio de muelles delanteros','Desmontaje y montaje de muelles delanteros, incluye alineación básica',850.00),
('Cambio de muelles traseros','Desmontaje y montaje de muelles traseros',750.00),
('Cambio de amortiguadores delanteros','Desmontaje y montaje de amortiguadores delanteros (par)',650.00),
('Cambio de amortiguadores traseros','Desmontaje y montaje de amortiguadores traseros (par)',600.00),
('Cambio de bujes de muelle','Sustitución de bujes en muelles de hojas, limpieza y lubricación',450.00),
('Reparación de muelle partido (hojas)','Sustitución de hoja(s) partida(s) en muelle de ballesta',950.00),
('Cambio de kit de suspensión completo','Sustitución total: muelles, amortiguadores y bujes delanteros y traseros',2200.00),
('Alineación y balanceo post-suspensión','Alineación computarizada y balanceo 4 ruedas tras trabajo de suspensión',350.00)",
$log, $err, "INSERT servicios (8)");

// ─── PRODUCTOS ──────────────────────────────────────────────────────────────
// Muelles (categoria_id=1, unidad_id=3 PAR)
q($db, "INSERT INTO productos (codigo,codigo_alterno,nombre,descripcion,categoria_id,unidad_id,proveedor_id,precio_costo,precio_venta,stock_minimo) VALUES
('MUE-HLX-DEL-15','MUE001','Muelle Delantero Toyota Hilux 2015-2023','Par de muelles delanteros para Toyota Hilux 4x4/4x2 2015-2023, carga reforzada',1,3,1,1250.00,1850.00,2.000),
('MUE-HLX-TRA-15','MUE002','Muelle Trasero Toyota Hilux 2015-2023','Par de muelles traseros para Toyota Hilux 4x4/4x2 2015-2023',1,3,1,980.00,1450.00,2.000),
('MUE-F150-DEL-15','MUE003','Muelle Delantero Ford F-150 2015-2022','Par de muelles delanteros para Ford F-150 gasolina/diésel 2015-2022',1,3,1,1450.00,2100.00,2.000),
('MUE-F150-TRA-15','MUE004','Muelle Trasero Ford F-150 2015-2022','Par de muelles traseros para Ford F-150 2015-2022, capacidad 1 ton',1,3,1,1200.00,1750.00,2.000),
('MUE-NP300-DEL-16','MUE005','Muelle Delantero Nissan NP300 2016-2023','Par de muelles delanteros para Nissan NP300 Frontier 2016-2023',1,3,4,1100.00,1600.00,2.000)",
$log, $err, "INSERT productos muelles (5)");

// Amortiguadores (categoria_id=2, unidad_id=1 PZA)
q($db, "INSERT INTO productos (codigo,codigo_alterno,nombre,descripcion,categoria_id,unidad_id,proveedor_id,precio_costo,precio_venta,stock_minimo) VALUES
('AMO-HLX-DEL-KYB','AMO001','Amortiguador Delantero KYB Toyota Hilux 2015-2023','Amortiguador delantero KYB Excel-G para Hilux 2015-2023 (precio por pieza)',2,1,2,850.00,1250.00,4.000),
('AMO-HLX-TRA-KYB','AMO002','Amortiguador Trasero KYB Toyota Hilux 2015-2023','Amortiguador trasero KYB Excel-G para Hilux 2015-2023 (precio por pieza)',2,1,2,750.00,1100.00,4.000),
('AMO-RNG-DEL-MON','AMO003','Amortiguador Delantero Monroe Ford Ranger 2019-2023','Amortiguador delantero Monroe OESpectrum para Ranger 2019-2023',2,1,3,920.00,1350.00,4.000),
('AMO-RNG-TRA-MON','AMO004','Amortiguador Trasero Monroe Ford Ranger 2019-2023','Amortiguador trasero Monroe OESpectrum para Ranger 2019-2023',2,1,3,820.00,1200.00,4.000),
('AMO-NP300-TRA-GAB','AMO005','Amortiguador Trasero Gabriel Nissan NP300 2016-2023','Amortiguador trasero Gabriel Ultra para NP300 Frontier 2016-2023',2,1,4,680.00,980.00,4.000)",
$log, $err, "INSERT productos amortiguadores (5)");

// Bujes y silentblocks (categoria_id=3)
q($db, "INSERT INTO productos (codigo,codigo_alterno,nombre,descripcion,categoria_id,unidad_id,proveedor_id,precio_costo,precio_venta,stock_minimo) VALUES
('BUJ-HLX-KIT','BUJ001','Kit de Bujes de Muelle Toyota Hilux (8 pzas)','Kit completo 8 bujes de poliuretano para muelles Toyota Hilux 2005-2023',3,2,1,320.00,520.00,3.000),
('BUJ-F150-KIT','BUJ002','Kit de Bujes de Muelle Ford F-150 (8 pzas)','Kit completo 8 bujes para muelles Ford F-150 2004-2022',3,2,1,380.00,580.00,3.000),
('SLB-HLX-HOJ','SLB001','Silentblock Hojas Toyota Hilux (par)','Par de silentblocks delanteros de hojas para Toyota Hilux 2005-2023',3,3,1,180.00,280.00,4.000),
('SLB-NP300-HOJ','SLB002','Silentblock Hojas Nissan NP300 (par)','Par de silentblocks de hojas para Nissan NP300 Frontier 2016-2023',3,3,4,160.00,250.00,4.000)",
$log, $err, "INSERT productos bujes/silentblocks (4)");

// Tornillería (categoria_id=4)
q($db, "INSERT INTO productos (codigo,codigo_alterno,nombre,descripcion,categoria_id,unidad_id,proveedor_id,precio_costo,precio_venta,stock_minimo) VALUES
('TOR-UNI-20','TOR001','Kit Tornillos y Tuercas Muelle Universal (20 pzas)','Juego 20 pzas: tornillos, tuercas y arandelas alta resistencia para muelles',4,2,5,120.00,200.00,5.000),
('TOR-HLX-PER','TOR002','Perno de Muelle Toyota Hilux (par)','Par de pernos centrales de muelle para Toyota Hilux 2005-2023, acero SAE grado 8',4,3,5,95.00,160.00,5.000)",
$log, $err, "INSERT productos tornillería (2)");

// Aceites y lubricantes (categoria_id=5)
q($db, "INSERT INTO productos (codigo,codigo_alterno,nombre,descripcion,categoria_id,unidad_id,proveedor_id,precio_costo,precio_venta,stock_minimo) VALUES
('LUB-GRS-KG','LUB001','Grasa de Alta Temperatura para Muelles (kg)','Grasa de calcio para bujes y hojas de muelle, soporta hasta 300°C',5,6,5,85.00,140.00,3.000),
('LUB-ANT-LT','LUB002','Lubricante Antioxidante para Suspensión (lt)','Lubricante penetrante antioxidante para tornillería y partes de suspensión',5,5,5,75.00,120.00,3.000)",
$log, $err, "INSERT productos lubricantes (2)");

// Consumibles (categoria_id=6)
q($db, "INSERT INTO productos (codigo,codigo_alterno,nombre,descripcion,categoria_id,unidad_id,proveedor_id,precio_costo,precio_venta,stock_minimo) VALUES
('CON-LIJ-240','CON001','Lija de Agua #240','Lija de agua grano 240 para preparación de superficies en suspensión',6,1,5,12.00,20.00,10.000),
('CON-DES-AER','CON002','Desengrasante en Aerosol 500ml','Desengrasante multiusos en aerosol 500ml para limpieza de piezas de suspensión',6,1,5,45.00,75.00,5.000)",
$log, $err, "INSERT productos consumibles (2)");

// ─── SERVICIOS_PRODUCTOS ─────────────────────────────────────────────────────
q($db, "INSERT INTO servicios_productos (servicio_id,producto_id,cantidad) VALUES
(1,1,1.000),(1,11,1.000),(1,15,1.000),(1,17,0.250),
(2,2,1.000),(2,11,1.000),(2,15,1.000),(2,17,0.250),
(3,6,2.000),(3,19,1.000),
(4,7,2.000),(4,19,1.000),
(5,11,1.000),(5,15,1.000),(5,17,0.250),
(7,1,1.000),(7,2,1.000),(7,6,2.000),(7,7,2.000),(7,11,1.000),(7,15,1.000),(7,17,0.500)",
$log, $err, "INSERT servicios_productos");

// ─── MOVIMIENTOS ─────────────────────────────────────────────────────────────
// Entradas (ids 1-3)
q($db, "INSERT INTO movimientos (tipo,folio,sucursal_id,proveedor_id,referencia_factura,notas,estado,usuario_id,created_at) VALUES
('entrada','ENT-2026-00001',1,1,'A2026-0115','Compra inicial muelles y amortiguadores Hermosillo','confirmado',2,'2026-01-15 09:30:00'),
('entrada','ENT-2026-00002',1,2,'KYB-2026-0220','Reposición amortiguadores KYB, bujes y consumibles','confirmado',2,'2026-02-20 10:15:00'),
('entrada','ENT-2026-00003',2,1,'A2026-0410','Primera compra sucursal Nogales','confirmado',3,'2026-04-10 08:45:00')",
$log, $err, "INSERT movimientos entradas (3)");

// Salidas (ids 4-9)
q($db, "INSERT INTO movimientos (tipo,folio,sucursal_id,mecanico_id,servicio_id,notas,estado,usuario_id,created_at) VALUES
('salida','SAL-2026-00001',1,1,1,'Hilux 2019 placas TSN-456-B, cambio muelles del','confirmado',2,'2026-02-05 11:00:00'),
('salida','SAL-2026-00002',1,2,3,'F-150 2020 placas ABN-123-K, amortiguadores del','confirmado',2,'2026-02-18 13:30:00'),
('salida','SAL-2026-00003',1,3,7,'Hilux 4x4 2017 placas XYZ-789-A, kit suspensión completo','confirmado',2,'2026-03-08 09:00:00'),
('salida','SAL-2026-00004',2,5,2,'Hilux 2018 placas NDG-321-C, muelles traseros','confirmado',3,'2026-03-22 14:00:00'),
('salida','SAL-2026-00005',1,1,4,'Ranger 2021 placas FRD-654-Z, amortiguadores tra','confirmado',2,'2026-04-15 10:30:00'),
('salida','SAL-2026-00006',2,6,1,'Hilux 2020 placas SNL-987-M, muelles delanteros','confirmado',3,'2026-05-03 12:15:00')",
$log, $err, "INSERT movimientos salidas (6)");

// Traspasos (ids 10-12)
q($db, "INSERT INTO movimientos (tipo,folio,sucursal_id,sucursal_dest_id,notas,estado,usuario_id,created_at) VALUES
('traspaso_salida','TRP-2026-00001',1,2,'Traspaso muelles F-150 a Nogales por demanda','confirmado',2,'2026-03-15 16:00:00'),
('traspaso_entrada','TRP-2026-00002',2,NULL,'Recepción traspaso muelles F-150 de Hermosillo','confirmado',3,'2026-03-16 08:30:00'),
('traspaso_salida','TRP-2026-00003',1,2,'Traspaso amortiguadores KYB en tránsito','confirmado',2,'2026-06-01 17:00:00')",
$log, $err, "INSERT movimientos traspasos (3)");

// ─── MOVIMIENTOS_DETALLE ─────────────────────────────────────────────────────
// ENT-2026-00001 (id=1): muelles y amortiguadores Hilux/F-150
q($db, "INSERT INTO movimientos_detalle (movimiento_id,producto_id,cantidad,precio_unitario) VALUES
(1,1,10.000,1250.00),(1,2,8.000,980.00),(1,3,6.000,1450.00),(1,4,6.000,1200.00),(1,6,16.000,850.00),(1,7,14.000,750.00)",
$log, $err, "Detalle ENT-2026-00001");

// ENT-2026-00002 (id=2): bujes, consumibles, lubricantes
q($db, "INSERT INTO movimientos_detalle (movimiento_id,producto_id,cantidad,precio_unitario) VALUES
(2,8,10.000,920.00),(2,9,10.000,820.00),(2,10,12.000,680.00),(2,11,12.000,320.00),(2,12,8.000,380.00),
(2,13,12.000,180.00),(2,14,10.000,160.00),(2,15,20.000,120.00),(2,16,15.000,95.00),
(2,17,5.000,85.00),(2,18,4.000,75.00),(2,19,30.000,12.00),(2,20,10.000,45.00)",
$log, $err, "Detalle ENT-2026-00002");

// ENT-2026-00003 (id=3): primera entrada Nogales
q($db, "INSERT INTO movimientos_detalle (movimiento_id,producto_id,cantidad,precio_unitario) VALUES
(3,1,6.000,1250.00),(3,2,4.000,980.00),(3,5,4.000,1100.00),(3,6,8.000,850.00),(3,7,8.000,750.00),
(3,8,6.000,920.00),(3,9,6.000,820.00),(3,11,6.000,320.00),(3,15,15.000,120.00),(3,19,20.000,12.00)",
$log, $err, "Detalle ENT-2026-00003");

// SAL-2026-00001 (id=4): Hilux TSN-456-B muelles del
q($db, "INSERT INTO movimientos_detalle (movimiento_id,producto_id,cantidad,precio_unitario) VALUES
(4,1,1.000,1850.00),(4,11,1.000,520.00),(4,15,1.000,200.00)",
$log, $err, "Detalle SAL-2026-00001");

// SAL-2026-00002 (id=5): F-150 ABN-123-K amort del
q($db, "INSERT INTO movimientos_detalle (movimiento_id,producto_id,cantidad,precio_unitario) VALUES
(5,6,2.000,1250.00),(5,19,1.000,20.00)",
$log, $err, "Detalle SAL-2026-00002");

// SAL-2026-00003 (id=6): Hilux XYZ-789-A kit completo
q($db, "INSERT INTO movimientos_detalle (movimiento_id,producto_id,cantidad,precio_unitario) VALUES
(6,1,1.000,1850.00),(6,2,1.000,1450.00),(6,6,2.000,1250.00),(6,7,2.000,1100.00),(6,11,1.000,520.00),(6,15,1.000,200.00)",
$log, $err, "Detalle SAL-2026-00003");

// SAL-2026-00004 (id=7): Hilux NDG-321-C muelles tra (Nogales)
q($db, "INSERT INTO movimientos_detalle (movimiento_id,producto_id,cantidad,precio_unitario) VALUES
(7,2,1.000,1450.00),(7,11,1.000,520.00),(7,15,1.000,200.00)",
$log, $err, "Detalle SAL-2026-00004");

// SAL-2026-00005 (id=8): Ranger FRD-654-Z amort tra
q($db, "INSERT INTO movimientos_detalle (movimiento_id,producto_id,cantidad,precio_unitario) VALUES
(8,7,2.000,1100.00),(8,17,0.250,140.00),(8,19,1.000,20.00)",
$log, $err, "Detalle SAL-2026-00005");

// SAL-2026-00006 (id=9): Hilux SNL-987-M muelles del (Nogales)
q($db, "INSERT INTO movimientos_detalle (movimiento_id,producto_id,cantidad,precio_unitario) VALUES
(9,1,1.000,1850.00),(9,11,1.000,520.00),(9,15,1.000,200.00)",
$log, $err, "Detalle SAL-2026-00006");

// TRP-2026-00001 salida (id=10)
q($db, "INSERT INTO movimientos_detalle (movimiento_id,producto_id,cantidad,precio_unitario) VALUES
(10,3,2.000,1450.00),(10,4,2.000,1200.00)",
$log, $err, "Detalle TRP-2026-00001 salida");

// TRP-2026-00002 entrada (id=11)
q($db, "INSERT INTO movimientos_detalle (movimiento_id,producto_id,cantidad,precio_unitario) VALUES
(11,3,2.000,1450.00),(11,4,2.000,1200.00)",
$log, $err, "Detalle TRP-2026-00002 entrada");

// TRP-2026-00003 salida en tránsito (id=12)
q($db, "INSERT INTO movimientos_detalle (movimiento_id,producto_id,cantidad,precio_unitario) VALUES
(12,6,4.000,850.00),(12,7,4.000,750.00)",
$log, $err, "Detalle TRP-2026-00003 en tránsito");

// ─── TRASPASOS ────────────────────────────────────────────────────────────────
q($db, "INSERT INTO traspasos (movimiento_salida_id,movimiento_entrada_id,estado,fecha_envio,fecha_recepcion) VALUES
(10,11,'recibido','2026-03-15 16:00:00','2026-03-16 08:30:00'),
(12,NULL,'en_transito','2026-06-01 17:00:00',NULL)",
$log, $err, "INSERT traspasos (2: 1 recibido, 1 en tránsito)");

// ─── STOCK_SUCURSAL (valores netos calculados) ───────────────────────────────
// Sucursal 1 (Hermosillo): ENT1+ENT2 - SAL1,2,3,5 - TRP1salida,TRP3salida
$s1 = [1=>8.000,2=>7.000,3=>4.000,4=>4.000,5=>0.000,
       6=>8.000,7=>6.000,8=>10.000,9=>10.000,10=>12.000,
       11=>10.000,12=>8.000,13=>12.000,14=>10.000,15=>18.000,
       16=>15.000,17=>4.750,18=>4.000,19=>28.000,20=>10.000];
// Sucursal 2 (Nogales): ENT3 - SAL4,6 + TRP1entrada (P3,P4) - TRP3 pendiente
$s2 = [1=>5.000,2=>3.000,3=>2.000,4=>2.000,5=>4.000,
       6=>8.000,7=>8.000,8=>6.000,9=>6.000,10=>0.000,
       11=>4.000,12=>0.000,13=>0.000,14=>0.000,15=>13.000,
       16=>0.000,17=>0.000,18=>0.000,19=>20.000,20=>0.000];

$ins = [];
foreach ($s1 as $pid => $qty) $ins[] = "($pid, 1, $qty)";
foreach ($s2 as $pid => $qty) $ins[] = "($pid, 2, $qty)";
q($db, "INSERT INTO stock_sucursal (producto_id, sucursal_id, cantidad) VALUES " . implode(',', $ins),
  $log, $err, "INSERT stock_sucursal (40 registros: 20 productos × 2 sucursales)");

// ─── FACTURAS ────────────────────────────────────────────────────────────────
// Subtotales: materiales + mano de obra
// F1: P6x2@1250=2500 + 650 = 3150
// F2: P1x1@1850+P2x1@1450+P6x2@1250+P7x2@1100+P11x1@520+P15x1@200 + 2200 = 10920
// F3: P7x2@1100+P17x0.25@140+P19x1@20 + 600 = 2855
// F4: P2x1@1450+P11x1@520+P15x1@200 + 750 = 2920
// F5: P1x1@1850+P11x1@520+P15x1@200 + 850 = 3420
q($db, "INSERT INTO facturas (folio,sucursal_id,estado,cliente_nombre,cliente_tel,vh_marca,vh_modelo,vh_anio,vh_placas,mecanico_id,servicio_id,mano_obra,mano_obra_desc,subtotal,total,movimiento_id,usuario_id,fecha_emision,fecha_pago) VALUES
('FAC-HMO-2026-00001',1,'emitida','Juan Carlos Pérez López','662-310-4521','Ford','F-150 XL',2020,'ABN-123-K',2,3,650.00,'Cambio amortiguadores delanteros par',3150.00,3150.00,5,2,'2026-02-18 14:00:00',NULL),
('FAC-HMO-2026-00002',1,'pagada','María Guadalupe García Flores','662-447-8830','Toyota','Hilux 4x4',2017,'XYZ-789-A',3,7,2200.00,'Cambio kit suspensión completo',10920.00,10920.00,6,2,'2026-03-08 10:30:00','2026-03-08 18:00:00'),
('FAC-HMO-2026-00003',1,'pagada','Óscar Sánchez Bustamante','662-181-6647','Ford','Ranger Sport',2021,'FRD-654-Z',1,4,600.00,'Cambio amortiguadores traseros par',2855.00,2855.00,8,2,'2026-04-15 11:00:00','2026-04-15 17:30:00'),
('FAC-NGL-2026-00001',2,'pagada','Pedro Antonio Armenta Ruiz','631-209-5544','Toyota','Hilux SR5',2018,'NDG-321-C',5,2,750.00,'Cambio muelles traseros',2920.00,2920.00,7,3,'2026-03-22 14:30:00','2026-03-22 19:00:00'),
('FAC-NGL-2026-00002',2,'emitida','Lupita Montoya Esquer','631-417-3390','Toyota','Hilux 4x4',2020,'SNL-987-M',6,1,850.00,'Cambio muelles delanteros',3420.00,3420.00,9,3,'2026-05-03 13:00:00',NULL)",
$log, $err, "INSERT facturas (5)");

// ─── FACTURAS_DETALLE ─────────────────────────────────────────────────────────
q($db, "INSERT INTO facturas_detalle (factura_id,producto_id,cantidad,precio_unitario) VALUES
(1,6,2.000,1250.00),
(2,1,1.000,1850.00),(2,2,1.000,1450.00),(2,6,2.000,1250.00),(2,7,2.000,1100.00),(2,11,1.000,520.00),(2,15,1.000,200.00),
(3,7,2.000,1100.00),(3,17,0.250,140.00),(3,19,1.000,20.00),
(4,2,1.000,1450.00),(4,11,1.000,520.00),(4,15,1.000,200.00),
(5,1,1.000,1850.00),(5,11,1.000,520.00),(5,15,1.000,200.00)",
$log, $err, "INSERT facturas_detalle");

// ─── FACTURAS_FOLIOS ──────────────────────────────────────────────────────────
q($db, "INSERT INTO facturas_folios (sucursal_id, anio, ultimo) VALUES (1, 2026, 3),(2, 2026, 2)",
$log, $err, "INSERT facturas_folios");

// ─── AUDITORÍA (muestra) ──────────────────────────────────────────────────────
q($db, "INSERT INTO auditoria (usuario_id,accion,tabla_ref,registro_id,ip,descripcion,created_at) VALUES
(1,'login','usuarios',1,'192.168.1.1','Inicio de sesión exitoso','2026-01-10 08:00:00'),
(2,'login','usuarios',2,'192.168.1.5','Inicio de sesión exitoso','2026-01-15 09:00:00'),
(2,'entrada_crear','movimientos',1,'192.168.1.5','Entrada ENT-2026-00001 confirmada, 6 partidas, proveedor: DRN','2026-01-15 09:30:00'),
(2,'salida_crear','movimientos',4,'192.168.1.5','Salida SAL-2026-00001 confirmada, Hilux TSN-456-B','2026-02-05 11:00:00'),
(2,'entrada_crear','movimientos',2,'192.168.1.5','Entrada ENT-2026-00002 confirmada, 13 partidas, proveedor: KYB','2026-02-20 10:15:00'),
(1,'producto_editar','productos',3,'192.168.1.1','Actualización precio venta Muelle F-150 delantero a $2,100','2026-03-01 11:20:00'),
(2,'traspaso_crear','traspasos',1,'192.168.1.5','Traspaso TRP-2026-00001 enviado a Nogales (P3×2, P4×2)','2026-03-15 16:00:00'),
(3,'traspaso_recibir','traspasos',1,'10.0.0.20','Traspaso TRP-2026-00002 recibido en Nogales','2026-03-16 08:30:00'),
(3,'entrada_crear','movimientos',3,'10.0.0.20','Entrada ENT-2026-00003 confirmada, 10 partidas, primera entrada Nogales','2026-04-10 08:45:00'),
(2,'traspaso_crear','traspasos',2,'192.168.1.5','Traspaso TRP-2026-00003 enviado a Nogales (P6×4, P7×4), en tránsito','2026-06-01 17:00:00')",
$log, $err, "INSERT auditoria (10 registros)");

// ─── SALIDA HTML ──────────────────────────────────────────────────────────────
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Seed — Datos de Prueba</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<style>
body{background:#1a2332;color:#e2e8f0;padding:2rem;}
.card{background:#243447;border:0;}
code{color:#f59e0b;}
</style>
</head>
<body>
<div style="max-width:860px;margin:auto">
<h3 class="mb-3" style="color:#f59e0b">🌱 Seed — Datos de Prueba</h3>

<?php if ($err): ?>
<div class="alert alert-danger">
  <strong>⚠️ Errores (<?= count($err) ?>):</strong>
  <ul class="mb-0 mt-2">
    <?php foreach ($err as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>

<div class="alert alert-success">
  ✓ <?= count($log) ?> operaciones completadas correctamente.
</div>

<div class="card p-4 mb-4">
<h5 style="color:#f59e0b">Credenciales de acceso</h5>
<table class="table table-dark table-sm mb-0">
<thead><tr><th>Email</th><th>Contraseña</th><th>Rol</th><th>Sucursal</th></tr></thead>
<tbody>
<tr><td><code>admin@tallermuellessonora.mx</code></td><td><code>Admin123!</code></td><td>admin</td><td>—</td></tr>
<tr><td><code>almacen.hmo@tallermuellessonora.mx</code></td><td><code>Almacen1!</code></td><td>almacenista</td><td>Hermosillo</td></tr>
<tr><td><code>almacen.ngl@tallermuellessonora.mx</code></td><td><code>Almacen1!</code></td><td>almacenista</td><td>Nogales</td></tr>
<tr><td><code>consulta@tallermuellessonora.mx</code></td><td><code>Consulta1!</code></td><td>consulta</td><td>—</td></tr>
<tr><td><code>gerente@tallermuellessonora.mx</code></td><td><code>Gerente1!</code></td><td>admin</td><td>Hermosillo</td></tr>
</tbody>
</table>
</div>

<div class="card p-4 mb-4">
<h5 style="color:#f59e0b">Resumen de datos cargados</h5>
<ul class="mb-0">
  <li>2 sucursales (Hermosillo y Nogales) actualizadas</li>
  <li>5 proveedores</li>
  <li>5 usuarios (admin, 2 almacenistas, consulta, gerente)</li>
  <li>7 mecánicos (4 HMO, 3 NGL)</li>
  <li>8 servicios</li>
  <li>20 productos (muelles, amortiguadores, bujes, tornillería, lubricantes, consumibles)</li>
  <li>3 entradas de compra (ENT-2026-00001 al 00003)</li>
  <li>6 salidas de servicio (SAL-2026-00001 al 00006)</li>
  <li>2 traspasos: 1 recibido, 1 en tránsito</li>
  <li>5 facturas (3 HMO: 1 emitida, 2 pagadas — 2 NGL: 1 emitida, 1 pagada)</li>
  <li>Stock calculado en 40 registros (20 productos × 2 sucursales)</li>
  <li>10 registros de auditoría</li>
</ul>
</div>

<div class="alert alert-warning">
  <strong>⚠️ IMPORTANTE:</strong> Elimina o renombra <code>seed.php</code> después de usarlo.
</div>

<details class="card p-3">
<summary style="cursor:pointer;color:#94a3b8">Ver log completo (<?= count($log) ?> pasos)</summary>
<ol class="mt-2 small" style="color:#94a3b8">
  <?php foreach ($log as $l): ?><li><?= htmlspecialchars($l) ?></li><?php endforeach; ?>
</ol>
</details>
</div>
</body>
</html>
