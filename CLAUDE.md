# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

`digimark` is an **Internal Social Media Dashboard** for a single brand: a CodeIgniter 4
(PHP 8.2+) app that pulls **Instagram** metrics from the Meta Graph API and **TikTok**
metrics (via a local scraper or creator OAuth), stores daily snapshots in MySQL, and shows
role-gated dashboards + an Instagram publish/schedule queue.

It is **not** an Autolaris storefront. Despite living under `/autolaris/websites/`, it shares
none of the sibling sites' pattern — no Shop/catalog API, no H2H shipping/payment, no
`produk`/`keranjang` domain. The only thing in common is the CI4 framework and the deploy
shape (Apache vhost on a high port behind HAProxy). Ignore the root `/autolaris/websites/CLAUDE.md`
storefront guidance here.

Design intent lives in two Indonesian docs at the repo root — read them before non-trivial
feature work:
- `PRD-Internal-Social-Media-Dashboard.md` — product requirements, phasing.
- `Technical-Design-Doc-Social-Media-Dashboard.md` — DB schema, job design, the "still open"
  decisions. Build phases: **Phase 1** = insight dashboards (done-ish), **Phase 2** = Ads,
  **Phase 3** = publish/schedule. The schema is already future-proofed for all three.

`stitch/` holds design mockups (rendered HTML + PNG screenshots per screen), not runnable code.

## Commands

Run everything from the repo root (`/autolaris/websites/digimark`).

```bash
composer install                       # PHP deps into vendor/
cp env .env                            # then edit; see "Environment" below
php spark key:generate                 # writes encryption.key into .env
php spark serve                        # dev server on http://localhost:8080

php spark migrate --all               # apply migrations (app/Database/Migrations)
php spark migrate:rollback            # undo last batch
php spark db:seed DatabaseSeeder      # RoleSeeder + AdminUserSeeder (creates admin user)
php spark dev:seed-fake-data          # fills dashboards with fake IG/TikTok data — CI_ENVIRONMENT=development ONLY

vendor/bin/phpunit                    # full test suite (single "App" suite = ./tests)
vendor/bin/phpunit tests/unit/HealthTest.php          # one file
vendor/bin/phpunit --filter testHealthEndpoint        # one method
```

`php builds [release|development|next]` toggles the CI4 dependency between the stable release
and dev branch — it rewrites `composer.json`/`phpunit.dist.xml`; run `composer update` after.

There is no linter/static-analysis config checked in.

## Architecture

### Request flow & auth
- **No third-party auth library.** `Auth\LoginController` validates against the `users` table
  (`UserModel::hashPassword` / `verifyPassword`) and sets session keys `logged_in`,
  `role_name`, plus user id/name.
- **`RoleFilter`** (alias `role` in `app/Config/Filters.php`) is the only gate. Routes opt in
  per group: `['filter' => 'role:admin,content_manager,viewer']`. Not logged in → redirect to
  `/login`; wrong role → 403 view. **Enforce role in the controller too** for anything
  sensitive — the filter only guards the route, and the TDD calls this out explicitly.
- Roles are fixed: `admin`, `content_manager`, `viewer` (seeded by `RoleSeeder`). `admin`
  routes live under the `/admin` group; content management (TikTok links, IG publish) is
  `admin,content_manager`; dashboards are all three.
- `app/Controllers` is organised by domain: `Auth/`, `Instagram/`, `TikTok/`, `Admin/`.
  Routes in `app/Config/Routes.php` are the source of truth for what exists.

### Data model: snapshots, not live API
Dashboards **never call the platform APIs on page load** (rate limits). Cron jobs take daily
snapshots into `*_insight_daily` tables; controllers only ever read those tables. Core tables
(see migrations, all dated `2026-09-01/02`): `roles`, `users`, `ig_account`, `ig_content`,
`ig_content_insight_daily`, `ig_profile_insight_daily`, `ig_ads_insight_daily`,
`tiktok_link`, `tiktok_creator_oauth`, `tiktok_insight_daily`, `publish_queue`,
`meta_app_config`, `job_run_log`, `audit_log`.

- **Single IG account** assumption (`ig_account` holds one connected Business account + its
  long-lived token, stored encrypted via CI4 Encryption).
- **`meta_app_config`** stores the Meta App ID/secret entered at `/admin/api-settings` — this
  is the runtime source; `IG_APP_ID`/`IG_APP_SECRET` env vars are only bootstrap fallback.
- `tiktok_link.data_source` is `oauth` or `scrape` per row and decides which code path
  `snapshot:tiktok` uses for that link. `tiktok_insight_daily.source` is recorded per row so a
  link can move between the two over time without rewriting history.
- `publish_queue` rows are polled by `process:publish-queue` (container-create → poll →
  publish); it has orchestration/tracking fields added in later migrations.

### Service libraries (`app/Libraries/`)
- `Instagram/` — `InstagramApiService` (Graph calls), `InstagramAdsApiService`,
  `InstagramOAuthService` + `InstagramTokenService` (connect + refresh long-lived token
  before H-7 of expiry), `PublishEngineService`.
- `TikTok/` — `TikTokTrackingService` depends on `TikTokDataSourceInterface`; concrete
  impls are `ScrapeTikTokDataSource` (HTTP call to the Node scraper — see below) and
  `TikTokCreatorOAuthService`. Swap point for the deliberately-unstable scraping method.
