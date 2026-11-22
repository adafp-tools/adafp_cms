# Deployment Guide — Party Sites CMS

## Shared hosting (cPanel / DirectAdmin / Hostinger)

### Server prerequisites

| Requirement | Minimum |
|---|---|
| PHP | 8.2 or higher |
| PHP extensions | BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, PDO_MySQL, Tokenizer, XML, GD, Zip |
| MySQL / MariaDB | 8.0+ / 10.6+ |
| Composer | 2.x (via SSH, or upload the PHAR) |
| Node.js / npm | **Not required** on the server — assets are pre-compiled in the release ZIP |

### Step-by-step installation

#### 1. Build the release ZIP (on your local machine)

```bash
php artisan cms:release
# Output: storage/releases/cms-{version}-{date}.zip
```

#### 2. Create the database

Via phpMyAdmin or MySQL CLI, run `scripts/mysql-setup.sql` (edit the password first):

```bash
mysql -u root -p < scripts/mysql-setup.sql
```

#### 3. Upload and extract

Upload the ZIP to the server and extract it:

```bash
unzip cms-{version}-{date}.zip -d /home/user/cms
```

Or use `scripts/deploy.sh` if you have SSH access:

```bash
./scripts/deploy.sh storage/releases/cms-*.zip user@yourserver.com /home/user/public_html
```

#### 4. Configure environment

```bash
cp .env.example .env
# Edit .env — set at minimum:
#   APP_URL=https://your-domain.com
#   APP_KEY=   (leave blank — generated in next step)
#   DB_DATABASE=adafp_cms
#   DB_USERNAME=cms_user
#   DB_PASSWORD=your_password
```

#### 5. Install dependencies and generate key

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
```

#### 6. Run migrations

```bash
php artisan migrate --force
```

#### 7. Create storage symlink

```bash
php artisan storage:link
```

If this fails (some Apache shared hosts restrict symlinks), the web installer detects this and falls back to a PHP file-serving route automatically.

#### 8. Point web root to `public/`

In cPanel: set the document root to `{install_dir}/public`.
In Nginx: `root /path/to/cms/public;`
In Apache `.htaccess`: already included in `public/.htaccess`.

#### 9. Run the web installer

Visit `https://your-domain/install` in a browser. The installer will:
1. Verify PHP version and required extensions
2. Test the database connection
3. Run any remaining migrations
4. Create the first Owner account
5. Set the site name and default language

---

## Demo site (cms.adafp.org) — Phase 8

- **Domain**: `cms.adafp.org`
- **Database**: `adafp_cms` (created via `scripts/mysql-setup.sql`)
- **Theme**: Classic (recommended for demo)
- After deployment, visit `https://cms.adafp.org/install`

---

## VPS / Dedicated server (server.nadus.org) — Phase 9

For the hosted multi-tenant service, use `scripts/provision-tenant.sh`:

```bash
# On the server, as root:
./scripts/provision-tenant.sh sudan-liberal cms.sudanliberal.org 'Str0ngPassw0rd!'
```

Each script call provisions one party tenant with its own database, Nginx vhost, and SSL certificate.

---

## Troubleshooting

| Symptom | Likely cause | Fix |
|---|---|---|
| 500 error on first visit | Missing `APP_KEY` or wrong DB credentials | Check `storage/logs/laravel.log` |
| Assets not loading (404 on CSS/JS) | Web root not pointing to `public/` | Verify document root |
| Images not displaying | Storage symlink failed | Run `php artisan storage:link` again; installer fallback kicks in automatically |
| `/install` redirects to `/panel/login` | Site already installed (storage/installed exists) | Delete `storage/installed` to re-run installer |
| Blank page, no error | PHP display_errors off | Add `APP_DEBUG=true` to `.env` temporarily |
