# Claude SSH deployment prompt — cms.adafp.org

> **Updated for v0.2.0** — ZIP now includes `vendor/` and `public/build/`;
> `composer install` and `npm run build` on the server are no longer needed.
> New `.env` keys: `CMS_DEMO_ADMIN_EMAIL`, `CMS_DEMO_ADMIN_PASSWORD`.
> After deploy, run `cms:demo-reset` to seed demo content.

Paste this prompt into a Claude Code session that has SSH access to `cms.adafp.org`.
Fill in the four values marked **[YOU FILL IN]** before pasting.

---

```
Deploy the Party Sites CMS to cms.adafp.org via SSH. Work through every
step below in order. Report each step as you complete it. At the end,
print the admin login URL and credentials in a clearly labelled block.

--- CONFIGURATION ---
SSH_USER=[YOU FILL IN]            # e.g. deploy  or  root  or  adil
REMOTE_DIR=[YOU FILL IN]          # e.g. /home/deploy/cms  or  /var/www/cms
DB_PASSWORD=[YOU FILL IN]         # strong MySQL password for cms_user
DEMO_PASSWORD=[YOU FILL IN]       # password shown on /demo page (e.g. Demo2026!)

Fixed values (do not change):
  HOST          = cms.adafp.org
  DB_NAME       = adafp_cms
  DB_USER       = cms_user
  ADMIN_EMAIL   = abdelaati@gmail.com
  ADMIN_NAME    = Adil Abdelaati
  SITE_NAME     = Party Sites CMS Demo
  DEFAULT_LOCALE= en
  ACTIVE_LOCALES= en,ar
  TIMEZONE      = Africa/Khartoum
  APP_URL       = https://cms.adafp.org

--- STEPS ---

1. MYSQL SETUP
   Run as MySQL root on the server. Use the DB_PASSWORD above.

   ssh ${SSH_USER}@cms.adafp.org "mysql -u root -p <<'SQL'
   CREATE DATABASE IF NOT EXISTS \`adafp_cms\`
       CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER IF NOT EXISTS 'cms_user'@'localhost'
       IDENTIFIED BY '${DB_PASSWORD}';
   GRANT ALL PRIVILEGES ON \`adafp_cms\`.* TO 'cms_user'@'localhost';
   FLUSH PRIVILEGES;
   SQL"

   If MySQL root requires a password prompt, run the SQL file interactively:
     scp /path/to/setup.sql ${SSH_USER}@cms.adafp.org:/tmp/setup.sql
     ssh ${SSH_USER}@cms.adafp.org "mysql -u root -p < /tmp/setup.sql && rm /tmp/setup.sql"

2. UPLOAD AND EXTRACT
   The release ZIP is cms-0.2.0-20261122.zip (download from the GitHub
   release at github.com/adafp-tools/adafp_tools_cms/releases/tag/v0.2.0,
   or copy from storage/releases/ on the build server).

   The ZIP includes vendor/ and public/build/ — no composer install or
   npm run build is needed on the server.

   Upload:
     scp cms-0.2.0-20261122.zip ${SSH_USER}@cms.adafp.org:/tmp/cms-release.zip

   Extract on server:
     ssh ${SSH_USER}@cms.adafp.org "
       mkdir -p ${REMOTE_DIR}
       unzip -o /tmp/cms-release.zip -d ${REMOTE_DIR}
       rm /tmp/cms-release.zip
     "

4. WRITE .env
   Generate a secure APP_KEY (run locally):
     php artisan key:generate --show   # copy the base64:... value

   Then write the .env on the server. Replace APP_KEY_VALUE with what
   the command above produced:

   ssh ${SSH_USER}@cms.adafp.org "cat > ${REMOTE_DIR}/.env <<'ENV'
APP_NAME=\"Party Sites CMS\"
APP_ENV=production
APP_KEY=APP_KEY_VALUE
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
DB_PASSWORD=${DB_PASSWORD}

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=true

FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
CACHE_STORE=file

MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=demo@cms.adafp.org
MAIL_FROM_NAME=\"Party Sites CMS\"

CMS_INSTANCE=demo
CMS_PRODUCT_NAME=\"Party Sites CMS\"
CMS_PANEL_PATH=panel
CMS_DEMO_ADMIN_EMAIL=demo@cms.adafp.org
CMS_DEMO_ADMIN_PASSWORD=${DEMO_PASSWORD}
CMS_DEMO_MAIL_ALLOWLIST=abdelaati@gmail.com
ENV"

5. GENERATE KEY AND MIGRATE
   ssh ${SSH_USER}@cms.adafp.org "
     cd ${REMOTE_DIR}
     php artisan key:generate --force
     php artisan migrate --force
   "

6. STORAGE SYMLINK
   ssh ${SSH_USER}@cms.adafp.org "
     cd ${REMOTE_DIR} && php artisan storage:link
   "
   If this fails (some Apache hosts restrict symlinks), that is fine —
   the CMS has a built-in fallback. Continue to the next step.

7. SET PERMISSIONS
   ssh ${SSH_USER}@cms.adafp.org "
     cd ${REMOTE_DIR}
     chmod -R 755 storage bootstrap/cache
   "

8. CREATE ADMIN ACCOUNT AND MARK INSTALLED
   Generate a strong admin password (20 chars, mixed):
     openssl rand -base64 20 | tr -d '=+/' | cut -c1-20
   Save that password — you will need it in the credentials block at the end.

   Then run this on the server (replace GENERATED_PASSWORD with the password
   you just generated):

   ssh ${SSH_USER}@cms.adafp.org "
     cd ${REMOTE_DIR}

     php artisan tinker --no-interaction <<'PHP'
       use App\Actions\Fortify\CreateNewUser;
       use App\Models\Setting;

       \$pw = 'GENERATED_PASSWORD';

       (new CreateNewUser)->create([
           'name'                  => 'Adil Abdelaati',
           'email'                 => 'abdelaati@gmail.com',
           'password'              => \$pw,
           'password_confirmation' => \$pw,
           'role'                  => 'owner',
       ]);

       Setting::set('site_name',     'Party Sites CMS Demo');
       Setting::set('default_locale','en');
       Setting::set('active_locales','en,ar');
       Setting::set('theme',         'classic');
       Setting::set('primary_color', '#1e3a5f');
       Setting::set('secondary_color','#c0392b');
       Setting::set('text_on_primary','#ffffff');
       Setting::set('maintenance_mode','0');
       Setting::set('noindex',        '0');
       Setting::set('page_counter_enabled','0');
       Setting::set('join_form_enabled','1');
       Setting::set('contact_form_enabled','1');
       echo 'Owner created and settings applied.' . PHP_EOL;
PHP

     touch storage/installed
     echo 'Site marked as installed.'
   "

9. SEED DEMO CONTENT
   ssh ${SSH_USER}@cms.adafp.org "
     cd ${REMOTE_DIR} && php artisan cms:demo-reset && echo 'Demo content seeded.'
   "
   This seeds the fictional party in four languages and creates the demo admin
   account using CMS_DEMO_ADMIN_EMAIL / CMS_DEMO_ADMIN_PASSWORD from .env.

10. CONFIGURE CRON (add to the server's crontab)
    (crontab -l 2>/dev/null | grep -v 'artisan schedule:run'; echo "* * * * * php ${REMOTE_DIR}/artisan schedule:run >> /dev/null 2>&1") | ssh ${SSH_USER}@cms.adafp.org crontab -

11. POINT WEB ROOT
    Set your web server's document root to: ${REMOTE_DIR}/public
    For Nginx:  root ${REMOTE_DIR}/public;
    For Apache: set DocumentRoot / cPanel document root to that path.

--- VERIFY ---

Check the site and demo mode respond correctly:
  ssh ${SSH_USER}@cms.adafp.org "curl -sI https://cms.adafp.org/en/ | head -2"
  ssh ${SSH_USER}@cms.adafp.org "curl -sI https://cms.adafp.org/en/ | grep -i robots"
  ssh ${SSH_USER}@cms.adafp.org "curl -sI https://cms.adafp.org/en/demo | head -2"
  ssh ${SSH_USER}@cms.adafp.org "curl -sI https://cms.adafp.org/panel | head -2"

Expected:
  - All return HTTP 200
  - robots header returns: x-robots-tag: noindex, nofollow

--- CREDENTIALS TO REPORT ---

At the end, print exactly this block with the real values filled in:

  ┌─────────────────────────────────────────────────────┐
  │  cms.adafp.org — Admin credentials                  │
  │                                                     │
  │  URL:       https://cms.adafp.org/panel             │
  │  Email:     abdelaati@gmail.com                     │
  │  Password:  [GENERATED_PASSWORD from Step 8]        │
  │                                                     │
  │  Demo login (shown at /en/demo):                    │
  │  Email:     demo@cms.adafp.org                      │
  │  Password:  [DEMO_PASSWORD you set]                 │
  │                                                     │
  │  ⚠  Enable 2FA on first Owner login.                │
  └─────────────────────────────────────────────────────┘

Do NOT output the Owner password anywhere else in the conversation.
```
