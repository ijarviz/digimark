<?php
/**
 * Minimal shell for unauthenticated pages (login).
 */
$session = session();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Login') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/tokens.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body>
<div class="auth-shell">
    <div class="card auth-card">
        <h1><?= esc($title ?? '') ?></h1>

        <?php if ($session->getFlashdata('error')): ?>
        <div class="flash flash-error"><?= esc($session->getFlashdata('error')) ?></div>
        <?php endif; ?>
        <?php if ($session->getFlashdata('success')): ?>
        <div class="flash flash-success"><?= esc($session->getFlashdata('success')) ?></div>
        <?php endif; ?>

        <?= view($contentView, $contentData ?? []) ?>
    </div>
</div>
<script src="<?= base_url('assets/js/app.js') ?>"></script>
</body>
</html>
