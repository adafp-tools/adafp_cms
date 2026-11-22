# Phase 8 — Deploy demo to cms.adafp.org

**Date prepared:** 22 November 2026  
**Release ZIP:** `cms-0.1.0-20261122.zip`  
**Database:** `adafp_cms` (as per Adil's instruction)

---

## Prerequisites on the server

| Item | Minimum |
|---|---|
| PHP | 8.2+ with BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, PDO_MySQL, Tokenizer, XML, GD, Zip |
| MySQL / MariaDB | 8.0+ / 10.6+ |
| Composer | 2.x |
| SSH access | Required for steps 1–7 |
| Web root | Must be configurable to point to `public/` |

---

## Step 1 — Build the release ZIP (on your local machine)

Clone the repository if you have not already, then run:

```bash
git clone https://github.com/adafp-tools/adafp_tools_cms.git
cd adafp_tools_cms
composer install
npm ci && npm run build
php artisan cms:release
```

The ZIP lands in `storage/releases/cms-0.1.0-YYYYMMDD.zip`.

---

## Step 2 — Create the database on the server

Log in to the server via SSH and run MySQL as root.
**Replace `CHANGE_ME_PASSWORD` with a strong password before running.**

```bash
mysql -u root -p
```

Then paste:

```sql
CREATE DATABASE IF NOT EXISTS `adafp_cms`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'cms_user'@'localhost'
    IDENTIFIED BY 'CHANGE_ME_PASSWORD';

GRANT ALL PRIVILEGES ON `adafp_cms`.* TO 'cms_user'@'localhost';

FLUSH PRIVILEGES;
```

---

## Step 3 — Upload and extract the ZIP

Replace `deploy@cms.adafp.org` and `/home/deploy` with your actual SSH user and path.

```bash
# From your local machine:
scp storage/releases/cms-0.1.0-*.zip deploy@cms.adafp.org:/home/deploy/cms-release.zip

# On the server:
ssh deploy@cms.adafp.org
cd /home/deploy
unzip -o cms-release.zip -d cms
rm cms-release.zip
cd cms
```

---

## Step 4 — Install Composer dependencies

```bash
composer install --no-dev --optimize-autoloader
```

---

## Step 5 — Configure the environment

```bash
cp .env.example .env
nano .env   # or vi .env
```

Set these values (minimum required):

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://cms.adafp.org

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=adafp_cms
DB_USERNAME=cms_user
DB_PASSWORD=CHANGE_ME_PASSWORD

MAIL_MAILER=smtp
MAIL_HOST=your.mail.host
MAIL_PORT=587
MAIL_USERNAME=your@email.com
MAIL_PASSWORD=your_mail_password
MAIL_FROM_ADDRESS=demo@cms.adafp.org

CMS_INSTANCE=demo
CMS_DEMO_MAIL_ALLOWLIST=your@email.com
```

---

## Step 6 — Generate application key and run migrations

```bash
php artisan key:generate
php artisan migrate --force
```

---

## Step 7 — Storage symlink

```bash
php artisan storage:link
```

If this returns an error (some Apache shared hosts restrict symlinks), it is not a problem — the web installer detects the failure and sets up a PHP file-serving fallback automatically.

---

## Step 8 — Point the web root to `public/`

**cPanel:** Set the document root (or subdomain root) to `/home/deploy/cms/public`  
**Nginx:**
```nginx
root /home/deploy/cms/public;
index index.php;
```
**Apache:** An `.htaccess` is already included in `public/`.

---

## Step 9 — Run the web installer

Open a browser and visit:

```
https://cms.adafp.org/install
```

The installer will:
1. Verify PHP version and extensions
2. Test the database connection
3. Run any remaining migrations
4. Create the first **Owner** account (you — Adil)
5. Set the site name and default language

After completing the installer, the site is live at `https://cms.adafp.org`.

---

## Step 10 — Post-install checklist

- [ ] Log in at `https://cms.adafp.org/panel`
- [ ] Go to **Settings → General** and set the site name, default language (e.g. English), active languages
- [ ] Go to **Settings → Theme** — select **Classic** for the demo
- [ ] Go to **Settings → Notifications** — enter the demo notification email
- [ ] Enable 2FA on your Owner account (**Settings → Security**)
- [ ] Create a test page and publish it — verify it appears on the public site
- [ ] Submit the contact form and verify the notification email arrives
- [ ] Verify the site is indexed (`/sitemap.xml` and `/robots.txt` load)
- [ ] Check the Arabic locale: visit `https://cms.adafp.org/ar/` — confirm RTL layout

---

## Troubleshooting

| Symptom | Fix |
|---|---|
| 500 on first visit | Check `storage/logs/laravel.log`; most likely `APP_KEY` empty or DB credentials wrong |
| CSS/JS 404 | Web root is not pointing to `public/` |
| Images not displaying | Storage symlink failed — installer fallback activates automatically |
| `/install` redirects to `/panel/login` | Site already installed; delete `storage/installed` to re-run |
| Blank page, no error | Set `APP_DEBUG=true` in `.env` temporarily, then set it back |
