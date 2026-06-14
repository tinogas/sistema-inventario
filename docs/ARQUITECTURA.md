# Arquitectura del Sistema — Inventario Taller

Sistema web en **PHP puro** (sin frameworks ni Composer) con base de datos **MySQL/MariaDB**, desplegado en cPanel. Implementa un patrón **MVC casero** con enrutamiento por query string, una capa de modelos sobre PDO con transacciones anidadas, y un generador de Excel sin dependencias externas.

---

## 1. Patrón MVC casero — flujo de una petición

Todo entra por un **front controller** único: `index.php`. No hay reescritura de URL ni archivos PHP públicos por módulo; el ruteo es por parámetros `?modulo=&accion=`.

### Secuencia de arranque (`index.php`)

```php
define('BASE_PATH', __DIR__);

require_once BASE_PATH . '/config/app.php';       // constantes, zona horaria, PERMISOS
require_once BASE_PATH . '/config/database.php';  // Database (PDO singleton)

require_once BASE_PATH . '/core/Session.php';
require_once BASE_PATH . '/core/Auth.php';
require_once BASE_PATH . '/core/Model.php';
require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/core/Router.php';

Session::iniciar();   // arranca sesión segura (cookie HttpOnly, SameSite=Strict)
Router::dispatch();   // resuelve módulo/acción y ejecuta el método
```

### Flujo completo de una petición

```
Navegador
   │  GET/POST  ?modulo=salidas&accion=confirmar
   ▼
index.php  ── carga config + núcleo, inicia sesión
   ▼
Router::dispatch()
   ├─ Lee ?modulo (def. "dashboard") y ?accion (def. "index")
   ├─ Si NO autenticado y módulo ≠ auth → redirige a login
   ├─ Busca módulo en tabla $rutas → require del archivo del Controller
   ├─ sanitizarMetodo("confirmar") → método camelCase
   └─ new SalidaController()->confirmar()
        ▼
   SalidaController (extends Controller)
   ├─ requireAuth / requirePermiso / requireAdmin  (guardas)
   ├─ validarCsrf()  (en acciones POST)
   ├─ instancia SalidaModel (extends Model)
   │     └─ PDO singleton → fetchAll / execute / transacciones
   ├─ auditoria()  (registra la acción)
   └─ render('salidas/lista', $datos)  ó  json($data)  ó  redirect()
        ▼
   Controller::render()
   ├─ deriva vistaPath: "salidas/lista" → modules/salidas/views/lista.php
   ├─ inyecta $flash, $usuario, $csrf, $appName, $appUrl
   └─ require shared/views/layout.php  (la vista se incluye desde el layout)
        ▼
   HTML al navegador
```

**Responsabilidades por capa:**

| Capa | Clase base | Rol |
|------|-----------|-----|
| Front controller | `index.php` | Punto de entrada único; carga e inicia |
| Router | `Router` | Mapea `modulo/accion` → clase/método; guardas de autenticación globales |
| Controller | `Controller` | Orquesta: validación, permisos, CSRF, llama al modelo, renderiza |
| Model | `Model` | Acceso a datos vía PDO, transacciones, folios, paginación |
| Vista | `modules/*/views/*.php` + `shared/views/layout.php` | Presentación HTML |

---

## 2. Router — mapeo de `?modulo=&accion=`

El `Router` es completamente estático. Mantiene una **tabla de rutas explícita** (no hay autoload por convención de carpetas): cada módulo declara su clase y el archivo a incluir.

### Módulos registrados

