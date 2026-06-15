# Arquitectura del Sistema — Inventario Taller

Sistema web en **PHP puro** (sin framework ni Composer) sobre **MySQL/MariaDB**, con patrón **MVC casero**, enrutamiento por *query string* y front controller único.

---

## 1. Flujo de una petición (MVC casero)

Todo entra por `index.php` (front controller):

```
index.php
  define BASE_PATH
  require config/app.php, config/database.php
  require core/helpers.php, Session, Auth, Model, Controller, Router
  Session::iniciar()
  Router::dispatch()
```

```
Navegador  ?modulo=salidas&accion=nueva
   ▼ index.php → Router::dispatch()
   ▼ guarda de auth global (si no logueado y modulo≠auth → login)
   ▼ require del Controller del módulo
   ▼ sanitizarMetodo(): "confirmar_recepcion" → "confirmarRecepcion"
   ▼ new SalidaController()->nueva()
        ├─ requirePermiso()/requireAdmin()
        ├─ validarCsrf() (POST)
        ├─ SalidaModel (PDO singleton, transacciones, generarFolio)
        └─ render('salidas/nueva', $datos) → layout → vista
   ▼ HTML
```

No hay autoloader: cada clase del núcleo se incluye con `require_once`; los controladores se cargan bajo demanda en el Router.

---

## 2. Router (`core/Router.php`)

Clase estática con tabla de rutas `modulo → [clase, archivo]`. Módulos registrados:

| `?modulo=` | Área |
|------------|------|
| auth, dashboard | Acceso / inicio |
| productos, categorias, unidades, proveedores, mecanicos, servicios | Catálogos |
| entradas, salidas, traspasos, facturas | Operación |
| reportes | Reportes |
| usuarios, sucursales, empresa, **backups**, **basedatos** | Administración |

`dispatch()`: lee `?modulo`/`?accion` (sanitizados con `filter_input`), aplica la guarda de autenticación global, valida módulo/archivo/clase/método y ejecuta. `sanitizarMetodo()` convierte `snake_case` → `camelCase`. Cualquier fallo → `error404()`.

---

## 3. Controlador base (`core/Controller.php`)

- **`render($vista, $datos)`**: deriva `vistaPath` (`"modulo/nombre"` → `modules/modulo/views/nombre.php`), aplica `noCache()`, hace `extract($datos)` e incluye `shared/views/layout.php`.
  - ⚠️ **Reescribe `$usuario`**: tras `extract`, `render()` hace `unset` de las claves reservadas (`flash`, `usuario`, `csrf`, `appName`, `appUrl`) y reasigna `$usuario = Auth::usuario()` (el **usuario logueado**). Por eso, en las vistas `$usuario` es siempre quien tiene la sesión, **nunca** el registro que se edita. Para datos de otro usuario, pásalos en otra clave (p. ej. `$datos['foto']`). *(Esto causó un bug donde la foto del admin aparecía en usuarios sin foto; se corrigió usando `$datos['foto']`.)*
- **`renderSinLayout($archivo, $datos)`**: página limpia sin el layout (impresión, p. ej. el pedido de reabastecimiento).
- **`noCache()`**: envía `Cache-Control: no-store`, `Pragma: no-cache`, `Expires: 0`. Evita formularios/listados cacheados.
- **Guardas**: `requireAuth()`, `requirePermiso($p)`, `requireAdmin()`.
- **CSRF**: `validarCsrf()` valida `$_POST['_csrf']` (con `hash_equals`), responde 403 si falla y renueva el token (un solo uso).
- **Entrada**: `postInt/postStr/postFloat`, `getInt/getStr`.
- **Salida**: `json()`, `redirect()`, `redirectBack()` (solo Referer interno).
- **`auditoria(...)`**: registra en la tabla `auditoria` (tolerante a fallos).

---

## 4. Modelo base (`core/Model.php`)

- **PDO singleton** compartido por todos los modelos.
- Helpers: `query/fetchAll/fetchOne/fetchColumn/execute/lastInsertId/paginar` (siempre sentencias preparadas).
- **Transacciones anidadas** con contador **estático** (`$txLevel`, `$txRollbackOnly`): como todos los modelos comparten la conexión, `FacturaModel::emitir()` puede llamar a `SalidaModel::confirmar()` sin romper la transacción. La transacción real solo se abre/cierra en el nivel externo; un rollback interno marca *rollback-only* y el commit externo revierte todo.
- **`generarFolio($tipo)`**: `PREFIJO-AAAA-#####` (ENT/SAL/TRP/AJU). Traspasos (salida y entrada) comparten prefijo **TRP** y se cuentan juntos para no colisionar en el índice UNIQUE. Sin `LOCK TABLES`.

