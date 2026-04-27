#!/usr/bin/env bash
# Instalador / deploy todo-en-uno para el servicio WhatsApp API.
# Idempotente: se puede correr multiples veces sin romper nada.
#
# Uso (en el VPS, como root o con sudo):
#   cd /var/www/html/megahnet/services/whatsapp-api && bash deploy.sh
#
# Si el .env no existe lo crea con valores aleatorios.
# Si Docker no esta instalado lo instala (Debian/Ubuntu).
# Si el vhost de Apache no existe lo crea y habilita.
# Levanta el docker compose y espera a que la API responda.

set -euo pipefail

# === colores ===
RED='\033[0;31m'; GRN='\033[0;32m'; YLW='\033[1;33m'; NC='\033[0m'
log()  { echo -e "${GRN}[deploy]${NC} $*"; }
warn() { echo -e "${YLW}[warn]${NC}  $*" >&2; }
err()  { echo -e "${RED}[error]${NC} $*" >&2; exit 1; }

[ "$(id -u)" -eq 0 ] || err "Hay que correr este script como root (sudo bash deploy.sh)"

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

VHOST_NAME="wa-api"
VHOST_FILE="/etc/apache2/sites-available/${VHOST_NAME}.conf"
DOMAIN="wa-api.sincosto.xyz"
COMPOSE_PROJECT="whatsapp-api"

# === 1. Docker ===
if ! command -v docker >/dev/null 2>&1; then
  log "Docker no esta instalado. Instalando..."
  apt-get update -qq
  apt-get install -y -qq ca-certificates curl gnupg
  install -m 0755 -d /etc/apt/keyrings
  curl -fsSL https://download.docker.com/linux/debian/gpg \
    | gpg --dearmor --yes -o /etc/apt/keyrings/docker.gpg
  chmod a+r /etc/apt/keyrings/docker.gpg
  echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] \
https://download.docker.com/linux/debian $(. /etc/os-release && echo "$VERSION_CODENAME") stable" \
    > /etc/apt/sources.list.d/docker.list
  apt-get update -qq
  apt-get install -y -qq docker-ce docker-ce-cli containerd.io \
    docker-buildx-plugin docker-compose-plugin
  systemctl enable --now docker
else
  log "Docker ya instalado: $(docker --version)"
fi

if ! docker compose version >/dev/null 2>&1; then
  err "docker compose plugin no disponible (revisa la instalacion)"
fi

# === 2. Apache modules + vhost ===
if ! command -v apache2 >/dev/null 2>&1; then
  warn "Apache no esta instalado en el host. Saltando configuracion de vhost."
else
  log "Habilitando mod_proxy y mod_proxy_http..."
  a2enmod proxy proxy_http >/dev/null

  if [ ! -f "$VHOST_FILE" ]; then
    log "Creando vhost ${VHOST_FILE}"
    cp "${SCRIPT_DIR}/apache-vhost.conf" "$VHOST_FILE"
  else
    if ! cmp -s "${SCRIPT_DIR}/apache-vhost.conf" "$VHOST_FILE"; then
      log "Actualizando vhost (cambios detectados)"
      cp "$VHOST_FILE" "${VHOST_FILE}.bak.$(date +%s)"
      cp "${SCRIPT_DIR}/apache-vhost.conf" "$VHOST_FILE"
    else
      log "Vhost sin cambios"
    fi
  fi

  a2ensite "${VHOST_NAME}.conf" >/dev/null
  log "Validando configuracion de Apache..."
  apache2ctl configtest
  systemctl reload apache2
fi

# === 3. .env ===
if [ ! -f .env ]; then
  log ".env no existe. Generando uno con valores aleatorios..."
  POSTGRES_PASSWORD="$(openssl rand -hex 16)"
  JWT_SECRET="$(openssl rand -hex 32)"
  cat > .env <<EOF
PORT=3005
STAGE=prod

POSTGRES_USER=whatsapp
POSTGRES_PASSWORD=${POSTGRES_PASSWORD}
POSTGRES_DB=whatsapp
DB_SYNC=true

JWT_SECRET=${JWT_SECRET}

HOST_WEBSOCKET=
EOF
  chmod 600 .env
  log ".env creado (chmod 600). NO commitear este archivo."
else
  log ".env ya existe, lo dejo como esta"
fi

# === 4. Carpetas runtime ===
mkdir -p sesiones static backups
# El UID 1000 (node) dentro del container necesita escribir aqui
chown -R 1000:1000 sesiones static backups || true

# === 5. Build + up ===
log "Levantando docker compose (build + up)..."
docker compose -p "$COMPOSE_PROJECT" up -d --build

# === 6. Healthcheck ===
log "Esperando a que la API responda en 127.0.0.1:3005..."
ATTEMPTS=30
for i in $(seq 1 $ATTEMPTS); do
  if curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:3005/api/whatsapp/status-sessions \
       | grep -qE "^(200|404)$"; then
    log "API respondiendo OK"
    break
  fi
  sleep 2
  if [ "$i" = "$ATTEMPTS" ]; then
    warn "La API no respondio despues de $((ATTEMPTS*2))s"
    warn "Logs: docker compose -p $COMPOSE_PROJECT logs --tail=50 api"
    exit 1
  fi
done

# === 7. Resumen ===
echo
log "=========================================="
log "  Deploy completo"
log "=========================================="
log "  URL publica:     https://${DOMAIN}/api"
log "  URL local:       http://127.0.0.1:3005/api"
log "  Compose project: ${COMPOSE_PROJECT}"
log "  Logs API:        docker compose -p ${COMPOSE_PROJECT} logs -f api"
log "  Reiniciar API:   docker compose -p ${COMPOSE_PROJECT} restart api"
log "  Bajar todo:      docker compose -p ${COMPOSE_PROJECT} down"
log "=========================================="
