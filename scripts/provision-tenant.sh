#!/usr/bin/env bash
# Provision a new party CMS tenant on server.nadus.org
# Usage: ./scripts/provision-tenant.sh {party_slug} {domain} {db_password}
# Requirements: Nginx, PHP-FPM 8.3+, MySQL 8+, Composer 2, certbot
# Run as root (or sudo) on the hosting server.
set -euo pipefail

if [ "$#" -lt 3 ]; then
    echo "Usage: $0 <party-slug> <domain> <db-password>"
    echo "  Example: $0 sudan-liberal cms.sudanliberal.org 'Str0ngPassw0rd!'"
    exit 1
fi

SLUG="$1"
DOMAIN="$2"
DB_PASS="$3"

BASE_DIR="/var/www/cms-tenants/$SLUG"
DB_NAME="cms_${SLUG//-/_}"
DB_USER="cms_${SLUG//-/_}"
CMS_BASE="/opt/cms-base"   # Extracted release ZIP lives here

if [ ! -d "$CMS_BASE" ]; then
    echo "ERROR: CMS base not found at $CMS_BASE"
    echo "  Extract the release ZIP there first:"
    echo "    mkdir -p $CMS_BASE && unzip cms-*.zip -d $CMS_BASE"
    exit 1
fi

echo "==> Creating tenant directory at $BASE_DIR ..."
mkdir -p "$BASE_DIR"
cp -r "$CMS_BASE/." "$BASE_DIR/"

echo "==> Creating database $DB_NAME ..."
mysql -u root -e "
  CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
  GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'localhost';
  FLUSH PRIVILEGES;
"

echo "==> Writing .env ..."
cp "$BASE_DIR/.env.example" "$BASE_DIR/.env"
sed -i "s|APP_URL=.*|APP_URL=https://$DOMAIN|" "$BASE_DIR/.env"
sed -i "s|DB_CONNECTION=.*|DB_CONNECTION=mysql|" "$BASE_DIR/.env"
sed -i "s|DB_DATABASE=.*|DB_DATABASE=$DB_NAME|" "$BASE_DIR/.env"
sed -i "s|DB_USERNAME=.*|DB_USERNAME=$DB_USER|" "$BASE_DIR/.env"
sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=$DB_PASS|" "$BASE_DIR/.env"

echo "==> Installing Composer dependencies ..."
cd "$BASE_DIR" && composer install --no-dev --optimize-autoloader -q

echo "==> Generating app key ..."
php artisan key:generate --force

echo "==> Running migrations ..."
php artisan migrate --force

echo "==> Creating storage symlink ..."
php artisan storage:link || true

echo "==> Setting permissions ..."
chown -R www-data:www-data "$BASE_DIR"
chmod -R 755 "$BASE_DIR/storage" "$BASE_DIR/bootstrap/cache"

echo "==> Writing Nginx vhost ..."
cat > "/etc/nginx/sites-available/cms-$SLUG.conf" <<NGINX
server {
    listen 80;
    server_name $DOMAIN;
    root $BASE_DIR/public;
    index index.php;
    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* { deny all; }
    location ~* \.(sql|env|log|sh)\$ { deny all; }

    access_log /var/log/nginx/cms-$SLUG-access.log;
    error_log  /var/log/nginx/cms-$SLUG-error.log;
}
NGINX

ln -sf "/etc/nginx/sites-available/cms-$SLUG.conf" "/etc/nginx/sites-enabled/"
nginx -t && systemctl reload nginx

echo "==> Requesting SSL certificate (Let's Encrypt) ..."
certbot --nginx -d "$DOMAIN" --non-interactive --agree-tos -m admin@nadus.org || \
    echo "WARNING: certbot failed — run manually: certbot --nginx -d $DOMAIN"

echo ""
echo "==> Tenant provisioned successfully."
echo "    Visit: https://$DOMAIN/install  to complete setup"
echo "    Slug: $SLUG | DB: $DB_NAME | Dir: $BASE_DIR"
