<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Akses Ditolak</title>
    <?= view('layouts/partials/tailwind_head') ?>
</head>
<body class="bg-surface text-on-surface font-body-md text-body-md min-h-screen flex items-center justify-center">
    <div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-card-padding max-w-sm text-center">
        <span class="material-symbols-outlined text-error text-[48px]">block</span>
        <h1 class="text-headline-lg font-headline-lg text-on-surface mt-2">403 — Akses Ditolak</h1>
        <p class="text-body-sm font-body-sm text-on-surface-variant mt-2 mb-4"><?= esc($message ?? 'Anda tidak memiliki akses ke halaman ini.') ?></p>
        <a href="<?= base_url('/') ?>" class="h-[36px] px-4 rounded bg-primary text-on-primary text-body-sm font-body-sm font-medium hover:bg-primary-container hover:text-on-primary-container transition-colors inline-flex items-center">Kembali ke Beranda</a>
    </div>
</body>
</html>