| `?modulo=` | Clase | Archivo |
|-----------|-------|---------|
| `auth` | `AuthController` | `modules/auth/AuthController.php` |
| `dashboard` | `DashboardController` | `modules/dashboard/DashboardController.php` |
| `productos` | `ProductoController` | `modules/productos/ProductoController.php` |
| `categorias` | `CategoriaController` | `modules/categorias/CategoriaController.php` |
| `unidades` | `UnidadController` | `modules/unidades/UnidadController.php` |
| `proveedores` | `ProveedorController` | `modules/proveedores/ProveedorController.php` |
| `mecanicos` | `MecanicoController` | `modules/mecanicos/MecanicoController.php` |
| `servicios` | `ServicioController` | `modules/servicios/ServicioController.php` |
| `entradas` | `EntradaController` | `modules/entradas/EntradaController.php` |
| `salidas` | `SalidaController` | `modules/salidas/SalidaController.php` |
| `traspasos` | `TraspasoController` | `modules/traspasos/TraspasoController.php` |
| `reportes` | `ReporteController` | `modules/reportes/ReporteController.php` |
| `usuarios` | `UsuarioController` | `modules/usuarios/UsuarioController.php` |
| `sucursales` | `SucursalController` | `modules/sucursales/SucursalController.php` |
| `facturas` | `FacturaController` | `modules/facturas/FacturaController.php` |
| `empresa` | `EmpresaController` | `modules/empresa/EmpresaController.php` |

### Lógica de despacho (`dispatch()`)

1. Lee parámetros con `filter_input(INPUT_GET, ...)` y `FILTER_SANITIZE_SPECIAL_CHARS`. Valores por defecto: `modulo = 'dashboard'`, `accion = 'index'`.
2. **Guarda global de sesión:** si el usuario no está autenticado y el módulo no es `auth`, redirige a `?modulo=auth&accion=login` (usa `APP_URL`).
3. Valida que el módulo exista en `$rutas`, que el archivo exista (`file_exists`), que la clase exista (`class_exists`) y que el método exista (`method_exists`). Cualquier fallo → **404** (`error404()`, que emite `http_response_code(404)` y termina).
4. Instancia el controlador y ejecuta el método resuelto.

### Convención de nombres: `snake_case` → `camelCase`

`sanitizarMetodo()` convierte la acción a nombre de método:

```php
// "crear_entrada" → "crearEntrada"
$partes = explode('_', preg_replace('/[^a-z0-9_]/', '', strtolower($accion)));
$metodo = array_shift($partes);
foreach ($partes as $parte) {
    $metodo .= ucfirst($parte);
}
```

- Primero baja todo a minúsculas y descarta cualquier carácter que no sea `[a-z0-9_]` (defensa contra inyección de nombres de método).
- El primer segmento queda en minúsculas; los siguientes se capitalizan.
- Ejemplos: `index` → `index`, `confirmar_recepcion` → `confirmarRecepcion`.

---

## 3. Controller base

Clase abstracta de la que heredan todos los controladores. Provee guardas de seguridad, render de vistas, helpers de entrada y utilidades de respuesta.

### Render y derivación de `vistaPath`

`render($vista, $datos)` deriva la ruta física de la vista a partir de una cadena `"modulo/nombre"`:

```php
$partes    = explode('/', $vista);
$vistaPath = count($partes) === 2
    ? BASE_PATH . '/modules/' . $partes[0] . '/views/' . $partes[1] . '.php'  // "salidas/lista"
    : BASE_PATH . '/modules/' . $vista . '.php';
```

Antes de `extract($datos)` **elimina claves reservadas** (`flash`, `usuario`, `csrf`, `appName`, `appUrl`) para evitar colisiones, y luego las reinyecta con valores controlados:

```php
unset($datos['flash'], $datos['usuario'], $datos['csrf'], $datos['appName'], $datos['appUrl']);
extract($datos);

$flash   = Session::getFlash();   // mensajes flash (una sola lectura)
$usuario = Auth::usuario();
$csrf    = Session::getCsrfToken();
$appName = APP_NAME;
$appUrl  = APP_URL;

require_once BASE_PATH . '/shared/views/layout.php';
```

La vista concreta **no se incluye directamente**: se incluye el **layout** (`shared/views/layout.php`), que a su vez incluye `$vistaPath`. También existe `renderSinLayout($archivo, $datos)` para salidas sin plantilla (impresión, fragmentos).

### Seguridad y guardas