---

## 5. Autenticación, sesión y roles

- **`Session`**: cookie `HttpOnly`, `SameSite=Strict`, `Secure` bajo HTTPS; `regenerar()` en login; flash de un uso; tokens CSRF.
- **`Auth`**: `intentarLogin()` (guarda en sesión `usuario_id/nombre/email/rol/sucursal_id/`**`foto`**), `logout()`, `usuario()` (incluye `foto`), `tienePermiso()`, `esAdmin()`.
- **Roles**: `admin` (comodín `*`), `almacenista`, `consulta`. Mapa `PERMISOS` en `config/app.php`.
- **Sucursal**: `sucursalFiltro()` (admin = `?sucursal_id=` o todas; otros = la suya) y `sucursalActual()`.
- **Sidebar condicional**: `shared/views/layout.php` envuelve cada enlace en `Auth::tienePermiso()` para que solo aparezcan las secciones accesibles al rol activo.

### Impersonación de usuarios

Permite que el admin opere el sistema como otro usuario sin hacer logout. Solo disponible si `usuario_rol === 'admin'` **y no hay impersonación activa** (protege contra encadenamiento).

```
Admin pulsa "Usar como [usuario]"
  → POST ?modulo=auth&accion=impersonar  (CSRF)
  → AuthController::impersonar()
       validar: rol=admin, !estaImpersonando(), target no-admin, activo
       Auth::iniciarImpersonacion($target)
           guarda _imp_id/nombre/email/foto en sesión
           reemplaza usuario_* con datos del target
           Session::set('_impersonando', true)
  → redirect dashboard  (ya con la sesión del target)

Usuario impersonado pulsa "Volver Admin"
  → POST ?modulo=auth&accion=terminar_impersonacion (CSRF)
  → AuthController::terminarImpersonacion()
       Auth::terminarImpersonacion()
           restaura usuario_* desde _imp_*
           fuerza usuario_rol = ROL_ADMIN
           Session::delete('_impersonando') + claves _imp_*
  → redirect dashboard  (sesión de admin restaurada)
```

Variables de sesión durante impersonación:

| Clave | Contenido |
|-------|-----------|
| `_impersonando` | `true` |
| `_imp_id` | ID del admin original |
| `_imp_nombre/email/foto` | Datos del admin original |
| `usuario_*` | Datos del usuario impersonado |

La barra naranja fija en el layout (`position:fixed; top:56px`) es visible con CSS ajustado (`top:96px` para sidebar/main-content). Se renderiza solo cuando `Auth::estaImpersonando()` es `true`.

---

## 6. Helpers del núcleo

- **`core/Upload.php`** — `Upload::imagen($campo,$prefijo,$actual)`: sube imágenes a `uploads/fotos/`, valida tipo MIME real con `finfo` (jpg/png/webp/gif) y tamaño (≤4 MB), nombre único, borra la anterior.
- **`core/helpers.php`** — imágenes **locales** (SVG Data URI, sin internet): `avatar_iniciales()`, `foto_o_avatar()`, `placeholder_rect()`, `foto_sucursal()`.
- **`core/XlsxWriter.php`** — `.xlsx` (OOXML) en PHP puro: arma el ZIP con `gzdeflate()` (sin `ZipArchive`). Filas agrupadas contraíbles, estilos y *shared strings*.

---

## 7. Mantenimiento de datos

- **Respaldos (`modules/backups`)**: `BackupModel` genera un dump `.sql` completo en PHP puro y lo registra en `backups_log`. Descarga protegida (admin, sin *path traversal*); carpeta `backups/` con `.htaccess` *deny*.
- **Base de datos (`modules/basedatos`)**: `BaseDatosModel` guarda un **seed de ejemplo** (`data/seed_ejemplo.sql`), lo **carga** (restaura, con divisor de SQL que respeta comillas) y **vacía** la base conservando el admin actual y los catálogos base. Carpeta `data/` con `.htaccess` *deny*.

---

## 8. Convenciones

- PHP puro, `declare(strict_types=1)` en `index.php`, `require_once` explícito.
- `utf8mb4` extremo a extremo; zona horaria `America/Hermosillo`.
- Constantes en `config/app.php`: `APP_NAME`, `APP_URL`, `APP_DEBUG`, `ROL_*`, `MOV_*`, `PERMISOS`.
- Seguridad transversal: sentencias preparadas, CSRF en POST, cookies seguras, `password_hash`, cabeceras anti-caché, auditoría.
- Estructura por módulo: `modules/<m>/{Controller, Model(s), views/}`; layout en `shared/views/`; subidas en `uploads/fotos/`; datos/respaldos en `data/` y `backups/`.
