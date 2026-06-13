# Sistema de Inventario — Taller de Muelles y Suspensiones

Sistema web de control de inventario para taller de reparaciones vehiculares con soporte multi-sucursal, escáner de código de barras e integración con Proneg.

---

## Requisitos del servidor (cPanel)

| Requisito | Versión mínima |
|-----------|----------------|
| PHP       | 8.1+           |
| MySQL     | 8.0+           |
| Apache    | 2.4+           |
| mod_rewrite | Habilitado    |

---

## Instalación en cPanel

### Paso 1 — Crear la base de datos

1. En cPanel → **MySQL Databases**
2. Crear una base de datos, p.ej. `usuario_inventario`
3. Crear un usuario MySQL y asignarle **todos los privilegios** sobre esa BD
4. Anotar: host (normalmente `localhost`), nombre BD, usuario y contraseña

### Paso 2 — Subir los archivos

1. En cPanel → **File Manager** → `public_html`
2. Crear carpeta `inventario` (o subir al dominio/subdirectorio deseado)
3. Subir todo el contenido de `public_html/inventario/` a esa carpeta
4. Alternativamente, usar **FTP** con FileZilla

### Paso 3 — Configurar la conexión a la BD

Editar `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'usuario_inventario');  // nombre de tu BD
define('DB_USER', 'usuario_mysql');       // usuario MySQL
define('DB_PASS', 'tu_contraseña');       // contraseña MySQL
```

### Paso 4 — Importar la base de datos

1. En cPanel → **phpMyAdmin**
2. Seleccionar la BD recién creada
3. Ir a la pestaña **Importar**
4. Seleccionar el archivo `install.sql`
5. Clic en **Continuar**

### Paso 5 — Configurar la URL base

En `config/app.php` ajustar si el sistema no está en la raíz:

```php
define('APP_URL', '/inventario');  // si está en dominio.com/inventario
// o
define('APP_URL', '');             // si está en la raíz del dominio
```

### Paso 6 — Configurar PHP 8.x en cPanel

1. cPanel → **MultiPHP Manager**
2. Seleccionar el directorio `/inventario`
3. Elegir **PHP 8.2** (o superior)

### Paso 7 — Activar SSL (HTTPS)

1. cPanel → **SSL/TLS** → **AutoSSL** o **Let's Encrypt**
2. Instalar el certificado para tu dominio
3. Las cookies seguras se activan automáticamente con HTTPS

---

## Primer acceso

| Campo      | Valor                 |
|------------|-----------------------|
| URL        | `https://tudominio.com/inventario/` |
| Email      | `admin@taller.com`    |
| Contraseña | `Admin2025!`          |

> **IMPORTANTE:** Cambiar la contraseña del administrador inmediatamente después del primer acceso.

---

## Configuración inicial recomendada

1. **Sucursales**: Actualizar nombre, ciudad y dirección de las 2 sucursales (Módulo → Administración)
2. **Usuarios**: Crear usuarios para cada almacenista y asignarlos a su sucursal
3. **Categorías**: Revisar y ajustar las categorías predefinidas
4. **Unidades**: Agregar unidades adicionales si se necesitan
5. **Proveedores**: Dar de alta los proveedores principales
6. **Productos**: Importar o capturar el catálogo inicial de productos

---

## Uso del escáner de código de barras

El sistema es compatible con cualquier **escáner USB HID** (pistola de código de barras que funciona como teclado).

**No requiere configuración especial.** Al abrir los formularios de Entradas, Salidas o Traspasos, el escáner está activo automáticamente:

1. Apunta el escáner al código de barras del producto
2. El sistema detecta automáticamente el código y busca el producto
3. Si lo encuentra, lo agrega a la lista de partidas
4. Si no lo encuentra, muestra un mensaje de advertencia

**En tablet/celular** sin escáner físico: el campo de código tiene autocompletado por nombre.

---

## Integración con Proneg

Al registrar una **Salida**, el campo "Folio Proneg / Orden de servicio" permite vincular manualmente el número de folio del sistema de facturación. Esto queda registrado en el inventario para trazabilidad.

Para las **Entradas de compra**, se puede subir el **XML CFDI** del proveedor y el sistema extrae automáticamente los conceptos para pre-llenar la entrada.

---

## Estructura de roles

| Rol          | Acceso |
|-------------|--------|
| `admin`     | Total: todas las sucursales, usuarios, configuración |
| `almacenista` | Su sucursal: entradas, salidas, traspasos, reportes |
| `consulta`  | Solo lectura: stock y reportes de su sucursal |

---

## Módulos del sistema

- **Dashboard** — KPIs y alertas de stock
- **Entradas** — Recepción de mercancía con escáner o importación CFDI
- **Salidas** — Despacho vinculado a folio Proneg y mecánico
- **Traspasos** — Movimientos entre sucursales con confirmación
- **Productos** — Catálogo con códigos de barras y stock mínimo
- **Proveedores** — Directorio de proveedores
- **Mecánicos** — Personal por sucursal
- **Servicios** — Tipos de servicio con productos asociados
- **Categorías / Unidades** — Clasificadores del catálogo
- **Reportes** — Stock actual, movimientos, kardex, alertas
- **Usuarios** — Gestión de accesos (solo admin)

---

## Soporte técnico

Para dudas técnicas, revisar primero:
1. Que la BD esté correctamente configurada en `config/database.php`
2. Que el mod_rewrite de Apache esté activo (cPanel → Apache Handlers)
3. Que PHP sea versión 8.1 o superior (cPanel → MultiPHP)
4. Que la carpeta `uploads/cfdi/` tenga permisos de escritura (chmod 755)
