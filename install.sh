#!/usr/bin/env bash
# ============================================================================
#  Megahnet - Instalador all-in-one para Debian/Ubuntu
#
#  Uso:
#      git clone git@github.com:mtandazo35/megahnet.git
#      cd megahnet
#      sudo bash install.sh
#
#  Idempotente: se puede correr multiples veces sin romper la instalacion.
#
#  El SSL se gestiona externamente (Nginx Proxy Manager / Cloudflare). Apache
#  queda en HTTP plano detras del proxy. Si vas a usar SSL nativo con certbot,
#  exporta MANAGE_SSL=1 antes de correr este script.
#
#  Variables opcionales (todas se pueden pasar como env para evitar prompts):
#      APP_DOMAIN     dominio publico del sistema (ej. sistema.midominio.com)
#      WA_DOMAIN      dominio publico del WhatsApp API (opcional, vacio = solo local)
#      DB_NAME        (default: sistema)
#      DB_USER        (default: megahnet)
#      ADMIN_EMAIL    (default: admin@admin.com)
#      ADMIN_PASS     (default: 12345678)
#      SKIP_WA=1      no instala el servicio WhatsApp
#      MANAGE_SSL=1   instala certbot y solicita cert via Let's Encrypt (off por defecto)
#      LE_EMAIL       email Let's Encrypt (solo si MANAGE_SSL=1)
# ============================================================================
set -euo pipefail

GREEN='\033[0;32m'; YELLOW='\033[1;33m'; RED='\033[0;31m'; NC='\033[0m'
log()  { echo -e "${GREEN}[install]${NC} $*"; }
warn() { echo -e "${YELLOW}[warn]${NC} $*"; }
err()  { echo -e "${RED}[error]${NC} $*" >&2; exit 1; }
ask()  { local p="$1" def="${2:-}" var; if [[ -t 0 ]]; then read -r -p "$p${def:+ [$def]}: " var </dev/tty; echo "${var:-$def}"; else echo "$def"; fi; }

[[ $EUID -eq 0 ]] || err "Este script debe ejecutarse como root (sudo bash install.sh)"

PROJECT_DIR="$(cd "$(dirname "$0")" && pwd)"

# Validar que Apache (www-data) pueda atravesar todo el path hasta el proyecto.
# /root tiene 700 por defecto -> Forbidden con AH00035 search permissions are missing.
# Verificamos cada componente del path.
_check_dir_traversable() {
    local p="$1"
    while [[ "$p" != "/" && -n "$p" ]]; do
        # Si www-data no puede atravesar este componente, falla.
        if ! sudo -u www-data test -x "$p"; then
            return 1
        fi
        p="$(dirname "$p")"
    done
    return 0
}
if ! _check_dir_traversable "$PROJECT_DIR"; then
    err "Apache (www-data) no puede acceder a $PROJECT_DIR (algun directorio padre tiene permisos restrictivos, tipico cuando se clona en /root o un home).
       Mueve el proyecto a /var/www/html y vuelve a correr el installer:
         sudo mv \"$PROJECT_DIR\" /var/www/html/megahnet
         cd /var/www/html/megahnet
         sudo bash install.sh"
fi
DB_NAME="${DB_NAME:-sistema}"
DB_USER="${DB_USER:-megahnet}"
ADMIN_EMAIL="${ADMIN_EMAIL:-admin@admin.com}"
ADMIN_PASS="${ADMIN_PASS:-12345678}"
SKIP_WA="${SKIP_WA:-0}"
MANAGE_SSL="${MANAGE_SSL:-0}"
LE_EMAIL="${LE_EMAIL:-}"

# ---------------------------------------------------------------------------
# 0. Cache de respuestas (asi un re-run no vuelve a preguntar)
# ---------------------------------------------------------------------------
INSTALL_CACHE="$PROJECT_DIR/.install.cache"
if [[ -f "$INSTALL_CACHE" ]]; then
    # shellcheck disable=SC1090
    source "$INSTALL_CACHE"
fi

# ---------------------------------------------------------------------------
# 0.1 Prompts interactivos (se saltan si las vars vienen del entorno)
# ---------------------------------------------------------------------------
echo
echo "==============================================================="
echo " Megahnet — Configuracion del despliegue"
echo "==============================================================="
APP_DOMAIN="${APP_DOMAIN:-${CACHE_APP_DOMAIN:-}}"
if [[ -z "$APP_DOMAIN" ]]; then
    APP_DOMAIN="$(ask 'Dominio publico del sistema (ej. sistema.midominio.com, vacio = usar IP)' '')"
