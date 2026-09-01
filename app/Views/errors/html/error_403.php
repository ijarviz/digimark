<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>403 - Akses Ditolak</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/tokens.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body>
    <div class="auth-shell">
        <div class="card auth-card">
            <h1>403 — Akses Ditolak</h1>
            <p class="text-muted"><?= esc($message ?? 'Anda tidak memiliki akses ke halaman ini.') ?></p>
            <a class="btn btn-primary" href="<?= base_url('/') ?>">Kembali ke Beranda</a>
        </div>
    </div>
</body>
</html>
