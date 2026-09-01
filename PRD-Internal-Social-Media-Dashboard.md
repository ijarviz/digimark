# PRD: Internal Social Media Dashboard & Content Management

**Versi:** 0.1 (Draft)
**Tanggal:** 1 September 2026
**Status:** Untuk direview — banyak asumsi teknis perlu divalidasi sebelum estimasi timeline final

---

## 1. Latar Belakang

Perusahaan membutuhkan satu sistem internal untuk memantau performa konten Instagram (organik & ads) serta TikTok, tanpa harus login manual ke tiap platform. Sistem ini juga diharapkan bisa mengelola publish/schedule konten Instagram langsung dari website, sehingga tim tidak perlu membuka aplikasi Instagram.

Akses dibatasi hanya untuk karyawan internal melalui username & password yang dibuat oleh admin (bukan self-registration).

---

## 2. Masalah yang Diselesaikan

- Data performa konten tersebar di beberapa tempat (Instagram Insights, Ads Manager, TikTok) — tidak ada satu dashboard terpusat.
- Tidak ada riwayat historis follower/profile visit harian karena platform hanya menyimpan window waktu terbatas.
- Proses publish konten manual per-platform memakan waktu tim konten.

---

## 3. Tujuan & Success Metrics

| Tujuan | Metrik |
|---|---|
| Sentralisasi data performa konten | Tim tidak perlu buka Instagram/TikTok manual untuk cek angka harian |
| Riwayat data historis tersedia | Data follower & insight tersimpan harian, bisa ditarik grafik >30 hari |
| Efisiensi publishing | Waktu publish konten dari website vs manual berkurang X% (perlu baseline) |

---

## 4. User & Role (wajib ada — jangan single-role)

| Role | Akses |
|---|---|
| **Admin** | Kelola user, kelola koneksi akun IG/TikTok, full akses semua data |
| **Content Manager** | Bisa lihat insight, upload/schedule konten |
| **Viewer** | Hanya bisa lihat dashboard insight (read-only), tidak bisa publish |

> Catatan: single username/password tanpa role adalah risiko keamanan untuk data yang menyimpan access token API. Role-based access minimal 3 tingkat di atas wajib ada sejak MVP.

---

## 5. Scope — Dibagi Bertahap (Bukan Satu Rilis)

Alasan pembagian fase: fitur publishing Instagram bergantung pada **App Review Meta** yang prosesnya di luar kendali internal (bisa berminggu-minggu, bisa ditolak). Insight/dashboard tidak bergantung pada approval itu, jadi bisa jalan lebih dulu.

### Fase 1 — Dashboard Insight (MVP)
- Login internal dengan role-based access
- Koneksi akun Instagram Business (via Facebook Login OAuth, permission `instagram_basic`, `instagram_manage_insights`, `pages_show_list`)
- Snapshot harian otomatis (cron/queue job) untuk:
  - Insight per konten: reach, impressions organik, like, comment, share, save
  - Insight profil: profile visits, follower count per tanggal
- Dashboard filter per tanggal / rentang tanggal / per konten
- Riwayat data tersimpan di database sendiri (bukan real-time query API), karena Instagram tidak simpan histori panjang

**Di luar scope Fase 1:** data Ads, publishing, TikTok.

### Fase 2 — Instagram Ads Insight
- Butuh akses terpisah: **Marketing API** dengan permission `ads_read`
- Prasyarat: konfirmasi siapa pemegang akses Ads Manager (internal/agency), dan izinnya
- Insight: reach & views dari campaign ads per konten/tanggal

### Fase 3 — Publish & Schedule Instagram dari Website
- Prasyarat keras: **Meta App Review** untuk permission `instagram_content_publish` harus lolos dulu — ini bukan sekadar coding, ada proses submission ke Meta yang bisa ditolak atau butuh revisi use case
- Batasan API yang perlu didesain dari awal:
  - Rate limit publish (~25 post/24 jam per akun IG Business)
  - Proses container creation → status polling (video butuh waktu processing) → baru publish
  - Tidak semua tipe konten didukung sama (single image vs carousel vs reels vs story punya alur API berbeda)
- Fitur schedule = job queue internal yang trigger publish API di waktu terjadwal (bukan native scheduling dari Meta)

### Fase 4 — TikTok Metrics (Best-Effort, Bukan Fitur Inti)
- **Peringatan desain:** tidak ada API resmi TikTok untuk narik metrics dari sembarang URL video pihak lain. Dua opsi realistis:
  1. **TikTok Display API resmi** — hanya bisa narik data video milik akun yang sudah OAuth-connect ke aplikasi kamu (bukan asal paste link kompetitor/akun lain). Legal, tapi tidak sebebas "tempel link mana saja".
  2. **Scraping non-resmi** — bisa ambil data dari link sembarang, tapi melanggar ToS TikTok, rawan IP-block, dan bisa berhenti bekerja kapan saja tanpa notice.
- Rekomendasi: mulai dari opsi 1 untuk akun resmi perusahaan. Kalau tetap butuh opsi 2 untuk keperluan riset/kompetitor, itu harus disetujui sebagai keputusan risiko sadar oleh manajemen (bukan default teknis), dan diberi fallback kalau scraper berhenti jalan.

---

## 6. Kebutuhan Non-Fungsional

- **Keamanan:** access token Instagram/TikTok disimpan **terenkripsi** di database (bukan plaintext), refresh token otomatis sebelum expired
- **Auth internal:** hash password (bcrypt/argon2), rate limit login, opsional 2FA untuk role Admin
- **Reliabilitas job:** snapshot harian & publish job harus punya retry mechanism + alert kalau gagal (misal token expired, rate limit tercapai)
- **Audit log:** siapa upload/schedule konten apa dan kapan — penting kalau lebih dari satu Content Manager

---

## 7. Risiko & Pertanyaan Terbuka

| Risiko | Dampak | Mitigasi |
|---|---|---|
| App Review Meta ditolak/lambat | Fase 3 (publish) tertunda tanpa kepastian waktu | Mulai submission di awal, paralel dengan development Fase 1–2 |
| Akses Ads Manager tidak dipegang tim internal | Fase 2 tidak bisa jalan | Konfirmasi ke tim marketing/agency dulu sebelum development dimulai |
| TikTok scraping berhenti bekerja | Fitur TikTok mendadak mati | Jangan jadikan fitur inti/SLA; siapkan fallback manual |
| Data historis tidak bisa ditarik mundur (baru mulai dari hari sistem live) | Tidak ada data "sebelum go-live" | Set ekspektasi ke stakeholder sejak awal — histori mulai dari nol |

**Pertanyaan yang perlu dijawab sebelum development dimulai:**
1. Berapa banyak akun Instagram Business yang perlu dikelola dalam sistem ini (1 brand atau multi-brand)?
2. Siapa yang pegang akses Facebook Business Manager & Ads Manager saat ini?
3. Apakah TikTok metrics untuk akun sendiri saja, atau juga untuk kompetitor/link sembarang? (Ini menentukan opsi 1 vs 2 di Fase 4)
4. Berapa lama data historis perlu disimpan (retention policy)?

---

## 8. Di Luar Scope (Eksplisit)

- Platform selain Instagram & TikTok
- Analisis sentiment/AI-generated insight otomatis
- Self-service registration (semua user dibuat manual oleh Admin)
