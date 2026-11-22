#!/usr/bin/env bash
# Deploy Party Sites CMS to a shared hosting server via SSH
# Usage: ./scripts/deploy.sh {zip_path} {remote_user@host} {remote_path}
# Requirements on remote: PHP 8.2+, Composer, MySQL, zip/unzip
set -euo pipefail

if [ "$#" -lt 3 ]; then
    echo "Usage: $0 <zip_path> <user@host> <remote_path>"
    echo "  Example: $0 storage/releases/cms-0.1.0-20261121.zip deploy@cms.example.org /home/deploy/public_html"
    exit 1
fi

ZIP="$1"
REMOTE="$2"
REMOTE_PATH="$3"

if [ ! -f "$ZIP" ]; then
    echo "ERROR: ZIP file not found: $ZIP"
    exit 1
fi

echo "==> Uploading release ZIP..."
scp "$ZIP" "$REMOTE:$REMOTE_PATH/cms-release.zip"

echo "==> Extracting on remote..."
ssh "$REMOTE" "cd $REMOTE_PATH && unzip -o cms-release.zip -d cms && rm cms-release.zip"

echo "==> Installing Composer dependencies..."
ssh "$REMOTE" "cd $REMOTE_PATH/cms && composer install --no-dev --optimize-autoloader"

echo "==> Setting permissions..."
ssh "$REMOTE" "cd $REMOTE_PATH/cms && chmod -R 755 storage bootstrap/cache"

echo ""
echo "==> Upload complete. Run these commands on the server:"
echo "    cd $REMOTE_PATH/cms"
echo "    cp .env.example .env"
echo "    # Edit .env: set APP_URL, DB_DATABASE, DB_USERNAME, DB_PASSWORD"
echo "    php artisan key:generate"
echo "    php artisan migrate --force"
echo "    php artisan storage:link"
echo "    # Point your web root to: $REMOTE_PATH/cms/public"
echo "    # Then visit /install in your browser"
