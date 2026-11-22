# Pilot Handover Kit — Party Sites CMS

This guide takes the pilot party's technical contact (and the ADAFP technical liaison) through installing, configuring, and launching the party website. Estimated time: 90 minutes.

---

## Pre-flight checklist

Collect all of the following before the installation session:

- [ ] Domain name (or subdomain) with A record pointed at the server (TTL ≤ 300 for quick propagation)
- [ ] Hosting server with PHP 8.2+, MySQL 8+, Composer 2 (or access to a shared hosting panel)
- [ ] Admin email address for notifications (e.g. `webmaster@partyname.org`)
- [ ] Party logo file — PNG or SVG, minimum 400×100 px, transparent background preferred
- [ ] Site name in all required languages (at minimum English and Arabic)
- [ ] List of initial panel users: name, email, role for each
- [ ] Theme choice: **Classic** (traditional layout) or **Campaign** (full-width hero "join us" layout)
- [ ] Active language(s): e.g. `en` only, or `en,ar`, or `en,fr,ar`

---

## Step 1 — Server setup (15 min)

Follow the full **[Deployment Guide](deployment.md)**.

Short summary:
1. Create database: run `scripts/mysql-setup.sql` (edit password first)
2. Upload release ZIP, extract, run `composer install`
3. Copy `.env.example` → `.env`, set `APP_URL`, `DB_*`, run `php artisan key:generate`
4. `php artisan migrate --force` then `php artisan storage:link`
5. Point web root to `public/`

---

## Step 2 — Web installer (10 min)

Visit `https://your-domain/install` in a browser. The installer:
1. Checks server requirements (PHP version, extensions)
2. Tests the database connection
3. Runs outstanding migrations
4. Creates the first **Owner** account (use a strong password + 2FA)
5. Sets the site name and default language

> **Important**: the Owner account is the most privileged. Enable 2FA immediately after first login (Settings → Your account → Two-factor authentication).

---

## Step 3 — Initial content setup (30 min)

Log in to the panel at `https://your-domain/panel`.

### 3a — Settings
1. Upload the party logo
2. Choose the theme (Classic or Campaign)
3. Set active languages (e.g. `en,ar`)
4. Set the notification email (where contact and join form submissions are sent)
5. Set the privacy policy URL (if the party has one)

### 3b — Pages
Create at minimum:
- An **About** page (template: About)
- A **Contact** page (template: Contact) — intro text above the contact form
- A **Join** page (template: Join) — intro text above the membership form

For each page: add an English translation (title + body), then Arabic translation.

### 3c — Menus
1. Go to Menus → Header
2. Add links: Home, About, News, Events, Leadership, Contact, Join
3. Go to Menus → Footer
4. Add: Contact, Privacy Policy (if applicable)

### 3d — People (Leadership)
Add the party's leadership team:
- Photo, name, position, group (Leadership / Executive / Parliament / Other)
- Add an Arabic translation for name and position

### 3e — First post
Create a welcome news article or statement:
- Title, body, publish date
- Status: Published
- Add Arabic translation

---

## Step 4 — Go live (5 min)

1. Settings → **Maintenance Mode: Off**
2. Visit the public site in English: `https://your-domain/en`
3. Visit the Arabic version: `https://your-domain/ar`
4. Verify: RTL layout, correct fonts, logo, navigation, contact form

---

## User roles reference

| Role | Permissions |
|---|---|
| **Owner** | Everything — settings, users, all content, backups, CRM config |
| **Admin** | All content + form submissions; cannot change settings or manage users |
| **Editor** | Create and edit their own content (submits for review; cannot publish directly) |
| **Translator** | Translate existing content; cannot create new items |

---

## Handover sign-off checklist

Before closing the installation session, verify each item:

- [ ] At least one **Owner** account created with **2FA enabled**
- [ ] At least one **Admin** account for day-to-day management
- [ ] Site name and logo set correctly in both English and Arabic
- [ ] At least one page and one post published and visible on the public site
- [ ] **Contact form tested** — submission received at the configured notification email
- [ ] **Join form tested** — submission visible in panel under Submissions
- [ ] **Arabic version verified** — RTL layout, correct font rendering, correct text direction
- [ ] **Maintenance mode is OFF**
- [ ] **Backup tested**: run `php artisan cms:backup` via SSH; files appear in Backups panel
- [ ] Party technical contact briefed on weekly automatic backup schedule
- [ ] Party technical contact has this handover kit and the deployment guide saved

---

## Post-handover support

For technical issues with the platform, contact the platform technical team (contact details provided separately to the party's technical contact — not included here).

For content and editorial questions, the party's Owner/Admin accounts have full panel access.
