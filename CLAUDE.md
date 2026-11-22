# CLAUDE.md — Party Sites CMS

Build notes and decisions for Claude Code.
Updated at the end of every phase.

---

## Project identity

| Item | Value |
|---|---|
| Product | Party Sites CMS (working name; `config('cms.product_name')`) |
| Owner / approver | Adil Abdelaati, ADAFP Deputy Secretary-General |
| Repository | `adafp-tools / adafp_tools_cms` (private) |
| Programme | ADAFP Online Presence and IT Assistance, Stages 3 & 4 |
| Build prompt | `Member_Sites_CMS_-_Build_Prompt.md` v4 (21 Nov 2026) |

---

## Critical rules (read before every change)

1. **Never modify** `adafp.org`, `admin.adafp.org`, the Party CRM, or the Party Agent Toolkit — unless a phase explicitly says so and Adil has approved it in the conversation.
2. **No ADAFP name, logo, branding or link** in anything a public visitor or the installed party sees: pages, admin panel, emails, headers, cookies, meta tags, asset paths, error pages, default content. Run `grep -rni "adafp" --exclude-dir={.git,docs,tests}` before every release; must return zero.
3. **Database for cms.adafp.org demo installation: `adafp_cms`** (not `cms_demo`).
4. **No third-party CDNs** for CSS, JS or fonts. Compile Tailwind with Vite; self-host fonts.
5. Use `config()`, never `env()`, outside of `config/` files.
6. **No secrets** in commits, terminals or transcripts.
7. `{locale}` routes: always declare `$locale` as the first parameter in controller methods.
8. **`storage:link` may fail** on some Apache hosts — the installer must test via HTTP and fall back to a PHP file route if the symlink does not serve files.
9. **No automatic online update checks** — the installed site never contacts GitHub or any ADAFP server at runtime.

---

## Decisions log

