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
#  Variables opcionales:
#      DB_NAME (default: sistema)
#      DB_USER (default: megahnet)
#      ADMIN_EMAIL (default: admin@admin.com)
#      ADMIN_PASS  (default: 12345678)
# ============================================================================
set -euo pipefail

GREEN='\033[0;32m'; YELLOW='\033[1;33m'; RED='\033[0;31m'; NC='\033[0m'
log()  { echo -e "${GREEN}[install]${NC} $*"; }
warn() { echo -e "${YELLOW}[warn]${NC} $*"; }
err()  { echo -e "${RED}[error]${NC} $*" >&2; exit 1; }

[[ $EUID -eq 0 ]] || err "Este script debe ejecutarse como root (sudo bash install.sh)"

PROJECT_DIR="$(cd "$(dirname "$0")" && pwd)"
DB_NAME="${DB_NAME:-sistema}"
DB_USER="${DB_USER:-megahnet}"
ADMIN_EMAIL="${ADMIN_EMAIL:-admin@admin.com}"
ADMIN_PASS="${ADMIN_PASS:-12345678}"

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
    libapache2-mod-php composer git unzip openssl curl >/dev/null

a2enmod rewrite -q

# ---------------------------------------------------------------------------
# 2. .env (genera DB password + crypt key si no existe)
# ---------------------------------------------------------------------------
ENV_FILE="$PROJECT_DIR/.env"
if [[ -f "$ENV_FILE" ]]; then
    log ".env ya existe, reusando credenciales"
    DB_PASS=$(grep -E '^DB_PASSWORD=' "$ENV_FILE" | head -1 | cut -d= -f2-)
else
    log "Generando .env"
    DB_PASS=$(openssl rand -hex 16)
    CRYPT_KEY=$(openssl rand -hex 16)
    SERVER_IP=$(hostname -I | awk '{print $1}')
    cat > "$ENV_FILE" <<EOF
BASE_URL=http://${SERVER_IP}/
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
chown -R www-data:www-data "$PROJECT_DIR"
chmod -R 775 storage facturaelectronica static
chmod 640 "$ENV_FILE" 2>/dev/null || true

# ---------------------------------------------------------------------------
# 7. Apache vhost
# ---------------------------------------------------------------------------
log "Configurando Apache vhost..."
cat > /etc/apache2/sites-available/megahnet.conf <<EOF
<VirtualHost *:80>
    ServerAdmin admin@localhost
    DocumentRoot ${PROJECT_DIR}

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
# 8. Resumen final
# ---------------------------------------------------------------------------
SERVER_IP=$(hostname -I | awk '{print $1}')
echo
echo "=================================================================="
echo "                  Megahnet instalado correctamente"
echo "=================================================================="
echo "  URL:        http://${SERVER_IP}/"
echo "  Usuario:    ${ADMIN_EMAIL}"
echo "  Clave:      ${ADMIN_PASS}"
echo
echo "  Archivos:"
echo "    .env       ${ENV_FILE}"
echo "    Apache log /var/log/apache2/megahnet-{error,access}.log"
echo
echo "  Para actualizar a futuro:"
echo "    cd ${PROJECT_DIR} && git pull && sudo bash install.sh"
echo "=================================================================="