| Método | Comportamiento |
|--------|----------------|
| `requireAuth()` | Si no hay sesión, redirige a login |
| `requirePermiso($permiso)` | `requireAuth()` + valida `Auth::tienePermiso()`; si falla, flash de error y redirige al dashboard |
| `requireAdmin()` | `requireAuth()` + `Auth::esAdmin()`; si no, flash y redirige al dashboard |
| `validarCsrf()` | Compara `$_POST['_csrf']` con la sesión; si es inválido responde **403** y `die()`. Tras validar llama a `Session::renovarCsrf()` (token de un solo uso) |

### Helpers de entrada

| Helper | Saneamiento |
|--------|-------------|
| `postInt($key, $def=0)` | cast a `int` |
| `postStr($key, $def='')` | `trim(strip_tags(...))` |
| `postFloat($key, $def=0.0)` | reemplaza `,`→`.` y cast a `float` (acepta separador decimal local) |
| `getInt($key, $def=0)` | cast a `int` desde `$_GET` |
| `getStr($key, $def='')` | `trim(strip_tags(...))` desde `$_GET` |

### Respuesta y redirección

- `json($data, $code=200)`: fija `http_response_code`, header `application/json; charset=utf-8`, emite `json_encode(..., JSON_UNESCAPED_UNICODE)` y termina.
- `redirect($url)`: redirige relativo a `APP_URL`.
- `redirectBack()`: vuelve al `HTTP_REFERER` **solo si es interno** (contiene `APP_URL`); en otro caso al dashboard — protección anti *open redirect*.

### Auditoría

`auditoria($accion, $tabla='', $id=0, $desc='')` inserta en la tabla `auditoria` (usuario, acción, tabla_ref, registro_id, IP, descripción). Está envuelta en `try/catch` vacío: **un fallo de auditoría nunca interrumpe el flujo de negocio**. Usa directamente `Database::getInstance()` (no la capa Model).

---

## 4. Model base

Clase base de todos los modelos. Cada instancia toma la conexión PDO compartida en su constructor:

```php
public function __construct() {
    $this->db = Database::getInstance();   // PDO singleton
}
```

### Acceso a datos

| Método | Retorno |
|--------|---------|
| `query($sql, $params)` | `PDOStatement` (prepare + execute) |
| `fetchAll($sql, $params)` | `array` de filas |
| `fetchOne($sql, $params)` | `?array` (`null` si no hay fila) |
| `fetchColumn($sql, $params)` | primer valor de la primera fila |
| `execute($sql, $params)` | `int` filas afectadas (`rowCount`) |
| `lastInsertId()` | `int` |

### Transacciones anidadas (contador estático) — pieza clave

PDO **no soporta** `beginTransaction()` anidados y **todos los modelos comparten la misma conexión singleton**. El problema concreto: `FacturaModel::emitir()` invoca a `SalidaModel::confirmar()`, y ambos métodos abren transacción sobre la misma conexión. Un `beginTransaction()` interno lanzaría excepción.

La solución es un **contador de nivel estático** (compartido entre todas las instancias) que abre/cierra la transacción **real** solo en el nivel más externo:

```php
private static int  $txLevel        = 0;
private static bool $txRollbackOnly = false;

protected function beginTransaction(): void {
    if (self::$txLevel === 0) {
        $this->db->beginTransaction();   // transacción real solo en el nivel 0
        self::$txRollbackOnly = false;
    }
    self::$txLevel++;
}

protected function commit(): void {
    if (self::$txLevel === 0) return;
    self::$txLevel--;
    if (self::$txLevel === 0) {                 // solo el nivel externo confirma
        if (self::$txRollbackOnly) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            self::$txRollbackOnly = false;
            throw new RuntimeException('Transacción revertida: una operación interna falló.');
        }
        $this->db->commit();
    }
}

protected function rollback(): void {
    if (self::$txLevel === 0) return;
    self::$txLevel--;
    if (self::$txLevel === 0) {
        if ($this->db->inTransaction()) $this->db->rollBack();
        self::$txRollbackOnly = false;
    } else {
        self::$txRollbackOnly = true;   // nivel interno: aplaza el rollback al externo
    }
}
```

**Semántica resultante:**

