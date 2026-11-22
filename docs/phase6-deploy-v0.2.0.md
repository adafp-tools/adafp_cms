# Phase 6 — Deploy v0.2.0 to cms.adafp.org

**Date prepared:** 22 November 2026  
**Release ZIP:** `cms-0.2.0-20261122.zip` (self-contained: includes `vendor/` and `public/build/`)  
**Database:** `adafp_cms`  
**SSH alias:** `ssh nadus` (configure in `~/.ssh/config` if not already)

---

## Before you start

You need on your Windows machine:
- The ZIP downloaded from the GitHub release (or via `scp` from the build server)
- `ssh nadus` working (opens a shell on the server)
- `scp` available (comes with OpenSSH, installed by default on Windows 10/11)

If the ZIP is still on the build server, download it first:

```powershell
scp nadus:/home/user/adafp_tools_cms/storage/releases/cms-0.2.0-20261122.zip $env:USERPROFILE\Downloads\
```

---

## Decide: fresh install or update?

- **Fresh install** (v0.1.0 was never deployed, or you want a clean slate): follow Steps 1–9.
- **Update from v0.1.0**: skip Steps 1–2, go straight to Step 3.

---

## Step 1 — Create the database (fresh install only)

```powershell
ssh nadus "mysql -u root -p -e `"
CREATE DATABASE IF NOT EXISTS \`adafp_cms\`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'cms_user'@'localhost'
  IDENTIFIED BY 'CHANGE_ME_DB_PASSWORD';
GRANT ALL PRIVILEGES ON \`adafp_cms\`.* TO 'cms_user'@'localhost';
FLUSH PRIVILEGES;
`""
```

Replace `CHANGE_ME_DB_PASSWORD` with a strong password and note it down — you'll need it in Step 4.

---

## Step 2 — Back up existing site (update only — skip on fresh install)

```powershell
ssh nadus "mysqldump -u cms_user -p adafp_cms > /tmp/cms-backup-before-v0.2.0.sql && echo 'DB backup done'"
ssh nadus "cp -r /var/www/cms.adafp.org /tmp/cms-backup-files-before-v0.2.0 && echo 'Files backup done'"
```

Adjust `/var/www/cms.adafp.org` to your actual site directory.

---

## Step 3 — Upload and extract the ZIP

```powershell
# Upload
scp $env:USERPROFILE\Downloads\cms-0.2.0-20261122.zip nadus:/tmp/cms-release.zip

# Extract (replace /var/www/cms.adafp.org with your actual path)
ssh nadus "unzip -o /tmp/cms-release.zip -d /var/www/cms.adafp.org && rm /tmp/cms-release.zip && echo 'Extracted.'"
```

> The ZIP includes `vendor/` and `public/build/` — no `composer install` or `npm run build` needed on the server.

---

## Step 4 — Write / update the .env

### 4a — Fresh install

Generate an APP_KEY on your local machine or the build server:

```powershell
ssh nadus "php /var/www/cms.adafp.org/artisan key:generate --show"
```

Copy the `base64:...` value. Then write the full `.env`:

```powershell
ssh nadus @"
cat > /var/www/cms.adafp.org/.env <<'ENV'
APP_NAME="Party Sites CMS"
APP_ENV=production
APP_KEY=PASTE_KEY_HERE
APP_DEBUG=false
APP_TIMEZONE=Africa/Khartoum
APP_URL=https://cms.adafp.org

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=adafp_cms
DB_USERNAME=cms_user
DB_PASSWORD=CHANGE_ME_DB_PASSWORD

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=true

FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
CACHE_STORE=file

MAIL_MAILER=smtp
MAIL_HOST=your.smtp.host
MAIL_PORT=587
MAIL_USERNAME=your@email.com
MAIL_PASSWORD=your_mail_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=demo@cms.adafp.org
MAIL_FROM_NAME="Party Sites CMS"

CMS_INSTANCE=demo
CMS_PRODUCT_NAME="Party Sites CMS"
CMS_PANEL_PATH=panel
CMS_DEMO_ADMIN_EMAIL=demo@cms.adafp.org
CMS_DEMO_ADMIN_PASSWORD=CHOOSE_A_DEMO_PASSWORD
CMS_DEMO_MAIL_ALLOWLIST=abdelaati@gmail.com
ENV
echo '.env written.'
"@
```

Replace the values marked in CAPITALS. `CMS_DEMO_ADMIN_PASSWORD` is the password
shown on the public `/demo` page so visitors can log in — choose something
memorable (e.g. `Demo2026!`) but not a password you use elsewhere.

### 4b — Update from v0.1.0 (add new keys to existing .env)

```powershell
ssh nadus @"
cd /var/www/cms.adafp.org
echo '' >> .env
echo 'CMS_INSTANCE=demo' >> .env
echo 'CMS_DEMO_ADMIN_EMAIL=demo@cms.adafp.org' >> .env
echo 'CMS_DEMO_ADMIN_PASSWORD=CHOOSE_A_DEMO_PASSWORD' >> .env
echo 'CMS_DEMO_MAIL_ALLOWLIST=abdelaati@gmail.com' >> .env
echo 'New vars appended.'
"@
```

---

## Step 5 — Run migrations and clear caches

```powershell
ssh nadus "cd /var/www/cms.adafp.org && php artisan migrate --force && php artisan optimize:clear && echo 'Migrations done.'"
```

---

## Step 6 — Storage symlink (fresh install only)

```powershell
ssh nadus "cd /var/www/cms.adafp.org && php artisan storage:link && echo 'Symlink OK.'"
```

If this fails (some hosts restrict symlinks), that is fine — the CMS has a built-in fallback.

---

## Step 7 — Set permissions

```powershell
ssh nadus "chmod -R 755 /var/www/cms.adafp.org/storage /var/www/cms.adafp.org/bootstrap/cache && echo 'Permissions set.'"
```

---

## Step 8 — Create Owner account and mark site installed (fresh install only)

Generate a strong Owner password first:

```powershell
ssh nadus "openssl rand -base64 20 | tr -d '=+/' | cut -c1-20"
```

Save that password securely. Then create the account:

```powershell
ssh nadus @"
cd /var/www/cms.adafp.org
php artisan tinker --no-interaction <<'PHP'
use App\Actions\Fortify\CreateNewUser;
use App\Models\Setting;