fi
WA_DOMAIN="${WA_DOMAIN:-${CACHE_WA_DOMAIN:-}}"
if [[ "$SKIP_WA" != "1" ]] && [[ -z "${WA_DOMAIN+x}" || -z "$WA_DOMAIN" ]] && [[ -z "${CACHE_WA_DOMAIN+x}" ]]; then
    WA_DOMAIN="$(ask 'Dominio publico del servicio WhatsApp (vacio = solo accesible localmente)' '')"
fi
# Validacion: WA_DOMAIN no puede colisionar con APP_DOMAIN (genera ServerName duplicado)
if [[ -n "$WA_DOMAIN" ]] && [[ "$WA_DOMAIN" == "$APP_DOMAIN" ]]; then
    warn "WA_DOMAIN no puede ser igual a APP_DOMAIN (\"$APP_DOMAIN\"); ignorando WA_DOMAIN."
    WA_DOMAIN=""
fi
LE_EMAIL="${LE_EMAIL:-${CACHE_LE_EMAIL:-}}"
if [[ "$MANAGE_SSL" == "1" ]] && [[ -n "$APP_DOMAIN" ]] && [[ -z "$LE_EMAIL" ]]; then
    LE_EMAIL="$(ask 'Email para Lets Encrypt (avisos de renovacion)' '')"
fi

# Persistir respuestas
cat > "$INSTALL_CACHE" <<EOF
CACHE_APP_DOMAIN="${APP_DOMAIN}"
CACHE_WA_DOMAIN="${WA_DOMAIN}"
CACHE_LE_EMAIL="${LE_EMAIL}"
EOF
chmod 600 "$INSTALL_CACHE"

# ---------------------------------------------------------------------------
# 1. Paquetes del sistema
# ---------------------------------------------------------------------------
log "Instalando paquetes del sistema..."
export DEBIAN_FRONTEND=noninteractive
apt-get update -qq
apt-get install -y -qq \
    apache2 mariadb-server \
    php php-cli php-mysql php-xml php-mbstring php-curl \
    php-gd php-zip php-bcmath php-soap php-intl php-imagick \
    libapache2-mod-php composer git unzip openssl curl ca-certificates >/dev/null

# certbot solo si MANAGE_SSL=1 (por defecto NO — el SSL lo termina un proxy externo)
if [[ "$MANAGE_SSL" == "1" ]] && [[ -n "$APP_DOMAIN" ]]; then
    apt-get install -y -qq certbot python3-certbot-apache >/dev/null
fi

# remoteip: que Apache vea la IP real del cliente cuando hay reverse-proxy delante
a2enmod rewrite headers proxy proxy_http remoteip -q

# Limites de PHP (uploads grandes para respaldos SQL)
PHP_VER=$(ls /etc/php/ 2>/dev/null | head -1)
if [[ -n "$PHP_VER" ]] && [[ -d "/etc/php/$PHP_VER/apache2/conf.d" ]]; then
    cat > "/etc/php/$PHP_VER/apache2/conf.d/99-megahnet-uploads.ini" <<EOF
upload_max_filesize = 512M
post_max_size = 512M
memory_limit = 512M
max_execution_time = 300
max_input_time = 300
max_file_uploads = 50
EOF
    cp "/etc/php/$PHP_VER/apache2/conf.d/99-megahnet-uploads.ini" "/etc/php/$PHP_VER/cli/conf.d/99-megahnet-uploads.ini" 2>/dev/null || true
fi

# ---------------------------------------------------------------------------
# 2. .env (genera DB password + crypt key si no existe)
# ---------------------------------------------------------------------------
ENV_FILE="$PROJECT_DIR/.env"
if [[ -f "$ENV_FILE" ]]; then
    log ".env ya existe, reusando credenciales"
    DB_PASS=$(grep -E '^DB_PASSWORD=' "$ENV_FILE" | head -1 | cut -d= -f2-)
    # Sincronizar BASE_URL con APP_DOMAIN si se cambio (re-run con dominio nuevo)
    if [[ -n "$APP_DOMAIN" ]]; then
        NEW_BASE="https://${APP_DOMAIN}/"
        CUR_BASE=$(grep -E '^BASE_URL=' "$ENV_FILE" | head -1 | cut -d= -f2-)
        if [[ "$CUR_BASE" != "$NEW_BASE" ]]; then
            log "Actualizando BASE_URL en .env: ${CUR_BASE} -> ${NEW_BASE}"
            sed -i "s|^BASE_URL=.*|BASE_URL=${NEW_BASE}|" "$ENV_FILE"
        fi
    fi