- `beginTransaction` solo abre la transacción física cuando `txLevel == 0`; en niveles internos solo incrementa el contador.
- `commit` solo confirma físicamente al volver a `txLevel == 0`.
- Si un nivel **interno** hace `rollback`, no revierte de inmediato: marca toda la operación como `txRollbackOnly`. Cuando el nivel externo intenta `commit`, detecta la marca, hace `rollBack()` real y **lanza `RuntimeException`** — evita confirmaciones parciales (atomicidad real del conjunto factura+salida).

### Generación de folios — `generarFolio($tipo)`

Formato: **`PREFIJO-AAAA-00001`** (5 dígitos con relleno). Prefijos por tipo de movimiento:

| Tipo (`MOV_*`) | Prefijo |
|----------------|---------|
| `MOV_ENTRADA` | `ENT` |
| `MOV_SALIDA` | `SAL` |
| `MOV_TRASPASO_SALIDA` | `TRP` |
| `MOV_TRASPASO_ENTRADA` | `TRP` |
| `MOV_AJUSTE` | `AJU` |
| (otro) | `MOV` |

**Manejo especial de traspasos:** salida y entrada de traspaso **comparten el prefijo `TRP`**, así que se cuentan **juntos** (`WHERE tipo IN (traspaso_salida, traspaso_entrada)`). De lo contrario, el primer `traspaso_salida` y el primer `traspaso_entrada` generarían ambos `TRP-AAAA-00001` y colisionarían en el índice `UNIQUE` de `folio`, impidiendo confirmar la recepción.

El conteo es por año (`YEAR(created_at) = :anno`) y el folio resultante es `count + 1`. El comentario del código documenta la decisión de concurrencia: el índice `UNIQUE` sobre `folio` actúa de salvaguarda (un INSERT duplicado falla y revierte); **deliberadamente no se usa `LOCK TABLES`** porque provocaría un commit implícito que rompería la transacción activa — la serialización fina se delega a `GET_LOCK()` advisory en los modelos que lo requieran.

### Paginación — `paginar($sql, $params, $pagina, $porPagina=20)`

Envuelve la consulta como subconsulta para contar el total (`SELECT COUNT(*) FROM (...) AS _t`) y luego aplica `LIMIT/OFFSET`. Devuelve un arreglo con `filas`, `total`, `pagina`, `por_pagina` y `total_paginas` (mínimo 1).

---

## 5. Auth / Session — roles, permisos, CSRF y sucursales

### Roles y permisos (`config/app.php`)

Tres roles, definidos como constantes `ROL_*`:

| Rol | Constante | Alcance |
|-----|-----------|---------|
| Administrador | `ROL_ADMIN` (`admin`) | Permiso comodín `*` (todo) |
| Almacenista | `ROL_ALMACENISTA` (`almacenista`) | Lista granular de permisos de operación |
| Consulta | `ROL_CONSULTA` (`consulta`) | Solo lectura: `dashboard.ver`, `productos.ver`, `reportes.ver` |

Los permisos se declaran en la constante `PERMISOS` (mapa `rol → array de permisos`). El almacenista puede ver/editar productos y mecánicos, crear entradas/salidas/traspasos, confirmar traspasos, y crear/emitir facturas, entre otros; no incluye administración de usuarios ni sucursales (reservado a admin vía `requireAdmin`).

`Auth::tienePermiso($permiso)` resuelve así:

```php
$permisos = PERMISOS[$rol] ?? [];
return in_array('*', $permisos) || in_array($permiso, $permisos);
```

### Autenticación (`Auth`)

- `intentarLogin($email, $password)`: consulta el usuario activo, verifica con `password_verify()` (hashes bcrypt en `password_hash`), actualiza `ultimo_acceso = NOW()`, **regenera el ID de sesión** (`Session::regenerar()`) y guarda en sesión `id`, `nombre`, `email`, `rol`, `sucursal_id`. Regla especial: un usuario no-admin sin sucursal asignada cae por defecto a la **sucursal 1**.
- `usuario()`: reconstruye el arreglo del usuario desde la sesión.
- `estaAutenticado()`: `Session::has('usuario_id')`.
- `esAdmin()`: compara el rol con `ROL_ADMIN`.

