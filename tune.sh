#!/usr/bin/env bash
# ============================================================================
#  Megahnet - Tuning de rendimiento (Debian/Ubuntu)
#
#  Aplica swap, PHP OPcache+JIT, MariaDB innodb tuning, Apache compresion/cache
#  y sysctl networking. Idempotente: corrible multiples veces.
#
#  Uso:
#      sudo bash tune.sh
#
#  Variables opcionales:
#      SWAP_SIZE_GB        tamano del swapfile (default: 4)
#      INNODB_BUFFER_MB    innodb_buffer_pool_size en MB (default: 25% RAM, min 256, max 2048)
#      OPCACHE_MEM_MB      opcache.memory_consumption en MB (default: 192)
#      OPCACHE_JIT_MB      opcache.jit_buffer_size en MB (default: 64)
#      SKIP_SWAP=1         no tocar swap
#      SKIP_PHP=1          no tocar PHP/OPcache
#      SKIP_MARIADB=1      no tocar MariaDB
#      SKIP_APACHE=1       no tocar Apache
#      SKIP_SYSCTL=1       no tocar sysctl
# ============================================================================
set -euo pipefail

GREEN='\033[0;32m'; YELLOW='\033[1;33m'; RED='\033[0;31m'; NC='\033[0m'
log()  { echo -e "${GREEN}[tune]${NC} $*"; }
warn() { echo -e "${YELLOW}[warn]${NC} $*"; }
err()  { echo -e "${RED}[error]${NC} $*" >&2; exit 1; }

[[ $EUID -eq 0 ]] || err "Debe correrse como root (sudo bash tune.sh)"

SWAP_SIZE_GB="${SWAP_SIZE_GB:-4}"
OPCACHE_MEM_MB="${OPCACHE_MEM_MB:-192}"
OPCACHE_JIT_MB="${OPCACHE_JIT_MB:-64}"
SKIP_SWAP="${SKIP_SWAP:-0}"
SKIP_PHP="${SKIP_PHP:-0}"
SKIP_MARIADB="${SKIP_MARIADB:-0}"
SKIP_APACHE="${SKIP_APACHE:-0}"
SKIP_SYSCTL="${SKIP_SYSCTL:-0}"

# innodb_buffer_pool: 25% de RAM si no se setea, con piso 256MB y techo 2048MB
if [[ -z "${INNODB_BUFFER_MB:-}" ]]; then
    TOTAL_MB=$(awk '/MemTotal/{print int($2/1024)}' /proc/meminfo)
    INNODB_BUFFER_MB=$(( TOTAL_MB / 4 ))
    [[ $INNODB_BUFFER_MB -lt 256 ]]  && INNODB_BUFFER_MB=256
    [[ $INNODB_BUFFER_MB -gt 2048 ]] && INNODB_BUFFER_MB=2048
fi

# ---------------------------------------------------------------------------
# 1. SWAP
# ---------------------------------------------------------------------------
if [[ "$SKIP_SWAP" != "1" ]]; then
    if swapon --show | grep -q .; then
        log "Swap ya activo: $(swapon --show --noheadings | head -1)"
    else
        log "Creando swapfile de ${SWAP_SIZE_GB}G..."
        fallocate -l "${SWAP_SIZE_GB}G" /swapfile
        chmod 600 /swapfile
        mkswap /swapfile >/dev/null
        swapon /swapfile
        grep -q '/swapfile' /etc/fstab || echo '/swapfile none swap sw 0 0' >> /etc/fstab
    fi
    cat > /etc/sysctl.d/99-swap.conf <<EOF
vm.swappiness=10
vm.vfs_cache_pressure=50
EOF
    sysctl -p /etc/sysctl.d/99-swap.conf >/dev/null
fi

# ---------------------------------------------------------------------------
# 2. PHP OPcache + JIT + realpath cache
# ---------------------------------------------------------------------------
if [[ "$SKIP_PHP" != "1" ]] && command -v php >/dev/null; then
    PHP_VER=$(ls /etc/php/ 2>/dev/null | head -1)
    if [[ -n "$PHP_VER" ]] && [[ -d "/etc/php/$PHP_VER/apache2/conf.d" ]]; then
        log "Configurando OPcache (memory=${OPCACHE_MEM_MB}M, jit=${OPCACHE_JIT_MB}M)..."
        cat > "/etc/php/$PHP_VER/apache2/conf.d/99-opcache-tuning.ini" <<EOF