else
    log "Generando .env"
    DB_PASS=$(openssl rand -hex 16)
    CRYPT_KEY=$(openssl rand -hex 16)
    SERVER_IP=$(hostname -I | awk '{print $1}')
    # Si hay dominio publico asumimos que el reverse-proxy (NPM/CF/certbot) sirve HTTPS;
    # BASE_URL debe reflejar la URL publica que el cliente ve.
    if [[ -n "$APP_DOMAIN" ]]; then
        BASE_URL_VAL="https://${APP_DOMAIN}/"
    else
        BASE_URL_VAL="http://${SERVER_IP}/"
    fi
    cat > "$ENV_FILE" <<EOF
BASE_URL=${BASE_URL_VAL}
APP_TITLE=MEGAHNET
ENVIROMENT=1
DB_HOST=localhost
DB_USER=${DB_USER}
DB_PASSWORD=${DB_PASS}
DB_NAME=${DB_NAME}
USER_SMTP=
CLAVE_SMTP=
HOST_SMTP=smtp.gmail.com
PUERTO_SMTP=465
SECURE_SMTP=1
CORREO_ALERTAS=
NOMBRE_IMPRESORA=POS-58-Series
CONTACTO=0000000000
CRYPT_KEY=${CRYPT_KEY}
RUTARESPALDOBD=/var/backups/megahnet
EOF
    chmod 640 "$ENV_FILE"
fi

# ---------------------------------------------------------------------------
# 3. Base de datos
# ---------------------------------------------------------------------------
log "Configurando MariaDB (db=${DB_NAME}, user=${DB_USER})..."
mysql -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
ALTER USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;"

