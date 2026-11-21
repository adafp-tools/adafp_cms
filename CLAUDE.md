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

---

## Phase status

| Phase | Title | Status |
|---|---|---|
| 1 | GitHub org, repo, skeleton | **In progress** |
| 2 | Web installer and admin security | Pending |
| 3 | Content model (4 languages) | Pending |
| 4 | Public site, themes, performance | **Complete** |
| 5 | Forms, sign-ups, data protection | Pending |
| 6 | CRM link (SSO + write-only sign-ups) | Pending |
| 7 | Packaging, releases, updates, backups | Pending |
| 8 | Demo site at cms.adafp.org | Pending |
| 9 | Hosted service on frepro.org | Pending |
| 10 | Pilot (Sudan National Trend Party) + handover | Pending |
| 11 | Final check | Pending |

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

## Neutrality check command

Run before every release ZIP is built:

```bash
grep -rni "adafp" --include="*.php" --include="*.blade.php" --include="*.js" --include="*.css" --include="*.json" --include="*.md" --exclude-dir=.git --exclude-dir=docs --exclude-dir=tests --exclude-dir=storage --exclude=CLAUDE.md --exclude=CHANGELOG.md .
```

Must return **zero results**. (`CLAUDE.md` and `CHANGELOG.md` are internal build docs, never distributed. `storage/` holds auto-generated view cache with the container path and is excluded from release ZIPs.)
