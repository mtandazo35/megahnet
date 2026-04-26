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

- PHP 8.x (Apache + mod_php)
- MariaDB / MySQL 5.7+
- Composer
- Bootstrap 5 + jQuery + DataTables (frontend)

## Instalacion rapida (Debian/Ubuntu fresh)

```bash
git clone git@github.com:mtandazo35/megahnet.git
cd megahnet
sudo bash install.sh
```

El script instala todo lo necesario y deja el sistema corriendo:

- Apache 2 + mod_rewrite + PHP 8 + MariaDB + Composer
- Crea BD `sistema` + usuario MariaDB + contrasena random en `.env`
- Importa `db/schema.sql` (39 tablas)
- Crea usuario admin por defecto: **admin@admin.com / 12345678**
- Hace `composer install --no-dev`
- Genera los symlinks de routing case-insensitive
- Configura vhost Apache (acceso por IP, sin dominio)
- Bloquea acceso publico a `.env`, `.git`, `composer.json`, `log.txt`

Se puede correr **multiples veces sin romper la instalacion** (es idempotente).

### Variables opcionales

```bash
sudo ADMIN_EMAIL=tu@correo.com ADMIN_PASS='tu-clave' bash install.sh
sudo DB_NAME=otrabd DB_USER=otroUser bash install.sh
```

### Actualizar el codigo

```bash
cd /var/www/html/megahnet
git pull
sudo bash install.sh   # idempotente, refresca composer/symlinks/vhost
```

## Estructura

```
controllers/      Controladores MVC
models/           Modelos / acceso a BD
views/            Vistas + templates
config/           Configuracion (Config.php lee de .env)
libraries/        Librerias propias
cron/             Tareas programadas
assets/           CSS / JS / imagenes
db/
  schema.sql       Estructura de BD (sin datos)
  seed-admin.sql   Plantilla de usuario admin inicial
install.sh         Instalador all-in-one
.env.example       Plantilla de variables de entorno
```

## Carpetas runtime (no versionadas)

`storage/` `static/` `facturaelectronica/` se crean automaticamente con
permisos `www-data:775`.

## Configuracion / secretos

Toda la configuracion sensible vive en `.env` (gitignored). `config/Config.php`
incluye un mini-loader que parsea `.env` al arranque sin requerir composer.

Variables principales:

- `BASE_URL` - URL publica del sistema
- `DB_HOST`, `DB_USER`, `DB_PASSWORD`, `DB_NAME` - credenciales MariaDB
- `USER_SMTP`, `CLAVE_SMTP`, `HOST_SMTP`, `PUERTO_SMTP` - SMTP para correos
- `CRYPT_KEY` - clave de cifrado interno
- `MIKROTIK_*` - credenciales del router (opcional)

Ver `.env.example` para el listado completo.

## Notas

- Los symlinks de routing en `controllers/` y `models/` se crean por
  `install.sh` porque Linux es case-sensitive (Windows no los necesita).
- El admin por defecto usa bcrypt (`PASSWORD_DEFAULT`). **Cambiar la clave en
  el primer login.**
- El instalador detecta si ya hay tablas/usuarios y NO sobrescribe datos
  existentes.
