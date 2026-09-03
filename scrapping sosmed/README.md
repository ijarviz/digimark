# Follower Counter (TikTok, Instagram & YouTube)

Ambil **jumlah follower/subscriber** akun publik TikTok, Instagram, dan YouTube lewat satu web
form. Tidak perlu login untuk semuanya — datanya diambil dari halaman profil/channel publik.

## Cara pakai (web app)

```
npm start
```

Buka `http://localhost:3000` — ada tiga kartu form: TikTok, Instagram, YouTube. Isi
username/handle (tanpa @), klik **Scrap**, jumlah follower/subscriber muncul di kartu
masing-masing.

Tiap hasil otomatis kecatat ke `data/follower-count-<platform>-<username>.csv` (timestamp, angka
mentah seperti "12.3K", dan angka yang sudah di-parse) — jalankan berkala buat lihat tren.

## Cara pakai (CLI, opsional — TikTok saja)

```
npm run count -- --username=namaakunkamu
```

## Cara kerja tiap platform

Ketiganya pakai Playwright (headless, tanpa login) buka halaman publik dan baca angkanya dari
DOM/HTML hasil render — bukan API resmi:

- **TikTok**: elemen `[data-e2e="followers-count"]` di halaman profil.
- **Instagram**: meta tag `og:description` di halaman profil, isinya teks
  "X Followers, Y Following, Z Posts". Sempat dicoba pakai `fetch` biasa (tanpa browser) supaya
  lebih ringan, tapi Instagram cuma mengembalikan app-shell kosong untuk request non-browser —
  jadi tetap butuh render lewat browser seperti TikTok.
- **YouTube**: teks "X subscribers" di dalam blok header channel (nama, handle, jumlah
  subscriber) — bukan cuma cari teks "subscribers" di seluruh halaman, karena bagian
  "featured/related channels" di bawahnya juga punya teks serupa milik channel lain.

Ketiga platform berbagi satu instance browser Playwright yang sama di server (lebih hemat
resource), tapi masing-masing punya context/page sendiri (cookies terpisah) supaya lookup satu
platform tidak saling ganggu dengan yang lain.

## Kenapa dibikin begini (biar tidak gampang diblokir)

- **Tidak perlu login di ketiga platform** — cuma baca data publik, jadi tidak ada akun yang
  dipertaruhkan/berisiko kena suspend.
- **Headless secara default** — tidak ada jendela browser yang keliatan. Kalau suatu saat perlu
  lihat prosesnya (misal buat debug), jalankan dengan `HEADLESS=false npm start`.
- **Cuma satu page load per lookup** — nggak ada scroll atau ambil data per-user, jadi ringan.
- **Auto-recovery** — kalau browser tertutup manual/crash, request berikutnya otomatis buka
  browser baru, server tidak perlu di-restart.
- **Jeda acak sebelum tiap request** (~0.8–1.8 detik) — menghindari pola akses yang terlalu
  mekanis/instan.

## Batasan & risiko yang perlu kamu tahu

- Tetap scraping dari tampilan web, bukan API resmi tiap platform — di luar Terms of Service,
  meski risikonya kecil karena tanpa login dan cuma baca angka publik.
- Kalau platform mengubah struktur halamannya (selector `data-e2e`, format meta tag
  `og:description`, struktur header channel YouTube), script perlu disesuaikan.
- Akun **private** followernya tidak akan terbaca (memang disembunyikan dari publik).
- Instagram kadang lebih agresif membatasi request tanpa login dari IP yang sama — kalau sering
  muncul error, jangan spam request beruntun, kasih jeda beberapa menit.
