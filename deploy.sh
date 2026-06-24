#!/usr/bin/env bash
# =============================================================================
#  deploy.sh — Full VPS setup for Crypto Exchange Website (Laravel 11)
#  Ubuntu 22.04 / 24.04 LTS · Nginx · PHP 8.3 · MySQL 8 · Node 20 · SSL
#
#  Usage:
#    chmod +x deploy.sh
#    sudo bash deploy.sh
#
#  Edit the CONFIG section below before running.
# =============================================================================

set -euo pipefail

# ── CONFIG — edit these ───────────────────────────────────────────────────────
DOMAIN="your-domain.com"          # e.g. ex-change.com
REPO="https://github.com/jafardxb812-max/Crypto-Exchange-Website.git"
BRANCH="main"
APP_DIR="/var/www/${DOMAIN}"
DB_NAME="exchange_db"
DB_USER="exchange_user"
DB_PASS="$(openssl rand -base64 24 | tr -dc 'A-Za-z0-9' | head -c 20)"
BSCSCAN_KEY="YourApiKeyToken"     # replace with your BscScan API key
PHP_VER="8.3"
# ─────────────────────────────────────────────────────────────────────────────

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; NC='\033[0m'
log()  { echo -e "${GREEN}[✓]${NC} $1"; }
warn() { echo -e "${YELLOW}[!]${NC} $1"; }
die()  { echo -e "${RED}[✗]${NC} $1"; exit 1; }

[ "$EUID" -eq 0 ] || die "Run as root: sudo bash deploy.sh"
[ "$DOMAIN" = "your-domain.com" ] && die "Set DOMAIN in the CONFIG section first."

echo ""
echo "======================================================"
echo "  Crypto Exchange Website — VPS Setup"
echo "  Domain : $DOMAIN"
echo "  Dir    : $APP_DIR"
echo "======================================================"
echo ""

# ── 1. System update ──────────────────────────────────────────────────────────
log "Updating system packages..."
apt-get update -qq && apt-get upgrade -y -qq

# ── 2. Essential tools ────────────────────────────────────────────────────────
log "Installing essential tools..."
apt-get install -y -qq curl wget git unzip zip ufw software-properties-common \
    ca-certificates gnupg lsb-release

# ── 3. PHP 8.3 ───────────────────────────────────────────────────────────────
log "Installing PHP ${PHP_VER}..."
add-apt-repository -y ppa:ondrej/php > /dev/null 2>&1
apt-get update -qq
apt-get install -y -qq \
    php${PHP_VER} php${PHP_VER}-fpm php${PHP_VER}-cli \
    php${PHP_VER}-mysql php${PHP_VER}-pgsql php${PHP_VER}-sqlite3 \
    php${PHP_VER}-curl php${PHP_VER}-mbstring php${PHP_VER}-xml \
    php${PHP_VER}-zip php${PHP_VER}-bcmath php${PHP_VER}-gd \
    php${PHP_VER}-intl php${PHP_VER}-redis php${PHP_VER}-opcache

# ── 4. Composer ───────────────────────────────────────────────────────────────
log "Installing Composer..."
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
composer --version

# ── 5. Nginx ─────────────────────────────────────────────────────────────────
log "Installing Nginx..."
apt-get install -y -qq nginx
systemctl enable nginx

# ── 6. MySQL 8 ───────────────────────────────────────────────────────────────
log "Installing MySQL 8..."
apt-get install -y -qq mysql-server
systemctl enable mysql

log "Configuring MySQL database..."
mysql -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
mysql -e "GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"
log "Database: ${DB_NAME} / User: ${DB_USER}"

# ── 7. Node.js 20 ────────────────────────────────────────────────────────────
log "Installing Node.js 20..."
curl -fsSL https://deb.nodesource.com/setup_20.x | bash - > /dev/null 2>&1
apt-get install -y -qq nodejs
node --version && npm --version

# ── 8. Clone repository ───────────────────────────────────────────────────────
log "Cloning repository..."
if [ -d "$APP_DIR" ]; then
    warn "Directory exists — pulling latest..."
    git -C "$APP_DIR" fetch origin
    git -C "$APP_DIR" reset --hard "origin/${BRANCH}"
else
    git clone --branch "$BRANCH" "$REPO" "$APP_DIR"
fi

cd "$APP_DIR"

# ── 9. Laravel dependencies ───────────────────────────────────────────────────
log "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction -q

log "Installing Node dependencies..."
npm ci --silent