\$pw = 'PASTE_OWNER_PASSWORD_HERE';

(new CreateNewUser)->create([
    'name'                  => 'Adil Abdelaati',
    'email'                 => 'abdelaati@gmail.com',
    'password'              => \$pw,
    'password_confirmation' => \$pw,
    'role'                  => 'owner',
]);

Setting::set('site_name',            'Party Sites CMS Demo');
Setting::set('default_locale',       'en');
Setting::set('active_locales',       'en,fr,ar');
Setting::set('theme',                'classic');
Setting::set('primary_color',        '#1e3a5f');
Setting::set('secondary_color',      '#c0392b');
Setting::set('text_on_primary',      '#ffffff');
Setting::set('maintenance_mode',     '0');
Setting::set('noindex',              '0');
Setting::set('page_counter_enabled', '0');
Setting::set('join_form_enabled',    '1');
Setting::set('contact_form_enabled', '1');

echo 'Owner created and settings applied.' . PHP_EOL;
PHP

touch storage/installed
echo 'Site marked as installed.'
"@
```

---

## Step 9 — Seed demo content and run first reset

```powershell
ssh nadus "cd /var/www/cms.adafp.org && php artisan cms:demo-reset && echo 'Demo content seeded.'"
```

This seeds the fictional "National Progress Party" in four languages and clears all caches.

---

## Step 10 — Configure the cron scheduler

The nightly `cms:demo-reset` runs via Laravel's scheduler. Add a cron entry:

```powershell
ssh nadus "(crontab -l 2>/dev/null; echo '* * * * * php /var/www/cms.adafp.org/artisan schedule:run >> /dev/null 2>&1') | crontab -"
```

Verify it was added:

```powershell
ssh nadus "crontab -l"
```

---

## Step 11 — Point the web root (if not already done)

The document root must point to `public/`:

**Apache / cPanel:** set DocumentRoot to `/var/www/cms.adafp.org/public`  
**Nginx:** `root /var/www/cms.adafp.org/public;`

An `.htaccess` file is already in `public/` for Apache.

---

## Verification

```powershell
# 1. Home page responds
ssh nadus "curl -sI https://cms.adafp.org/en/ | head -2"

# 2. noindex header is set (demo mode)
ssh nadus "curl -sI https://cms.adafp.org/en/ | grep -i robots"

# 3. /demo page returns 200
ssh nadus "curl -sI https://cms.adafp.org/en/demo | head -2"

# 4. Panel login page returns 200
ssh nadus "curl -sI https://cms.adafp.org/panel | head -2"
```

Expected results:
- All return `HTTP/2 200` (or HTTP/1.1 200)
- The robots check returns `x-robots-tag: noindex, nofollow`

---

## Troubleshooting

| Symptom | Fix |
|---|---|
| 500 on first visit | `ssh nadus "tail -20 /var/www/cms.adafp.org/storage/logs/laravel.log"` |
| CSS/JS 404 | Web root is not pointing to `public/` |
| Images not displaying | Storage symlink failed — CMS fallback activates automatically |
| `cms:demo-reset` returns "not demo instance" | `CMS_INSTANCE=demo` missing or misspelled in `.env` |
| Panel login shows no demo credentials box | Same — confirm `CMS_INSTANCE=demo` |
| Blank page, no error | `ssh nadus "cd /var/www/cms.adafp.org && php artisan config:clear"` |

---

## Credentials block (fill in after Step 8)

```
┌─────────────────────────────────────────────────────┐
│  cms.adafp.org — Owner (admin) credentials          │
│                                                     │
│  URL:       https://cms.adafp.org/panel             │
│  Email:     abdelaati@gmail.com                     │
│  Password:  [OWNER_PASSWORD from Step 8]            │
│                                                     │
│  Demo login (shown on /en/demo):                    │
│  Email:     demo@cms.adafp.org                      │
│  Password:  [CMS_DEMO_ADMIN_PASSWORD from Step 4]   │
│                                                     │
│  ⚠  Enable 2FA on first Owner login.                │
└─────────────────────────────────────────────────────┘
```
