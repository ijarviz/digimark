# Technical Design Document: Internal Social Media Dashboard

**Versi:** 0.1 (Draft)
**Turunan dari:** PRD-Internal-Social-Media-Dashboard.md
**Konteks konfirmasi:** 1 akun Instagram Business (single brand), TikTok scope = link kreator afiliasi (bukan kompetitor)

---

## 1. Ruang Lingkup Dokumen

Dokumen ini mencakup desain teknis untuk **Fase 1 (Dashboard Insight)** secara penuh, dan menyiapkan skema/arsitektur yang sudah future-proof untuk Fase 2 (Ads) & Fase 3 (Publish/Schedule) supaya tidak perlu migrasi besar nanti.

Stack asumsi: **CodeIgniter 4**, MySQL/MariaDB, cron via system scheduler + CI4 Spark command, vanilla JS di frontend (sesuai pola kerja existing kamu).

---

## 2. Arsitektur Sistem (Overview)

```
[Meta Graph API]  <---OAuth/token--->  [CI4 Backend]
     |                                       |
     |  (daily snapshot job)                 |--- MySQL (data historis)
     v                                       |
[Cron Jobs] ---------------------------------|
     |                                       |
[TikTok: OAuth kreator (opsional) / Scraper] |
                                              v
                                    [Dashboard Web (internal, role-based)]
```

Komponen inti:
1. **Auth & Role Module** — login internal, role: Admin / Content Manager / Viewer
2. **Instagram Integration Module** — OAuth connect, token refresh, content sync, insight snapshot, (Fase 3: publish)
3. **TikTok Tracking Module** — input link manual, dua jalur data (OAuth kreator / scraper), insight snapshot
4. **Job Scheduler** — CI4 Spark commands dijalankan via cron, dicatat di job log untuk monitoring
5. **Dashboard/Reporting** — query dari tabel snapshot harian, bukan real-time API call (supaya cepat & tidak kena rate limit tiap dashboard dibuka)

---

## 3. Skema Database

### 3.1 Auth & Role
```
roles
- id (PK)
- name              -- admin, content_manager, viewer

users
- id (PK)
- name
- email
- username (unique)
- password_hash
- role_id (FK -> roles.id)
- is_active
- last_login_at
- created_at
```

### 3.2 Instagram — Akun & Token
```
ig_account
- id (PK)
- ig_business_id          -- Instagram Business Account ID dari Graph API
- ig_username
- fb_page_id
- access_token_encrypted  -- long-lived token, dienkripsi
- token_expires_at
- connected_by (FK -> users.id)
- connected_at
```
> Meski scope saat ini 1 akun, tetap dibuat sebagai tabel (bukan config statis) supaya kalau nanti nambah brand tidak perlu redesign dari nol.

### 3.3 Instagram — Konten & Insight
```
ig_content
- id (PK)
- ig_account_id (FK)
- ig_media_id (unique)     -- ID dari Graph API
- media_type               -- image, video, carousel, reels
- caption
- permalink
- thumbnail_url
- posted_at
- synced_at

ig_content_insight_daily
- id (PK)
- ig_content_id (FK)
- snapshot_date
- reach
- impressions
- likes
- comments
- shares
- saves
- plays                    -- untuk video/reels
- UNIQUE(ig_content_id, snapshot_date)

ig_profile_insight_daily
- id (PK)
- ig_account_id (FK)
- snapshot_date
- follower_count
- profile_visits
- reach
- impressions
- UNIQUE(ig_account_id, snapshot_date)
```

### 3.4 Instagram Ads — Fase 2 (skema disiapkan, belum diaktifkan)
```
ig_ads_insight_daily
- id (PK)
- ig_content_id (FK, nullable)   -- kalau ads-nya boost dari organic post
- campaign_id
- snapshot_date
- ad_reach
- ad_impressions
- spend
- UNIQUE(campaign_id, snapshot_date)
```

### 3.5 Publish Queue — Fase 3 (skema disiapkan, belum diaktifkan)
```
publish_queue
- id (PK)
- ig_account_id (FK)
- caption
- media_urls (JSON)
- media_type
- scheduled_at
- status              -- pending, processing, published, failed
- ig_media_id_result  -- diisi setelah publish sukses
- error_message
- created_by (FK -> users.id)
- created_at
```

### 3.6 TikTok — Link Tracking (hybrid: OAuth kreator / scraper)
```
tiktok_link
- id (PK)
- url
- tiktok_video_id
- creator_handle
- affiliate_note           -- nama campaign/kreator, catatan internal
- data_source              -- 'oauth' atau 'scrape'
- added_by (FK -> users.id)
- created_at

tiktok_creator_oauth        -- hanya diisi kalau kreator setuju connect resmi
- id (PK)
- creator_handle
- access_token_encrypted
- token_expires_at
- connected_at

tiktok_insight_daily
- id (PK)
- tiktok_link_id (FK)
- snapshot_date
- views
- likes
- comments
- shares
- source                   -- 'oauth' atau 'scrape', dicatat per baris (bisa berubah kalau kreator baru connect belakangan)
- UNIQUE(tiktok_link_id, snapshot_date)
```

