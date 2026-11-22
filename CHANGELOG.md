# Changelog

All notable changes to Party Sites CMS are documented here.
Format: [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).
Versioning: [Semantic Versioning](https://semver.org/).

---

## [Unreleased]

## [0.2.0] – 2026-11-22

### Added
- **Interface translations (Phase 1)** — all theme, email, and installer views
  wrapped with `__()`. French, Portuguese, and Arabic translations added for
  six PHP groups (`panel`, `forms`, `auth`, `pagination`, `passwords`,
  `validation`) and three JSON files. `TranslationCompletenessTest` (23+ assertions).
- **Installer language selector (Phase 1b)** — EN/FR/PT/AR picker in the
  installer header; `InstallerLocaleMiddleware` persists choice across steps;
  `GET /installer/lang/{locale}` route.
- **`DemoContentSeeder` (Phase 2)** — fictional "National Progress Party" with
  four pages, three posts, three events, four people, three documents, two
  galleries, and header/footer menus — all in four languages. Zero ADAFP
  strings. Four seeder tests.
- **Demo mode (Phase 3)**:
  - `cms:demo-reset` command — truncates all content tables, re-seeds, clears
    caches; guarded by `CMS_INSTANCE=demo`.
  - `DemoNoindex` middleware — appended globally; forces
    `X-Robots-Tag: noindex, nofollow` on every response in demo mode.
  - `DemoGuard` middleware — appended to web group; blocks Fortify
    `user-password` / `user-profile-information` and panel
    `users.invite` / `users.destroy` in demo mode.
  - `DemoMailFilter` listener — strips recipients not in
    `CMS_DEMO_MAIL_ALLOWLIST`; cancels delivery if none remain.
  - Nightly schedule: `cms:demo-reset` at 00:00 when `CMS_INSTANCE=demo`.
- **Demo info page `/demo` (Phase 4)** — public page (available in all four
  locale prefixes) explaining the demo, showing login credentials with
  Alpine.js password-reveal, and linking to the admin panel. Returns 404 in
  production.
- **Demo footer links (Phase 4)** — Classic and Campaign themes: in demo mode
  a "Admin panel (demo)" link appears in the footer pointing to `/{locale}/demo`.
- **Panel login helper (Phase 4)** — in demo mode, the login page shows a
  yellow credentials card, pre-fills the email field, and hides the
  "Forgot password?" link.
- `lang/{en,fr,pt,ar}/demo.php` — 12-key translation files for the demo page.
- New `.env` keys for demo mode: `CMS_INSTANCE`, `CMS_DEMO_ADMIN_EMAIL`,
  `CMS_DEMO_ADMIN_PASSWORD`, `CMS_DEMO_MAIL_ALLOWLIST`.

### Fixed
- `active_locales` stored as JSON array by the web installer — now correctly
  stored as a CSV string (`en,fr,pt,ar`) matching what `LocaleMiddleware` reads.
- `default_locale` not persisted to the `settings` table by the web installer
  or `cms:install` command.
- `config('cms.default_locale')` not hydrated from DB at boot — fixed in
  `AppServiceProvider`.

### Changed
- Release ZIP now bundles `vendor/` (production dependencies only, stripped
  with `composer install --no-dev`) and `public/build/` compiled assets,
  making releases fully self-contained.
- `.env.example` is now included in release ZIPs.

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
