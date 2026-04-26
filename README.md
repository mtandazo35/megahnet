# Sistema MEGAHNET

Sistema de gestion + facturacion electronica SRI (Ecuador) en PHP.

## Modulos

- Clientes / contratos / planes
- Facturacion electronica SRI (firma + envio + autorizacion)
- Notas de credito / debito, guias de remision, retenciones
- Creditos y abonos
- Ordenes de venta
- Inventario
- Cron de mantenimiento (renovaciones, suspensiones)
- Notificaciones por email + WhatsApp (NestJS+Baileys)
- Integracion Mikrotik (RouterOS API)

## Stack

- PHP 8.x (Apache/Nginx + PHP-FPM)
- MySQL 5.7+
- Composer
- Bootstrap 5 + jQuery + DataTables (frontend)

## Setup local

```bash
git clone <repo-url> megahnet
cd megahnet

# 1) Dependencias
composer install

# 2) Configuracion
cp .env.example .env
# Editar .env con credenciales reales

# 3) Base de datos
mysql -u root -p -e "CREATE DATABASE sistema CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
# Importar tu dump de BD

# 4) Carpetas runtime (no se versionan)
mkdir -p storage static facturaelectronica
chmod -R 775 storage static facturaelectronica

# 5) Servidor web
# Apuntar DocumentRoot a la raiz del proyecto.
# Reescritura via .htaccess (incluido).
```

## Estructura

```
controllers/   Controladores MVC
models/        Modelos / acceso a BD
views/         Vistas + templates
config/        Configuracion (Config.php sanitizado)
libraries/     Librerias propias
cron/          Tareas programadas
assets/        CSS / JS / imagenes
facturaelectronica/  XMLs SRI (no en git)
storage/       Configs runtime + sesiones (no en git)
```

## Notas para deploy en Linux

El sistema requiere algunos symlinks de routing case-insensitive en `controllers/`
y `models/` (Linux es case-sensitive). Tras `git clone` ejecutar:

```bash
# Aliases de case-routing (no se incluyen en el repo)
cd controllers
ln -sf mikrotiks.php  Mikrotik.php
ln -sf mikrotiks.php  Mikrotiks.php
ln -sf SriDashboard.php Sridashboard.php
ln -sf OrdenVenta.php Ordenventa.php
ln -sf notaCredito.php Notacredito.php
ln -sf notaCredito.php NotaCredito.php
ln -sf notaCredito.php notacredito.php
ln -sf GrupoTrabajos.php Grupotrabajos.php
ln -sf RangoIp.php Rangoip.php
cd ../models
ln -sf notacreditoModel.php notaCreditoModel.php
```

En Windows (case-insensitive) no son necesarios.

## Credenciales y secretos

Este repo NO contiene credenciales reales. Toda configuracion sensible se carga
via env-vars (ver `.env.example`). El archivo `config/Config.php` lee de
`getenv()` con fallbacks seguros para desarrollo.
