<?php
/**
 * Shared page shell. Controllers call:
 *   echo view('layouts/main', ['title' => ..., 'activeNav' => ..., 'contentView' => 'instagram/dashboard', 'contentData' => [...]]);
 */
$session  = session();
$roleName = $session->get('role_name');
$username = $session->get('username');
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Dashboard') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/tokens.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
    <?php if (! empty($extraHeadScripts)): foreach ($extraHeadScripts as $src): ?>
    <script src="<?= $src ?>"></script>
    <?php endforeach; endif; ?>
</head>
<body>
<div class="app-shell">
    <nav class="app-nav">
        <div class="brand">Social Dashboard</div>

        <a href="<?= base_url('dashboard/instagram') ?>" class="<?= ($activeNav ?? '') === 'ig-dashboard' ? 'active' : '' ?>">Instagram Insight</a>
        <a href="<?= base_url('dashboard/tiktok') ?>" class="<?= ($activeNav ?? '') === 'tiktok-dashboard' ? 'active' : '' ?>">TikTok Metrics</a>
        <a href="<?= base_url('tiktok/links') ?>" class="<?= ($activeNav ?? '') === 'tiktok-links' ? 'active' : '' ?>">TikTok Links</a>

        <?php if ($roleName === 'admin'): ?>
        <div class="nav-section">Admin</div>
        <a href="<?= base_url('admin/users') ?>" class="<?= ($activeNav ?? '') === 'admin-users' ? 'active' : '' ?>">Users</a>
        <a href="<?= base_url('admin/ig-account') ?>" class="<?= ($activeNav ?? '') === 'admin-ig-account' ? 'active' : '' ?>">IG Account</a>
        <a href="<?= base_url('admin/job-logs') ?>" class="<?= ($activeNav ?? '') === 'admin-job-logs' ? 'active' : '' ?>">Job Logs</a>
        <?php endif; ?>
    </nav>

    <main class="app-main">
        <div class="app-topbar">
            <h1><?= esc($title ?? '') ?></h1>
            <div class="user-tag">
                <?= esc($username ?? '') ?> &middot; <?= esc($roleName ?? '') ?>
                &middot; <a href="<?= base_url('logout') ?>">Logout</a>
            </div>
        </div>

        <?php if ($session->getFlashdata('error')): ?>
        <div class="flash flash-error"><?= esc($session->getFlashdata('error')) ?></div>
        <?php endif; ?>
        <?php if ($session->getFlashdata('success')): ?>
        <div class="flash flash-success"><?= esc($session->getFlashdata('success')) ?></div>
        <?php endif; ?>

        <?= view($contentView, $contentData ?? []) ?>
    </main>
</div>
<script src="<?= base_url('assets/js/app.js') ?>"></script>
<?php if (! empty($extraBodyScripts)): foreach ($extraBodyScripts as $src): ?>
<script src="<?= $src ?>"></script>
<?php endforeach; endif; ?>
</body>
</html>