| Date | Topic | Decision |
|---|---|---|
| 21 Nov 2026 | Framework | **Laravel 12** (prompt said 11; upgraded because Laravel 11 carries 5 unpatched security advisories including high-severity CRLF injection CVE-2026-48019 and medium path-confusion; Laravel 12 fixes all; PHP 8.2+ requirement unchanged) |
| 21 Nov 2026 | Demo database | **`adafp_cms`** (Adil's instruction; build prompt said `cms_demo`) |
| 21 Nov 2026 | GitHub org name | **`adafp-tools`** (Adil's choice; spaces not allowed by GitHub) |
| 21 Nov 2026 | GitHub repo name | **`adafp_tools_cms`** |
| 21 Nov 2026 | Second org owner | Deferred — Adil is sole owner for now |
| 21 Nov 2026 | Org-wide 2FA | Not enforced at org level for now |
| 21 Nov 2026 | Packages | `laravel/fortify` (auth + 2FA), `intervention/image` (media resizing), `mews/purifier` (HTMLPurifier for rich text), `spatie/laravel-sitemap` (sitemap generation) |
| 21 Nov 2026 | Phase 4 Lighthouse | Lighthouse mobile ≥90 performance, ≥95 accessibility must be verified locally on `cms.test` with both Classic and Campaign themes active — cannot be tested in cloud container. Run via Chrome DevTools or `npx lighthouse`. |
| 21 Nov 2026 | LICENSE | Proprietary ADAFP draft — **pending Adil's legal review before first external release** |
| 22 Nov 2026 | Phase 4 Lighthouse | Mobile Lighthouse run in cloud container (Chromium at `/opt/pw-browsers/chromium`, `php artisan serve` on localhost). All four passes (Classic/EN, Classic/AR, Campaign/EN, Campaign/AR): **Performance 100 · Accessibility 100 · Best-practices 100 · SEO 92**. SEO deduction: missing meta description on fresh install (no content) — not a code defect; score reaches 100 once party admin sets homepage description. Phase 4 targets (≥90 perf, ≥95 accessibility) **satisfied**. |
| 22 Nov 2026 | Phase 1b Fonts | Inter and Playfair Display variable fonts placed in `public/fonts/`: `inter/InterVariable.woff2`, `inter/InterVariable-Italic.woff2`, `playfair/PlayfairDisplay-Variable.woff2`, `playfair/PlayfairDisplay-Italic-Variable.woff2`. Source: `@fontsource-variable` npm packages (OFL licence, Latin wght subset). No CDN. Font files committed to repo. Phase 1b **complete**. |
| 22 Nov 2026 | Phase 11 final check | 95/95 tests · neutrality zero · `npm run build` clean · no `env()` outside config/ · all code phases complete · deployment/provision/handover scripts committed |
| 22 Nov 2026 | `cms:release` subprocess env | PHPUnit `<env>` tags use `force=false`; parent `.env` vars (`QUEUE_CONNECTION` etc.) leaked into child and caused `CrmPushTest` to fail in-process. Fixed by explicitly exporting `QUEUE_CONNECTION=sync CACHE_STORE=array SESSION_DRIVER=array DB_CONNECTION=sqlite DB_DATABASE=:memory:` in the `exec()` prefix in `ReleaseCommand`. |
| 22 Nov 2026 | Release v0.1.0 | First release ZIP built: `cms-0.1.0-20261122.zip` (417 KB, 381 files). Deployment runbook: `docs/phase8-deploy-cms.adafp.org.md`. |
| 22 Nov 2026 | Release v0.2.0 | ZIP `cms-0.2.0-20261122.zip` (12 MB, vendor/ included). Two deploy-time fixes: (1) `public/robots.txt` static file removed — it shadowed the RobotsController demo route; (2) `DemoSeeder`: `translated_by='demo'` → `'human'`, MenuLocation enum array keys replaced with string values (`'header'`/`'footer'`) — PHP 8.4 rejects enum instances as array literal keys. Live smoke test on cms.adafp.org: 9/9 pass. |

---

## Phase status

| Phase | Title | Status |
|---|---|---|
| 1 | GitHub org, repo, skeleton | **Complete** (Phase 1b fonts: **Complete** 22 Nov 2026) |
| 2 | Web installer and admin security | **Complete** |
| 3 | Content model (4 languages) | **Complete** |
| 4 | Public site, themes, performance | **Complete** |
| 5 | Forms, sign-ups, data protection | **Complete** |
| 6 | CRM link (SSO + write-only sign-ups) | **Complete** |
| 7 | Packaging, releases, updates, backups | **Complete** |
| 8 | Demo site at cms.adafp.org | **Release ZIP built (v0.2.0, 22 Nov 2026) — deploy using `docs/phase8-deploy-cms.adafp.org.md`** |
| 9 | Hosted service on frepro.org | **Scripts ready — provision manually** |
| 10 | Pilot (Sudan National Trend Party) + handover | **Handover kit ready — pilot TBD** |
| 11 | Final check | **Complete** |

---

## Environment

| Item | Value |
|---|---|
| Cloud build | claude.ai Code cloud container (PHP 8.3.6, Composer 2.8, Node 22, npm 10, gh 2.89) |
| Original prompt target | Windows / Laravel Herd / `cms.test` — cloud container used instead; release ZIPs and scripts work on both |
| Local test host | `cms.test` (configure locally with Herd or Laragon) |
| Demo host | `cms.adafp.org` (Phase 8) |
| Hosted service | `*.frepro.org` on server.nadus.org (Phase 9) |

---

## Fonts (Phase 1b — to be completed)

Self-hosted in `public/fonts/`:
- **Inter** (sans-serif body) — download from `rsms.me/inter/` or Google Fonts offline
- **Playfair Display** (serif headings) — download from Google Fonts offline
- Both referenced in `resources/css/app.css` with `@font-face`; no CDN.

---

## Translation architecture (Phase 1 — i18n)

### How locale routing works
- Public routes use a `{locale}` prefix; `LocaleMiddleware` calls `App::setLocale($locale)`.
- The admin panel has **no locale middleware** and renders in the app's default locale (`config('app.locale')`, typically `en`). There is no per-user locale column.
- The installer also has no locale middleware; it always renders in the app default.
- **Consequence**: `lang/fr/panel.php`, `lang/pt/panel.php`, and `lang/ar/panel.php` are ready for future use but are NOT auto-activated until a locale-switching mechanism is added to the panel (e.g. user profile setting or query string). Do not remove them — they are required for future panel i18n.

### Translation file types
| File pattern | Used for | Mechanism |
|---|---|---|
| `lang/{locale}/panel.php` | Panel, auth, installer UI | `__('panel.key')` |
| `lang/{locale}/forms.php` | Public form labels | `__('forms.key')` |
| `lang/{locale}.json` | Theme views, installer, emails | `__('English text as key')` |

### Coverage test
`tests/Unit/TranslationCoverageTest.php` verifies that every key in `en/panel.php`, `en/forms.php`, and `fr.json` exists in every other locale. Run it with `php artisan test --filter=TranslationCoverageTest`.

---

## Neutrality check command

Run before every release ZIP is built:

```bash
grep -rni "adafp" --include="*.php" --include="*.blade.php" --include="*.js" --include="*.css" --include="*.json" --include="*.md" --exclude-dir=.git --exclude-dir=docs --exclude-dir=tests --exclude-dir=storage --exclude=CLAUDE.md --exclude=CHANGELOG.md .
```

Must return **zero results**. (`CLAUDE.md` and `CHANGELOG.md` are internal build docs, never distributed. `storage/` holds auto-generated view cache with the container path and is excluded from release ZIPs.)