opcache.enable=1
opcache.enable_cli=0
opcache.memory_consumption=${OPCACHE_MEM_MB}
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.validate_timestamps=1
opcache.revalidate_freq=60
opcache.fast_shutdown=1
opcache.jit=tracing
opcache.jit_buffer_size=${OPCACHE_JIT_MB}M
realpath_cache_size=4096k
realpath_cache_ttl=600
EOF
    else
        warn "No encontre /etc/php/*/apache2/conf.d, salteo OPcache"
    fi
fi

# ---------------------------------------------------------------------------
# 3. MARIADB innodb tuning + slow log
# ---------------------------------------------------------------------------
if [[ "$SKIP_MARIADB" != "1" ]] && [[ -d /etc/mysql/mariadb.conf.d ]]; then
    log "MariaDB: innodb_buffer_pool=${INNODB_BUFFER_MB}M"
    cat > /etc/mysql/mariadb.conf.d/99-megahnet-tuning.cnf <<EOF
[mysqld]
innodb_buffer_pool_size = ${INNODB_BUFFER_MB}M
innodb_log_file_size = 128M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT
innodb_file_per_table = 1
max_connections = 100
table_open_cache = 2000
tmp_table_size = 64M
max_heap_table_size = 64M
query_cache_type = 0
query_cache_size = 0
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2
EOF
    mkdir -p /var/log/mysql && chown mysql:mysql /var/log/mysql
    systemctl restart mariadb 2>/dev/null || true
fi

# ---------------------------------------------------------------------------
# 4. APACHE: KeepAlive + deflate + expires
# ---------------------------------------------------------------------------
if [[ "$SKIP_APACHE" != "1" ]] && command -v apache2ctl >/dev/null; then
    log "Apache: deflate, expires, KeepAlive"
    a2enmod deflate expires headers -q 2>/dev/null || true
    cat > /etc/apache2/conf-available/megahnet-perf.conf <<'EOF'
KeepAlive On
MaxKeepAliveRequests 200
KeepAliveTimeout 5

<IfModule mod_deflate.c>
  AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json application/xml application/xml+rss image/svg+xml font/ttf font/otf application/font-woff application/font-woff2
</IfModule>

<IfModule mod_expires.c>
  ExpiresActive On
  ExpiresByType text/css "access plus 7 days"
  ExpiresByType application/javascript "access plus 7 days"
  ExpiresByType image/png "access plus 30 days"
  ExpiresByType image/jpeg "access plus 30 days"
  ExpiresByType image/gif "access plus 30 days"
  ExpiresByType image/svg+xml "access plus 30 days"
  ExpiresByType image/webp "access plus 30 days"
  ExpiresByType font/woff2 "access plus 30 days"
  ExpiresByType font/woff "access plus 30 days"
</IfModule>
EOF
    a2enconf megahnet-perf -q 2>/dev/null || true
    apache2ctl configtest 2>&1 | grep -v 'Syntax OK' || true
    systemctl reload apache2 2>/dev/null || systemctl restart apache2 2>/dev/null || true
fi

# ---------------------------------------------------------------------------
# 5. SYSCTL networking + file handles
# ---------------------------------------------------------------------------
if [[ "$SKIP_SYSCTL" != "1" ]]; then
    log "sysctl: TCP backlog, tw_reuse, file-max"
    cat > /etc/sysctl.d/99-net-tuning.conf <<EOF
net.core.somaxconn=4096
net.core.netdev_max_backlog=5000
net.ipv4.tcp_max_syn_backlog=4096
net.ipv4.tcp_tw_reuse=1
net.ipv4.tcp_fin_timeout=15
net.ipv4.ip_local_port_range=10240 65535
fs.file-max=2097152
EOF
    sysctl -p /etc/sysctl.d/99-net-tuning.conf >/dev/null
fi

echo
log "=================================================================="
log "  Tuning aplicado"
log "  swap=$(swapon --show=size --noheadings 2>/dev/null | head -1 || echo none)"
log "  innodb_buffer_pool=${INNODB_BUFFER_MB}M  opcache=${OPCACHE_MEM_MB}M+JIT${OPCACHE_JIT_MB}M"
log "=================================================================="