### Filtro de sucursal (multi-sucursal)

El sistema opera con **2 sucursales**. Dos métodos rigen qué datos ve cada usuario:

| Método | Lógica |
|--------|--------|
| `sucursalFiltro()` | **Admin:** la sucursal seleccionada en `?sucursal_id=` (validada con `FILTER_VALIDATE_INT`) o `null` = todas. **Almacenista/Consulta:** siempre su sucursal asignada en sesión |
| `sucursalActual()` | La sucursal del usuario en sesión (`null` si es 0/no asignada) |

Es decir, el admin puede inspeccionar cualquier sucursal o el conjunto completo; los demás roles quedan acotados por servidor a su sucursal, sin posibilidad de saltarse el filtro vía query string.

### Sesión (`Session`)

- `iniciar()`: arranca la sesión con cookie `lifetime=0`, `path=APP_URL`, `secure` si hay HTTPS, **`httponly=true`** y **`samesite=Strict`**.
- `destruir()`: vacía `$_SESSION`, expira la cookie y llama a `session_destroy()`.
- `regenerar()`: `session_regenerate_id(true)` (anti fijación de sesión, usado en login).
- **Flash messages:** `flash($tipo, $mensaje)` acumula en `$_SESSION['_flash']`; `getFlash()` lee y borra (consumo único).

### CSRF

| Método | Función |
|--------|---------|
| `getCsrfToken()` | Devuelve el token de sesión; lo genera con `bin2hex(random_bytes(32))` si no existe (32 bytes → 64 hex) |
| `validarCsrf($token)` | Compara con `hash_equals()` (comparación de tiempo constante) |
| `renovarCsrf()` | Regenera el token tras cada validación → **token de un solo uso** |

El layout inyecta `$csrf` en las vistas; los controladores lo verifican con `Controller::validarCsrf()` en cada POST.

---

## 6. XlsxWriter — `.xlsx` puro sin ZipArchive

Generador de archivos Excel **OOXML sin dependencias** (sin `ZipArchive`, sin Composer). Construye manualmente el paquete ZIP usando `gzdeflate()` y `pack()`.

### API pública

```php
$xw = new XlsxWriter();
$xw->addSheet('Stock');
$xw->writeHeader(['Código', 'Producto', 'Total']);
$xw->writeRow(['MUE001', 'Muelle Toyota', 8], 0, true);  // nivel 0, collapsed=true
$xw->writeRow(['', 'Hermosillo', 5],          1);         // nivel 1 (detalle, oculto)
$xw->download('stock.xlsx');
```

| Método | Descripción |
|--------|-------------|
| `addSheet($nombre)` | Crea hoja y la fija como activa |
| `writeHeader($cols)` | Fila de encabezado: negrita + fondo gris, siempre nivel 0 |
| `writeRow($cells, $level=0, $collapsed=false, $bold=false)` | Fila de datos. `level` 0 = resumen, ≥1 = detalle |
| `download($filename)` | Normaliza la extensión a `.xlsx`, emite headers y vuelca el binario |

### Filas agrupadas (outline) contraíbles

El soporte de grupos `+/-` de Excel se logra con atributos OOXML por fila:

- Nivel `0` = fila de resumen; niveles `1+` = detalle.
- Las filas de detalle nacen **ocultas** (`hidden="1"` cuando `level > 0`) y los grupos arrancan **contraídos**: la fila resumen lleva `collapsed="1"` cuando se pasa `$collapsed=true` (solo aplica en nivel 0).
- En la hoja se emite `<sheetFormatPr outlineLevelRow="N">` (N = nivel máximo) para activar los botones `+/-`, y `<outlinePr summaryBelow="0" summaryRight="0"/>` para que el **resumen quede arriba** del detalle.
- Cada fila de detalle lleva `outlineLevel="N"`.

### Estilos y tipos de celda

