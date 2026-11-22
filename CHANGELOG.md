# Changelog

All notable changes to Party Sites CMS are documented here.
Format: [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).
Versioning: [Semantic Versioning](https://semver.org/).

---

## [Unreleased]

## [0.2.0] – 2026-11-22

### Added
- **Phase interface translations (FR, PT, AR):** All admin panel, form, and public-theme strings translated into French, Portuguese and Arabic. Keys: `lang/{locale}/panel.php`, `lang/{locale}/forms.php`, `lang/{locale}.json`.
- **Demo content seeder (`DemoSeeder`):** Seeds fictional "Progressive Citizens Party" data (pages, posts, events, people, documents, menus) with full 4-locale translations. Runs only when `CMS_INSTANCE=demo`.
- **Demo mode (`CMS_INSTANCE=demo`):**
  - `DemoGuard` middleware: blocks password/email/name changes, 2FA, invitations, settings, backups and updater for the demo account; allows all content operations.
  - `DemoNoIndex` middleware: adds `X-Robots-Tag: noindex, nofollow` to every response.
  - Robots.txt forced to `Disallow: /` and `<meta name="robots" content="noindex,nofollow">` on every public page in demo mode.
  - Contact and join forms return a translated notice instead of storing or emailing submissions.
  - Mail allow-list (`CMS_DEMO_MAIL_ALLOWLIST`): `FilterDemoMailRecipients` listener cancels outgoing mail to non-listed addresses; logs recipient domain only.
  - `cms:demo-reset` artisan command: maintenance mode → purge all content, media, submissions, demo-user sessions and audit log → recreate demo account → reseed → clear caches → bring site back up. Runs only in demo mode.
  - Nightly schedule: `cms:demo-reset` at 02:30 server time (`routes/console.php`), only when `CMS_INSTANCE=demo`.
- **Demo information page (`/{locale}/demo`):** Public page explaining the demo (credentials, allowed/blocked actions, reset schedule). Registered before the catch-all slug route. Available in EN, FR, PT, AR.
- **Footer demo link:** Both Classic and Campaign theme layouts show "Try the admin panel (demo)" linking to `/{locale}/demo` in demo mode only.
- **Panel login demo hint box:** Shows demo email, password and a "Fill in" button that populates the login form; visible only in demo mode.
- Feature tests: `tests/Feature/Phase3/DemoModeTest.php` — verifies demo gate does not activate in production, demo guard blocks/allows correct routes, contact form stores in production and shows notice in demo, robots.txt behaviour, and `cms:demo-reset` refuses outside demo mode.

### Changed
- `config/cms.php`: renamed `demo_admin_*` keys to `demo_login_*` for clarity; added `demo_mail_allowlist`.
- `app/Http/Controllers/Public/ResolvesLocale::themeData()`: `$noindex` is now `true` whenever `CMS_INSTANCE=demo`, regardless of the per-site noindex setting.
- `app/Http/Controllers/Public/RobotsController`: same demo-forced noindex logic.
- `app/Providers/AppServiceProvider::boot()`: registers `FilterDemoMailRecipients` listener only in demo mode.
- `routes/web.php`: `DemoNoIndex` middleware added to the locale-prefixed public route group; `DemoPageController` import added.
- `routes/panel.php`: `DemoGuard` middleware added to the authenticated 2FA zone.
- `bootstrap/app.php`: `demo.guard` and `demo.noindex` middleware aliases registered.
- `ReleaseCommand`: `vendor/` removed from exclude list so shared-hosting ZIPs are self-contained.

## [0.1.0] – 2026-11-21

### Added
- Laravel 12 project skeleton.
- `config/cms.php`: product_name, version, instance, feature flags, panel_path, supported locales.
- Self-hosted fonts directory (`public/fonts/`): Inter and Playfair Display (to be downloaded, Phase 1b).
- `VERSION` file (semver).
- `LICENSE`: proprietary — draft, pending legal review.
- `CLAUDE.md`: build notes and decisions log.
- Packages: `laravel/fortify`, `intervention/image`, `mews/purifier`, `spatie/laravel-sitemap`.
- Tailwind CSS v4 + Vite (via `@tailwindcss/vite`).
- Alpine.js (CDN placeholder; to be bundled, Phase 4).
