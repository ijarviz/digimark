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
that blob is missing the lookup fails — either a genuinely bad/truncated/duplicate URL or a
deleted/private video (*"Tidak menemukan data video"*, no retry), or TikTok served a bot-check
page because this datacenter IP got **rate-limited under load** (*"TikTok memblokir
permintaan"*, retried).

Mitigations in place (all env-tunable, see the systemd unit):
- **Retry + linear backoff** on bot-check responses — `TIKTOK_SCRAPE_RETRIES` (default 2),
  in `lib/tiktok.js`. Backoff and page timeouts are kept short here because the PHP caller
  (`ScrapeTikTokDataSource`) waits ~120s for the whole lookup and the scraper serializes
  calls — a slow abandoned call backs the queue up for every request behind it.
- **Server-side pacing** of the `/api/video-stats` path — `TIKTOK_SCRAPE_MIN_DELAY_MS` /
  `TIKTOK_SCRAPE_MAX_DELAY_MS` (default 1500–3000) before each scrape, on top of the
  per-resource queue that already serializes them. Protects the `snapshot:tiktok` cron too.
- **Spaced Refresh-All loop** in `public/assets/js/tiktok-links.js` (client adds ~1s between
  links; the server delay above is the real throttle). A full ~100-link run takes ~15–20 min.
- **Proxy hook** — set `PROXY_URL` (+ `PROXY_USERNAME`/`PROXY_PASSWORD`) and the browser routes
  through it. A residential/mobile proxy is the actual cure for the IP blocking; not yet
  configured.

### Influencer Discovery
Admin-only page at **`/discovery/influencers`** (`Discovery\InfluencerDiscoveryController`,
route filter `role:admin` + the role re-checked in the controller) — searches Instagram and
TikTok **by keyword/niche** (not a known handle) via Apify actors and lists matching accounts
with follower count/bio. Distinct from `ScrapeTikTokDataSource`, which only reads metrics for
a URL you already have — this is for finding accounts you don't know about yet.

`App\Libraries\Discovery\ApifyDiscoveryService` calls Apify's `run-sync-get-dataset-items`
(blocks until the run finishes) via CI4 `CURLRequest`, using `APIFY_TOKEN` from `.env`:
- Instagram: `apify/instagram-search-scraper`, `searchType=user` — returns profiles directly.
- TikTok: `clockworks/tiktok-scraper` has no dedicated profile-search mode — with
  `searchSection=/user` it returns one **video** per matched profile (`resultsPerPage=1`), and
  the profile itself is read out of that video's `authorMeta` (handle, `fans`, `signature`, …).

A search can take up to ~60–70s per platform (Apify's own run budget), so the `digi-mark`
HAProxy backend carries a `timeout server 180s` override (see Deployment below) — the shared
50s default would cut the request off mid-search.

"Simpan ke tracking" (TikTok results only — `tiktok_link.url` validation requires a
`tiktok.com` URL, so there's nowhere to put an Instagram find yet) inserts into `tiktok_link`
with `discovery_source = 'apify'` (shown as a small badge on `tiktok/links`) and **`is_active =
0`**: a discovered row holds a *profile* URL, not a *video* URL, so `snapshot:tiktok` /
`refresh-one` have nothing to scrape until someone edits in a real video link and activates it.

### Improve Me (Jarvis Power menu)
Admin-only page at **`/admin/improve-me`** (`Admin\Improve\ImproveMeController`, route filter
`role:admin` + the role re-checked in the controller). An admin types a prompt; the app opens
a GitHub issue labeled `improve-me` on the repo (`Config\Github` → `github.repo`/`github.token`
in `.env`), which triggers a claude.ai cloud routine to make the change **on a branch and open
a PR** — never a direct push to `main`. `ImproveMeService` (in `app/Libraries/Improve/`, uses
CI4 `CURLRequest`, not Guzzle) polls each non-terminal request's issue on every page load:
labels `improve-me:in-progress`/`improve-me:done` drive the shown status, and the routine's
final comment is cached into `ai_improve_requests.result_comment` / `result_pr_url` once done.
With `github.token` unset the page still renders; submitting just flashes a "not configured"
notice. **Ported from automedia** (`app/Controllers/Admin/Improve/`, `app/Services/Improve/`,
`app/Views/admin/improve_me/`) — Bootstrap→Tailwind, Guzzle→CURLRequest, RBAC permission→plain
`role_name === 'admin'`.

The GitHub repo backing this is **`ijarviz/digimark`** (moved here from `RaihanFirdhan/DM-DATA-`
specifically so its owner, the Claude GitHub App installation, and the claude.ai account
running the cloud routine are all the same identity — see the routine note below for why that
mattered). `github.token` is a fine-grained PAT on `ijarviz/digimark` with Issues, Contents,
and Administration R/W.

The cloud routine ("Digimark Improve Me") is wired to auto-fire on new issues via a GitHub
webhook. **The webhook event name must be `issues.opened` (dotted, action-specific) — bare
`issues` silently registers but never fires.** The `create_webhook_trigger` API does not
validate the `events` value at all (confirmed: it accepts nonsense event names with a clean
200), so a wrong format gives no error — the trigger recheck for this is watching whether a
`gh issue create` on a fresh test issue produces a *new* routine run session within seconds
(`RemoteTrigger` `list_runs`/`get_run_log`), not just trusting the create call's response.

## Environment (`.env`)

Beyond the standard CI4 keys (`CI_ENVIRONMENT`, `app.baseURL`, `database.default.*`,
`encryption.key`), this app reads:

| Key | Purpose |
|---|---|
| `IG_APP_ID`, `IG_APP_SECRET` | Meta app — bootstrap fallback for `meta_app_config` |
| `IG_REDIRECT_URI` | OAuth callback, must match `/admin/ig-account/callback` |
| `github.repo`, `github.token` | "Improve Me" — repo (`owner/name`) + fine-grained PAT (Issues: R/W) for the GitHub-issue bridge |
| `APIFY_TOKEN` | Influencer Discovery — Apify account token used to run the search actors |
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
  `https_in`, switch Cloudflare to Full (strict). `backend digi-mark` also carries a
  `timeout server 180s` override (shared default is 50s) for Influencer Discovery's
  synchronous Apify calls — see that section above.
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
