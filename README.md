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

El script te preguntara dominios y configura todo. Lo que instala y deja corriendo:

- Apache 2 + mods (rewrite/headers/proxy/proxy_http/remoteip) + PHP 8 + MariaDB + Composer
- Crea BD `sistema` + usuario MariaDB + contrasena random en `.env`
- Importa `db/schema.sql` (39 tablas)
- Crea usuario admin por defecto: **admin@admin.com / 12345678**
- Hace `composer install --no-dev`
- Genera los symlinks de routing case-insensitive
- Configura vhost Apache con `ServerName` del dominio que indiques
- Configura `RemoteIPHeader X-Forwarded-For` para que Apache vea la IP real del cliente cuando hay reverse-proxy delante
- **Despliega el servicio WhatsApp API** (Docker compose, NestJS+Baileys, puerto 3005)
  - Genera su `.env` con secretos aleatorios
  - Si das `WA_DOMAIN` configura el vhost Apache reverse-proxy de la API
- Inicializa `storage/alertas-config.json` apuntando a la WA API local
- Bloquea acceso publico a `.env`, `.git`, `composer.json`, `log.txt`

Se puede correr **multiples veces sin romper la instalacion** (es idempotente).
Las respuestas a los prompts quedan cacheadas en `.install.cache` (gitignored).

### SSL

Por defecto el installer **NO** gestiona SSL — Apache queda en HTTP plano detras
de un reverse-proxy externo (Nginx Proxy Manager, Cloudflare, traefik, etc.).
Al final del install te muestra la config exacta para crear el Proxy Host en NPM.

Si prefieres SSL nativo con Let's Encrypt + certbot:

```bash
sudo MANAGE_SSL=1 LE_EMAIL=tu@correo.com bash install.sh
```

### Variables opcionales

Todas se pueden pasar como env para evitar prompts:

```bash
sudo APP_DOMAIN=sistema.midominio.com WA_DOMAIN=wa.midominio.com bash install.sh
sudo ADMIN_EMAIL=tu@correo.com ADMIN_PASS='tu-clave' bash install.sh
sudo DB_NAME=otrabd DB_USER=otroUser bash install.sh
sudo SKIP_WA=1 bash install.sh    # solo PHP, sin servicio WhatsApp
```

### Actualizar el codigo

Hay dos formas. **Desde el panel** (recomendada) o por consola.

#### Desde el panel: Administracion > Actualizacion del sistema

La pantalla compara la version instalada con la publicada en GitHub y, si hay
cambios, permite aplicarlos con un boton. **Antes de tocar nada genera un
respaldo de la base de datos y del codigo**; si el respaldo falla, la
actualizacion se cancela.

Pasos que ejecuta:

1. Comprueba requisitos: repositorio accesible, **sin cambios locales sin subir**
   y que el avance sea fast-forward. Si algo falla, no continua.
2. Respalda en `/root/backups/`:
   - `megahnet-pre-update-<fecha>.sql.gz` (volcado de la base)
   - `megahnet-pre-update-<fecha>.tar.gz` (codigo y runtime)
   - Snapshot de la VM en Proxmox **si** estan definidas las variables `PVE_*`
     del `.env` (es un extra; si falla, solo avisa: el respaldo real son los dos
     archivos anteriores). Se conservan los ultimos 5.
3. `git pull --ff-only`
4. Reaplica los parches de `facturaelectronica/` (esa carpeta no se versiona).
5. Corre `install.sh` con `SKIP_WA=1` (migraciones, dependencias, permisos)
   **sin reiniciar el servicio de WhatsApp**, para no cortar las sesiones.

El progreso y el registro se ven en la misma pantalla. Estado y log quedan en
`storage/update/status.json` y `storage/update/update.log`.

**Requisitos** (los deja listos `install.sh`):

- `/usr/local/sbin/megahnet-update` instalado y regla en `/etc/sudoers.d/megahnet-update`
  que permite a `www-data` ejecutar solo `check` y `run`.
- **Deploy key de solo lectura** registrada en GitHub. `install.sh` genera la clave
  (`/root/.ssh/id_ed25519_megahnet`) y la muestra: hay que pegarla en
  *repo > Settings > Deploy keys > Add deploy key*, **sin marcar "Allow write access"**.
  Hasta que se registre, el boton avisa que no puede descargar cambios.
- Variables opcionales en `.env` para el snapshot: `PVE_API_URL`, `PVE_TOKEN_ID`,
  `PVE_TOKEN_SECRET`, `PVE_NODE`, `PVE_VMID` (el token necesita permiso `VM.Snapshot`).

**Si algo sale mal**, restaurar con los respaldos previos:

```bash
cd /var/www/megahnet
tar xzf /root/backups/megahnet-pre-update-<fecha>.tar.gz          # codigo
zcat /root/backups/megahnet-pre-update-<fecha>.sql.gz | mysql <BD>  # base de datos
```

#### Por consola

```bash
cd /var/www/megahnet
sudo /usr/local/sbin/megahnet-update check   # JSON con version local/remota, no modifica nada
sudo /usr/local/sbin/megahnet-update run     # respaldo + actualizacion

# equivalente manual (sin respaldo automatico):
git pull && sudo bash install.sh
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