log "Building frontend assets..."
npm run build

# ── 10. Environment file ─────────────────────────────────────────────────────
log "Configuring .env..."
if [ ! -f ".env" ]; then
    cp .env.example .env
fi

APP_KEY=$(php artisan key:generate --show)

cat > .env <<EOF
APP_NAME="EX-Change"
APP_ENV=production
APP_KEY=${APP_KEY}
APP_DEBUG=false
APP_URL=https://${DOMAIN}

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=${DB_NAME}
DB_USERNAME=${DB_USER}
DB_PASSWORD=${DB_PASS}

CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

BSCSCAN_API_KEY=${BSCSCAN_KEY}
EOF

# ── 11. Artisan setup ────────────────────────────────────────────────────────
log "Running Laravel setup..."
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link

# ── 12. Permissions ──────────────────────────────────────────────────────────
log "Setting permissions..."
chown -R www-data:www-data "$APP_DIR"
chmod -R 755 "$APP_DIR"
chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

# ── 13. MCP server dependencies ──────────────────────────────────────────────
log "Installing MCP server dependencies..."
cd "${APP_DIR}/mcp-servers/binance"
npm ci --silent
cd "$APP_DIR"

# ── 14. Nginx virtual host ───────────────────────────────────────────────────
log "Configuring Nginx..."
cat > /etc/nginx/sites-available/${DOMAIN} <<NGINX
server {
    listen 80;
    server_name ${DOMAIN} www.${DOMAIN};
    root ${APP_DIR}/public;
    index index.php index.html;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php${PHP_VER}-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 120;
    }

    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    location ~ /\.(?!well-known).* { deny all; }

    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml;
    gzip_min_length 1024;

    client_max_body_size 10M;
}
NGINX

ln -sf /etc/nginx/sites-available/${DOMAIN} /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx

# ── 15. SSL (Let's Encrypt) ──────────────────────────────────────────────────
log "Installing Certbot for SSL..."
apt-get install -y -qq certbot python3-certbot-nginx

warn "Running Certbot — make sure DNS A record for ${DOMAIN} points to this server IP first."
certbot --nginx -d "${DOMAIN}" -d "www.${DOMAIN}" \
    --non-interactive --agree-tos --redirect \
    --email "admin@${DOMAIN}" || warn "SSL setup failed — run manually: certbot --nginx -d ${DOMAIN}"

# Auto-renew
systemctl enable certbot.timer

# ── 16. Supervisor (MCP server as daemon) ────────────────────────────────────
log "Installing Supervisor..."
apt-get install -y -qq supervisor

cat > /etc/supervisor/conf.d/binance-mcp.conf <<SUPERVISOR
[program:binance-mcp]
command=node ${APP_DIR}/mcp-servers/binance/index.mjs
directory=${APP_DIR}/mcp-servers/binance
autostart=true
autorestart=true
stderr_logfile=/var/log/binance-mcp.err.log
stdout_logfile=/var/log/binance-mcp.out.log
user=www-data
environment=NODE_ENV="production"
SUPERVISOR

supervisorctl reread && supervisorctl update && supervisorctl start binance-mcp

# ── 17. Firewall ─────────────────────────────────────────────────────────────
log "Configuring UFW firewall..."
ufw --force enable
ufw allow OpenSSH
ufw allow 'Nginx Full'

# ── 18. Save credentials ─────────────────────────────────────────────────────
CRED_FILE="/root/exchange-credentials.txt"
cat > "$CRED_FILE" <<CREDS
=== Crypto Exchange — Server Credentials ===
Date      : $(date)
Domain    : https://${DOMAIN}
App Dir   : ${APP_DIR}

MySQL:
  Database : ${DB_NAME}
  Username : ${DB_USER}
  Password : ${DB_PASS}

BscScan API Key: ${BSCSCAN_KEY}
===========================================
CREDS
chmod 600 "$CRED_FILE"

# ── Done ─────────────────────────────────────────────────────────────────────
echo ""
echo "======================================================"
echo -e "${GREEN}  SETUP COMPLETE${NC}"
echo "======================================================"
echo "  Site      : https://${DOMAIN}"
echo "  App dir   : ${APP_DIR}"
echo "  Creds     : ${CRED_FILE}"
echo "  MCP server: supervisorctl status binance-mcp"
echo "  Nginx log : tail -f /var/log/nginx/error.log"
echo "  Laravel   : tail -f ${APP_DIR}/storage/logs/laravel.log"
echo "======================================================"