- **numFmts:** `164` = `#,##0.000` (3 decimales), `165` = `#,##0.00` (moneda, 2 decimales).
- **fonts:** normal y negrita (Calibri 10).
- **fills:** encabezado con relleno sólido gris `FFD9D9D9`.
- **cellXfs:** estilo 0 normal, 1 header (negrita+gris), 2 negrita, 3 numérico 3 decimales.
- Las cadenas se desduplican en una tabla de **shared strings** (`sharedStrings.xml`); los valores numéricos se escriben como `<v>` y reciben el formato de 3 decimales salvo en cabeceras/negritas. Hay *fallback* a `inlineStr` si una cadena no estuviera mapeada.

### Empaquetado ZIP manual (`zipFiles()`)

Por cada parte del paquete OOXML (`[Content_Types].xml`, `_rels/.rels`, `docProps/app.xml`, `xl/workbook.xml`, rels, `styles.xml`, `sharedStrings.xml`, `xl/worksheets/sheet{i}.xml`):

1. Comprime el contenido con `gzdeflate($content, 6)` (deflate raw, método 8).
2. Calcula `crc32($content)` y tamaños comprimido/original.
3. Escribe el **Local File Header** (`pack('VvvvvvVVVvv', 0x04034b50, ...)`) + nombre + datos.
4. Acumula la entrada del **Central Directory** (`0x02014b50`) con el offset del header local.

Al final escribe el **End Of Central Directory** (`0x06054b50`) y concatena `localData . centralDir . eocd`. La conversión índice→letra de columna se hace en `colLetter()` (base-26 estilo Excel: A, B, …, Z, AA, …).

---

## 7. Convenciones globales (`config/app.php`)

| Convención | Valor / nota |
|-----------|--------------|
| Identidad app | `APP_NAME = 'Inventario Taller'`, `APP_VERSION = '1.0.0'` |
| Ruta base | `APP_URL = '/inventario'` (ajustable según dominio en cPanel); usada en redirecciones, cookies y validación de Referer |
| **Zona horaria** | `date_default_timezone_set('America/Hermosillo')` — todas las fechas y folios usan hora de Sonora |
| **Charset** | `utf8mb4` en la conexión (config de base de datos); JSON con `JSON_UNESCAPED_UNICODE`; XML de Excel en UTF-8 |
| Modo depuración | `APP_DEBUG = true` → muestra errores y `error_reporting(E_ALL)`. En producción debe ser `false` (oculta errores) |
| Roles (`ROL_*`) | `ROL_ADMIN='admin'`, `ROL_ALMACENISTA='almacenista'`, `ROL_CONSULTA='consulta'` |
| Tipos de movimiento (`MOV_*`) | `MOV_ENTRADA='entrada'`, `MOV_SALIDA='salida'`, `MOV_TRASPASO_SALIDA='traspaso_salida'`, `MOV_TRASPASO_ENTRADA='traspaso_entrada'`, `MOV_AJUSTE='ajuste'` |
| Permisos | Constante `PERMISOS` (mapa rol→permisos); admin usa comodín `*` |

Las constantes `ROL_*` y `MOV_*` se usan de forma transversal: `Auth` para resolver permisos y comparar roles, y `Model::generarFolio()` para mapear tipos de movimiento a prefijos de folio. Centralizarlas evita literales mágicos dispersos y mantiene consistente la lógica de folios y permisos en todo el sistema.

---

## Resumen de archivos del núcleo

| Archivo | Responsabilidad |
|---------|-----------------|
| `index.php` | Front controller: carga, inicia sesión, despacha |
| `core/Router.php` | Tabla de rutas, despacho, `snake_case`→`camelCase`, 404 |
| `core/Controller.php` | Base de controladores: render+layout, guardas, CSRF, helpers, auditoría, JSON |
| `core/Model.php` | Base de modelos: PDO, transacciones anidadas estáticas, folios, paginación |
| `core/Auth.php` | Login, roles, permisos, filtro de sucursal |
| `core/Session.php` | Sesión segura, flash, CSRF |
| `core/XlsxWriter.php` | Excel OOXML sin dependencias (ZIP manual + deflate, outline contraíble) |
| `config/app.php` | Constantes globales, zona horaria, permisos por rol |