# Importar schema solo si la BD esta vacia
TABLE_COUNT=$(mysql -N -B -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='${DB_NAME}'")
if [[ "$TABLE_COUNT" == "0" ]]; then
    if [[ -f "$PROJECT_DIR/db/schema.sql" ]]; then
        log "Importando schema..."
        mysql "${DB_NAME}" < "$PROJECT_DIR/db/schema.sql"
    else
        warn "db/schema.sql no encontrado, BD queda vacia"
    fi
else
    log "BD ya tiene ${TABLE_COUNT} tablas, no se reimporta schema"
fi

# Aplicar migraciones (db/migrations/*.sql) — deben ser idempotentes (usar IF NOT EXISTS).
# Se corren siempre, tanto en BD nueva como en deploys existentes.
if [[ -d "$PROJECT_DIR/db/migrations" ]]; then
    for mig in "$PROJECT_DIR/db/migrations"/*.sql; do
        [[ -f "$mig" ]] || continue
        log "Aplicando migracion $(basename "$mig")..."
        mysql "${DB_NAME}" < "$mig" 2>&1 | grep -v -E '^Warning:' || true
    done
fi

# Crear admin por defecto solo si la tabla usuarios esta vacia
USER_COUNT=$(mysql -N -B "${DB_NAME}" -e "SELECT COUNT(*) FROM usuarios" 2>/dev/null || echo "0")
if [[ "$USER_COUNT" == "0" ]] && [[ -f "$PROJECT_DIR/db/seed-admin.sql" ]]; then
    log "Creando usuario admin (${ADMIN_EMAIL} / ${ADMIN_PASS})"
    HASH=$(php -r "echo password_hash('${ADMIN_PASS}', PASSWORD_DEFAULT);")
    sed "s|__HASH__|${HASH}|g; s|admin@admin.com|${ADMIN_EMAIL}|g" \
        "$PROJECT_DIR/db/seed-admin.sql" | mysql "${DB_NAME}"
elif [[ "$USER_COUNT" -gt "0" ]]; then
    log "Tabla usuarios ya tiene ${USER_COUNT} registros, no se inserta admin"
fi

# Sembrar configuracion con datos demo solo si la tabla esta vacia (install fresco).
# El usuario edita desde Sistema > Configuracion despues.
CFG_COUNT=$(mysql -N -B "${DB_NAME}" -e "SELECT COUNT(*) FROM configuracion" 2>/dev/null || echo "0")
if [[ "$CFG_COUNT" == "0" ]] && [[ -f "$PROJECT_DIR/db/seed-demo.sql" ]]; then
    log "Sembrando configuracion DEMO (edita desde Sistema > Configuracion)"
    mysql "${DB_NAME}" < "$PROJECT_DIR/db/seed-demo.sql"
elif [[ "$CFG_COUNT" -gt "0" ]]; then
    log "Tabla configuracion ya tiene ${CFG_COUNT} registros, no se siembra demo"
fi

# Copiar logo generico si no hay uno custom subido por el cliente.
# Ambos archivos (Logo.jpg + LogoFactura.jpg) estan gitignored para que cada instalacion
# tenga su propio logo. logo-default.jpg si esta versionado y sirve de placeholder.
LOGO_DEFAULT="$PROJECT_DIR/assets/images/logo-default.jpg"
if [[ -f "$LOGO_DEFAULT" ]]; then
    for L in Logo.jpg LogoFactura.jpg; do
        if [[ ! -f "$PROJECT_DIR/assets/images/$L" ]]; then
            cp "$LOGO_DEFAULT" "$PROJECT_DIR/assets/images/$L"
            log "Logo placeholder copiado a assets/images/$L"
        fi
    done
fi

# ---------------------------------------------------------------------------
# 4. Composer
# ---------------------------------------------------------------------------
log "Instalando dependencias PHP (composer install)..."
cd "$PROJECT_DIR"
sudo -u www-data -H composer install --no-interaction --no-dev --ignore-platform-req=php 2>&1 \
    | tail -5 \
    || composer install --no-interaction --no-dev --ignore-platform-req=php 2>&1 | tail -5

# ---------------------------------------------------------------------------
# 5. Symlinks de routing case-insensitive (necesarios solo en Linux)
# ---------------------------------------------------------------------------
log "Creando symlinks de case-routing..."
cd "$PROJECT_DIR/controllers"
[[ -f mikrotiks.php     ]] && { ln -sf mikrotiks.php Mikrotik.php; ln -sf mikrotiks.php Mikrotiks.php; }
[[ -f SriDashboard.php  ]] && ln -sf SriDashboard.php  Sridashboard.php
[[ -f OrdenVenta.php    ]] && ln -sf OrdenVenta.php    Ordenventa.php
[[ -f notaCredito.php   ]] && { ln -sf notaCredito.php Notacredito.php; ln -sf notaCredito.php NotaCredito.php; ln -sf notaCredito.php notacredito.php; }
[[ -f GrupoTrabajos.php ]] && ln -sf GrupoTrabajos.php Grupotrabajos.php
[[ -f RangoIp.php       ]] && ln -sf RangoIp.php       Rangoip.php
cd "$PROJECT_DIR/models"
[[ -f notacreditoModel.php ]] && ln -sf notacreditoModel.php notaCreditoModel.php

# ---------------------------------------------------------------------------
# 6. Carpetas runtime + permisos
# ---------------------------------------------------------------------------
log "Creando carpetas runtime y ajustando permisos..."
cd "$PROJECT_DIR"
mkdir -p storage facturaelectronica static
mkdir -p /var/backups/sistema
chown www-data:www-data /var/backups/sistema
chmod 750 /var/backups/sistema
chown -R www-data:www-data "$PROJECT_DIR"
chmod -R 775 storage facturaelectronica static
chmod 640 "$ENV_FILE" 2>/dev/null || true

# ---------------------------------------------------------------------------
# 7. Apache vhost
# ---------------------------------------------------------------------------
log "Configurando Apache vhost..."
SERVER_NAME_BLOCK=""
if [[ -n "$APP_DOMAIN" ]]; then
    SERVER_NAME_BLOCK="    ServerName ${APP_DOMAIN}"
fi
cat > /etc/apache2/sites-available/megahnet.conf <<EOF
<VirtualHost *:80>
${SERVER_NAME_BLOCK}
    ServerAdmin admin@localhost
    DocumentRoot ${PROJECT_DIR}

    # Si hay un reverse-proxy delante (Nginx Proxy Manager, Cloudflare, etc.),
    # confiar en X-Forwarded-For para registrar la IP real del cliente.
    RemoteIPHeader X-Forwarded-For
    RemoteIPTrustedProxy 127.0.0.1/8
    RemoteIPTrustedProxy 10.0.0.0/8
    RemoteIPTrustedProxy 172.16.0.0/12
    RemoteIPTrustedProxy 192.168.0.0/16

    <Directory ${PROJECT_DIR}>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # Archivos sensibles
    <FilesMatch "(^\.|\.env\$|composer\.(json|lock)\$)">
        Require all denied
    </FilesMatch>
    RedirectMatch 404 /\.git
    <Files "log.txt">
        Require all denied
    </Files>

    ErrorLog \${APACHE_LOG_DIR}/megahnet-error.log
    CustomLog \${APACHE_LOG_DIR}/megahnet-access.log combined
</VirtualHost>
EOF
a2dissite 000-default.conf -q 2>/dev/null || true
a2ensite megahnet.conf -q
apache2ctl configtest 2>&1 | tail -1
systemctl reload apache2 || systemctl restart apache2

# ---------------------------------------------------------------------------
# 7b. SSL nativo (opcional — solo si MANAGE_SSL=1, por defecto NO)
#     Por defecto se asume reverse-proxy externo (Nginx Proxy Manager / CF).
# ---------------------------------------------------------------------------
if [[ "$MANAGE_SSL" == "1" ]] && [[ -n "$APP_DOMAIN" ]] && [[ -n "$LE_EMAIL" ]]; then
    log "Solicitando certificado SSL nativo para ${APP_DOMAIN}..."
    certbot --apache --non-interactive --agree-tos -m "$LE_EMAIL" \
            -d "$APP_DOMAIN" --redirect 2>&1 | tail -10 \
        || warn "certbot fallo. Verifica DNS y puerto 80 abierto."
fi

# ---------------------------------------------------------------------------
# 7c. Servicio WhatsApp API (Docker + Apache reverse proxy opcional)
# ---------------------------------------------------------------------------
WA_DEPLOY="$PROJECT_DIR/services/whatsapp-api/deploy.sh"
if [[ "$SKIP_WA" != "1" ]] && [[ -f "$WA_DEPLOY" ]]; then
    log "Desplegando servicio WhatsApp API..."
    WA_DOMAIN="$WA_DOMAIN" bash "$WA_DEPLOY" || warn "deploy.sh del WA fallo, revisa los logs arriba"

    if [[ "$MANAGE_SSL" == "1" ]] && [[ -n "$WA_DOMAIN" ]] && [[ -n "$LE_EMAIL" ]]; then
        log "Solicitando SSL nativo para ${WA_DOMAIN}..."
        certbot --apache --non-interactive --agree-tos -m "$LE_EMAIL" \
                -d "$WA_DOMAIN" --redirect 2>&1 | tail -10 \
            || warn "certbot fallo para ${WA_DOMAIN}, revisa DNS"
    fi
else
    log "Servicio WhatsApp API omitido (SKIP_WA=1 o no encontrado)"
fi

# Auto-poblar wa_api en alertas-config.json (si no existe)
ALERTAS_CFG="$PROJECT_DIR/storage/alertas-config.json"
if [[ ! -f "$ALERTAS_CFG" ]]; then
    log "Inicializando storage/alertas-config.json con WA API local..."
    mkdir -p "$PROJECT_DIR/storage"
    cat > "$ALERTAS_CFG" <<JSON
{
    "destinatarios": [],
    "wa_api": {
        "base_url": "http://127.0.0.1:3005",
        "session_id": "",
        "phones_alerta": []
    },
    "tipos_activos": {}
}
JSON
    chown www-data:www-data "$ALERTAS_CFG"
    chmod 664 "$ALERTAS_CFG"
fi

# ---------------------------------------------------------------------------
# 8. Resumen final
# ---------------------------------------------------------------------------
SERVER_IP=$(hostname -I | awk '{print $1}')
PUBLIC_URL_HINT="http://${SERVER_IP}/"
[[ -n "$APP_DOMAIN" ]] && PUBLIC_URL_HINT="https://${APP_DOMAIN}/  (terminado por tu reverse-proxy)"
echo
echo "=================================================================="
echo "                  Megahnet instalado correctamente"
echo "=================================================================="
echo "  URL publica:    ${PUBLIC_URL_HINT}"
echo "  URL local:      http://${SERVER_IP}/"
echo "  Usuario:        ${ADMIN_EMAIL}"
echo "  Clave:          ${ADMIN_PASS}"
echo
echo "  Archivos:"
echo "    .env          ${ENV_FILE}"
echo "    cache install ${INSTALL_CACHE}"
echo "    Apache log    /var/log/apache2/megahnet-{error,access}.log"
echo
if [[ "$MANAGE_SSL" != "1" ]]; then
echo "  --- Configurar SSL en tu Nginx Proxy Manager ---"
echo "  1. Proxy Host del sistema:"
echo "     Domain:      ${APP_DOMAIN:-<tu-dominio>}"
echo "     Forward:     http://${SERVER_IP}:80"
echo "     Block common exploits: si"
echo "     Websockets:  si (por si se agrega)"
echo "     SSL:         Request a new certificate (Let's Encrypt) + Force SSL"
if [[ -n "$WA_DOMAIN" ]]; then
echo "  2. Proxy Host del WhatsApp API:"
echo "     Domain:      ${WA_DOMAIN}"
echo "     Forward:     http://${SERVER_IP}:80   (Apache enruta por Host)"
echo "     SSL:         Request a new certificate (Let's Encrypt) + Force SSL"
fi
echo
fi
echo "  Para actualizar a futuro:"
echo "    cd ${PROJECT_DIR} && git pull && sudo bash install.sh"
echo "=================================================================="
