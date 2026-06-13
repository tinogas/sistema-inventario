# ============================================================
# instalar-local.ps1
# Instala y configura el Sistema de Inventario localmente
# EJECUTAR COMO ADMINISTRADOR: clic derecho -> Run as Administrator
# ============================================================

param(
    [string]$DBName = "inventario_taller",
    [string]$DBUser = "root",
    [string]$DBPass = ""   # vacio por defecto en Laragon/XAMPP
)

$ErrorActionPreference = "Stop"
$ProjectSrc = "$PSScriptRoot\public_html\inventario"

Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "  Sistema de Inventario - Instalacion local" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

# ---- 1. Verificar admin ----
$currentPrincipal = [Security.Principal.WindowsPrincipal][Security.Principal.WindowsIdentity]::GetCurrent()
if (-not $currentPrincipal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)) {
    Write-Host "ERROR: Debes ejecutar este script como Administrador." -ForegroundColor Red
    Write-Host "Clic derecho en el archivo -> 'Run as Administrator'" -ForegroundColor Yellow
    Read-Host "Presiona Enter para salir"
    exit 1
}

# ---- 2. Detectar si ya hay un servidor web instalado ----
$laragonPath = ""
$htdocsPath  = ""
$mysqlExe    = ""
$phpExe      = ""

$candidatos = @(
    "C:\laragon",
    "C:\Program Files\laragon",
    "C:\xampp",
    "C:\Program Files\xampp",
    "C:\wamp64",
    "C:\wamp"
)

foreach ($c in $candidatos) {
    if (Test-Path $c) {
        if ($c -like "*laragon*") {
            $htdocsPath = "$c\www"
            $mysqlExe   = Get-ChildItem "$c\bin\mysql" -Recurse -Filter "mysql.exe" -ErrorAction SilentlyContinue | Select-Object -First 1 -ExpandProperty FullName
            $phpExe     = Get-ChildItem "$c\bin\php"   -Recurse -Filter "php.exe"   -ErrorAction SilentlyContinue | Select-Object -First 1 -ExpandProperty FullName
        } elseif ($c -like "*xampp*") {
            $htdocsPath = "$c\htdocs"
            $mysqlExe   = "$c\mysql\bin\mysql.exe"
            $phpExe     = "$c\php\php.exe"
        } elseif ($c -like "*wamp*") {
            $htdocsPath = "$c\www"
            $mysqlExe   = Get-ChildItem "$c\bin\mysql" -Recurse -Filter "mysql.exe" -ErrorAction SilentlyContinue | Select-Object -First 1 -ExpandProperty FullName
            $phpExe     = Get-ChildItem "$c\bin\php"   -Recurse -Filter "php.exe"   -ErrorAction SilentlyContinue | Select-Object -First 1 -ExpandProperty FullName
        }
        $laragonPath = $c
        Write-Host "Servidor encontrado: $c" -ForegroundColor Green
        break
    }
}

# ---- 3. Instalar Laragon si no hay nada instalado ----
if (-not $laragonPath) {
    Write-Host "No se encontro ningun servidor web. Instalando Laragon..." -ForegroundColor Yellow

    $chocoOk = Get-Command choco -ErrorAction SilentlyContinue
    if ($chocoOk) {
        Write-Host "Instalando Laragon con Chocolatey..." -ForegroundColor Cyan
        choco install laragon -y --force
    } else {
        Write-Host "Descargando Laragon directamente..." -ForegroundColor Cyan
        $laragonUrl = "https://github.com/leokhoa/laragon/releases/download/6.0.0/laragon-wamp.exe"
        $installer  = "$env:TEMP\laragon-installer.exe"
        Invoke-WebRequest -Uri $laragonUrl -OutFile $installer -UseBasicParsing
        Write-Host "Ejecutando instalador de Laragon (acepta los defaults)..." -ForegroundColor Yellow
        Start-Process -FilePath $installer -Wait
    }

    # Re-detectar despues de instalar
    foreach ($c in $candidatos) {
        if (Test-Path $c) {
            $laragonPath = $c
            if ($c -like "*laragon*") {
                $htdocsPath = "$c\www"
                $mysqlExe   = Get-ChildItem "$c\bin\mysql" -Recurse -Filter "mysql.exe" -ErrorAction SilentlyContinue | Select-Object -First 1 -ExpandProperty FullName
                $phpExe     = Get-ChildItem "$c\bin\php"   -Recurse -Filter "php.exe"   -ErrorAction SilentlyContinue | Select-Object -First 1 -ExpandProperty FullName
            }
            break
        }
    }
}