- `AuditLogger` — writes `audit_log` for user actions (login, publish, connect, …).

### Jobs (`app/Commands/`)
All snapshot/refresh commands extend **`BaseSnapshotCommand`**, which wraps `handleJob()` and
writes a `job_run_log` row (`success`/`partial`/`failed`) on every run — a job failing silently
would leave holes in dashboards, so this logging is mandatory, not optional. Commands:
`snapshot:ig-content`, `snapshot:ig-profile`, `snapshot:ig-ads`, `refresh:ig-token`,
`snapshot:tiktok` (per-link try/catch — one bad link never stops the batch),
`process:publish-queue`. `dev:seed-fake-data` is dev-only.

### TikTok scraper (separate service)
`scrapping sosmed/` is a **standalone Node (ESM) Express + Playwright** app — its own
`package.json`, not part of the PHP build. It exposes `GET /api/count` (follower/subscriber
count for tiktok/instagram/youtube) and `GET /api/video-stats` (TikTok video metrics) by
rendering public profile pages headless (no login). The CI4 app reaches it at
`TIKTOK_SCRAPER_BASE_URL`. It must be running for `data_source=scrape` links to sync.
Playwright is **pinned to 1.49.1** here because the server runs Node 18 (1.50+ requires Node 20).

It reads TikTok's `#__UNIVERSAL_DATA_FOR_REHYDRATION__` JSON blob from the video page. When
that blob is missing it throws *"Tidak menemukan data video"* — which means any of: a genuinely
bad/truncated/duplicate URL, a deleted/private video, **or** (most common in bulk) TikTok
served a bot-check page instead of the video. This server is a datacenter IP with no proxy,
so **bursts get rate-limited**: `snapshot:tiktok` cron and the "Refresh All" button both walk
~100 links and a chunk fail once TikTok trips. Paced one-at-a-time with a few seconds gap the
same links succeed. Durable fixes (not yet done): a residential/mobile proxy, spacing the
Refresh-All JS loop, and a retry-with-backoff in the scraper.

## Environment (`.env`)

Beyond the standard CI4 keys (`CI_ENVIRONMENT`, `app.baseURL`, `database.default.*`,
`encryption.key`), this app reads:

| Key | Purpose |
|---|---|
| `IG_APP_ID`, `IG_APP_SECRET` | Meta app — bootstrap fallback for `meta_app_config` |
| `IG_REDIRECT_URI` | OAuth callback, must match `/admin/ig-account/callback` |
| `TIKTOK_SCRAPER_BASE_URL` | URL of the `scrapping sosmed` service |
| `SEED_ADMIN_USERNAME` / `SEED_ADMIN_EMAIL` / `SEED_ADMIN_PASSWORD` | consumed by `AdminUserSeeder`; if password unset it auto-generates one and prints it once |

## Deployment on this server

Wired the same way as the sibling sites (Apache high port ← HAProxy host ACL), reachable at
**`dm.autogroup.co.id`**:

- **Apache**: vhost `*:96` in `/etc/apache2/sites-available/dm-autogroup-co-id.conf`
  (`Listen 96` in `ports.conf`), DocumentRoot `public/`.
- **HAProxy** (`/etc/haproxy/haproxy.cfg`): `backend digi-mark → 127.0.0.1:96`. The host ACL
  is in the **`http-in` (port 80)** frontend, *not* `https_in` — TLS is terminated at
  Cloudflare (SSL mode: Flexible), so the origin only ever sees plain HTTP. There is **no
  origin certificate yet**; `app.forceGlobalSecureRequests` must stay **off** or CI4 will
  redirect-loop behind Cloudflare. To move to real end-to-end TLS: drop a combined PEM in
  `/etc/haproxy/certs/dm.autogroup.co.id.pem`, add the `use_backend digi-mark` line to
  `https_in`, switch Cloudflare to Full (strict).
- **Real client IP**: every request arrives from HAProxy on `127.0.0.1`, so
  `App::$proxyIPs` trusts loopback and reads the visitor IP from Cloudflare's
  `CF-Connecting-IP` header. Without this the login throttle (`LoginController`, 5 tries /
  15 min) and `audit_log` would key on `127.0.0.1` for everyone — one bad password would
  lock out all users. Keep the origin reachable only via Cloudflare (or `CF-Connecting-IP`
  becomes spoofable).
- **Cron**: `/etc/cron.d/digimark` runs the snapshot/publish commands as `www-data`
  (TZ Asia/Jakarta), logging to `writable/logs/cron_*.log`.
- **Scraper**: systemd unit `digimark-scraper.service` runs `scrapping sosmed/server.js` as
  `www-data` on port **3010** (`PORT`, `HEADLESS=true`,
  `PLAYWRIGHT_BROWSERS_PATH=/opt/ms-playwright`, `HOME=/var/lib/digimark-scraper`); logs to
  `writable/logs/scraper.log`.
- **DB**: MySQL/MariaDB `digimark`, dedicated user `digimark_app@localhost`.
- Whole tree is owned by `www-data`; run `composer`/`php spark` as `www-data` to keep
  `writable/` writable by the web process.