### 3.7 Monitoring & Audit
```
job_run_log
- id (PK)
- job_name           -- snapshot_ig_content, snapshot_ig_profile, snapshot_tiktok, publish_processor
- status              -- success, partial, failed
- started_at
- finished_at
- error_message

audit_log
- id (PK)
- user_id (FK)
- action              -- login, publish_content, schedule_content, connect_account, dll
- entity_type
- entity_id
- meta (JSON)
- created_at
```

---

## 4. Alur OAuth Instagram

1. Admin klik "Connect Instagram" → redirect ke Facebook Login (Business Login flow)
2. User authorize dengan permission: `instagram_basic`, `instagram_manage_insights`, `pages_show_list`, `pages_read_engagement`
3. Callback terima short-lived user token → exchange ke **long-lived token** (~60 hari)
4. Ambil Page ID → ambil IG Business Account ID yang terhubung ke Page tsb
5. Simpan token terenkripsi di `ig_account`, catat `token_expires_at`
6. **Job refresh token** berjalan otomatis sebelum expired (idealnya H-7), karena kalau token expired semua job snapshot berikutnya gagal total

> Fase 3 (publish) butuh permission tambahan `instagram_content_publish` — ini yang harus lolos App Review Meta terpisah, tidak otomatis dapat dengan permission Fase 1.

---

## 5. Desain Job/Cron

| Job | Frekuensi | Fungsi |
|---|---|---|
| `snapshot:ig-content` | Harian (misal 02:00) | Loop semua `ig_content` aktif, tarik insight per media, insert ke `ig_content_insight_daily` |
| `snapshot:ig-profile` | Harian | Tarik follower_count & profile_visits, insert ke `ig_profile_insight_daily` |
| `refresh:ig-token` | Harian, cek expiry | Refresh token sebelum H-7 dari expired |
| `snapshot:tiktok` | Harian (bisa lebih sering, misal 2x/hari kalau butuh granularity) | Loop `tiktok_link`, ambil data via OAuth (kalau ada) atau scraper (fallback), insert ke `tiktok_insight_daily` |
| `process:publish-queue` (Fase 3) | Tiap 5 menit | Cek `publish_queue` status=pending & scheduled_at<=now, jalankan container creation → poll status → publish |

Semua job **wajib** log ke `job_run_log` — ini bukan opsional, karena kalau snapshot gagal diam-diam, dashboard akan menampilkan data yang bolong tanpa ada yang sadar. Tambahkan alert (email/Telegram bot) kalau job gagal 2x berturut-turut.

---

## 6. Catatan Desain TikTok (Bagian Paling Berisiko)

- **Prioritas:** tawarkan opsi connect resmi ke kreator afiliasi dulu (lebih stabil, legal). `tiktok_link.data_source` menentukan job mana yang dipakai untuk link tsb.
- **Untuk link tanpa OAuth:** scraping tetap perlu, tapi desain job harus **toleran kegagalan** — satu link gagal tidak boleh menggagalkan seluruh batch. Simpan `error_message` per link, bukan per job.
- **Keputusan teknis yang masih terbuka:** metode/library scraping mana yang dipakai (perlu riset terpisah, bisa berubah sewaktu-waktu mengikuti perubahan TikTok) — ini sengaja tidak dikunci di dokumen ini karena sifatnya rapuh dan perlu direview ulang secara berkala, bukan sekali desain lalu dianggap final.

---

## 7. Keamanan

- Token IG & TikTok OAuth disimpan **terenkripsi** (CI4 Encryption service / sodium), key encryption di `.env`, tidak pernah commit ke repo
- Password user di-hash (CI4 default: bcrypt/argon2)
- Role-based access diterapkan di level middleware/filter, bukan hanya UI (jangan hanya sembunyikan tombol — cek permission juga di controller)
- Rate limit login untuk cegah brute force

---

## 8. Yang Belum Diputuskan (Perlu Dijawab Sebelum Coding Fase 1 Dimulai)

1. Hosting cron job: VPS yang sudah ada, atau perlu server terpisah?
2. Retention data snapshot: disimpan selamanya, atau ada kebijakan arsip/hapus setelah X bulan?
3. Notifikasi kegagalan job: email, Telegram bot, atau channel lain?
4. Berapa banyak `tiktok_link` yang realistis dikelola per bulan? (Menentukan apakah scraping manual-trigger cukup, atau perlu queue-based supaya tidak overload sekaligus)