if (-not $laragonPath -or -not $htdocsPath) {
    Write-Host "ERROR: No se pudo encontrar ni instalar un servidor web." -ForegroundColor Red
    Write-Host "Instala XAMPP manualmente desde https://www.apachefriends.org y vuelve a ejecutar este script." -ForegroundColor Yellow
    Read-Host "Presiona Enter para salir"
    exit 1
}

Write-Host "Usando htdocs: $htdocsPath" -ForegroundColor Green
Write-Host "MySQL:         $mysqlExe"   -ForegroundColor Green
Write-Host "PHP:           $phpExe"     -ForegroundColor Green
Write-Host ""

# ---- 4. Copiar archivos del proyecto ----
$destino = "$htdocsPath\inventario"
Write-Host "Copiando archivos a $destino ..." -ForegroundColor Cyan
if (Test-Path $destino) {
    Write-Host "La carpeta ya existe, actualizando archivos..." -ForegroundColor Yellow
}
Copy-Item -Path "$ProjectSrc\*" -Destination $destino -Recurse -Force
Write-Host "Archivos copiados." -ForegroundColor Green

# ---- 5. Configurar database.php ----
Write-Host "Configurando database.php..." -ForegroundColor Cyan
$dbConfig = @"
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', '$DBName');
define('DB_USER', '$DBUser');
define('DB_PASS', '$DBPass');
define('DB_CHARSET', 'utf8mb4');

class Database
{
    private static ?PDO `$instance = null;

    public static function getInstance(): PDO
    {
        if (self::`$instance === null) {
            `$dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                DB_HOST, DB_NAME, DB_CHARSET
            );
            self::`$instance = new PDO(`$dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        return self::`$instance;
    }

    private function __construct() {}
    private function __clone() {}
}
"@
Set-Content -Path "$destino\config\database.php" -Value $dbConfig -Encoding UTF8
Write-Host "database.php configurado." -ForegroundColor Green

# ---- 6. Asegurarse de que Apache esta corriendo ----
Write-Host "Iniciando servicios..." -ForegroundColor Cyan
if ($laragonPath -like "*laragon*") {
    $laragonExe = "$laragonPath\laragon.exe"
    if (Test-Path $laragonExe) {
        Start-Process $laragonExe -WindowStyle Normal
        Start-Sleep -Seconds 4
    }
} elseif ($laragonPath -like "*xampp*") {
    $xamppExe = "$laragonPath\xampp-control.exe"
    if (Test-Path $xamppExe) {
        Start-Process $xamppExe
        Write-Host "Abre el Panel de Control de XAMPP y arranca Apache y MySQL manualmente." -ForegroundColor Yellow
        Start-Sleep -Seconds 3
    }
}

# ---- 7. Crear base de datos e importar SQL ----
if ($mysqlExe -and (Test-Path $mysqlExe)) {
    Write-Host "Creando base de datos '$DBName'..." -ForegroundColor Cyan
    $crearDB = "CREATE DATABASE IF NOT EXISTS ``$DBName`` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    & $mysqlExe -u $DBUser --password="$DBPass" -e $crearDB 2>&1 | Out-Null
    Write-Host "Base de datos creada." -ForegroundColor Green

    Write-Host "Importando install.sql..." -ForegroundColor Cyan
    $sqlFile = "$destino\install.sql"
    & $mysqlExe -u $DBUser --password="$DBPass" $DBName < $sqlFile 2>&1 | Out-Null
    Write-Host "SQL importado." -ForegroundColor Green
} else {
    Write-Host "AVISO: No se encontro mysql.exe. Importa install.sql manualmente desde phpMyAdmin." -ForegroundColor Yellow
    Write-Host "       URL: http://localhost/phpmyadmin" -ForegroundColor Yellow
}

# ---- 8. Abrir en el navegador ----
Write-Host ""
Write-Host "============================================" -ForegroundColor Green
Write-Host "  Instalacion completada!" -ForegroundColor Green
Write-Host "============================================" -ForegroundColor Green
Write-Host ""
Write-Host "Accede al sistema:" -ForegroundColor White
Write-Host "  Setup admin: http://localhost/inventario/setup.php" -ForegroundColor Cyan
Write-Host "  Sistema:     http://localhost/inventario/" -ForegroundColor Cyan
Write-Host ""
Write-Host "Abriendo setup.php en el navegador..." -ForegroundColor Yellow
Start-Sleep -Seconds 2
Start-Process "http://localhost/inventario/setup.php"

Read-Host "Presiona Enter para cerrar"
