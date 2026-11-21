# Changelog

All notable changes to Party Sites CMS are documented here.
Format: [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).
Versioning: [Semantic Versioning](https://semver.org/).

---

## [Unreleased]

## [0.1.0] – 2026-11-21

### Added
- Laravel 12 project skeleton.
- `config/cms.php`: product_name, version, instance, feature flags, panel_path, supported locales.
- Self-hosted fonts directory (`public/fonts/`): Inter and Playfair Display (to be downloaded, Phase 1b).
- `VERSION` file (semver).
- `LICENSE`: proprietary, ADAFP — draft, pending legal review.
- `CLAUDE.md`: build notes and decisions log.
- Packages: `laravel/fortify`, `intervention/image`, `mews/purifier`, `spatie/laravel-sitemap`.
- Tailwind CSS v4 + Vite (via `@tailwindcss/vite`).
- Alpine.js (CDN placeholder; to be bundled, Phase 4).
