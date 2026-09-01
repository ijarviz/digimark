<?php
/**
 * Minimal shell for unauthenticated pages (login) — Kinetic Analytics design system.
 */
$session = session();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Login') ?> - Social Orchestrator</title>
    <?= view('layouts/partials/tailwind_head') ?>
</head>
<body class="bg-surface text-on-surface font-body-md text-body-md min-h-screen flex items-center justify-center">
<div class="w-full max-w-sm">
    <div class="flex items-center gap-3 mb-6 justify-center">
        <div class="w-10 h-10 rounded-lg bg-primary flex items-center justify-center text-on-primary">
            <span class="material-symbols-outlined">hub</span>
        </div>
        <div>
            <h1 class="text-headline-md font-headline-md font-bold text-on-surface leading-tight">Social Orchestrator</h1>
            <p class="text-on-surface-variant text-label-caps font-label-caps">Admin Console</p>
        </div>
    </div>

    <div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-card-padding">
        <h2 class="text-headline-md font-headline-md text-on-surface mb-4"><?= esc($title ?? '') ?></h2>

        <?php if ($session->getFlashdata('error')): ?>
        <div class="mb-4 px-4 py-3 rounded border border-error bg-error-container/40 text-on-error-container text-body-sm font-body-sm flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px] text-error">error</span>
            <?= esc($session->getFlashdata('error')) ?>
        </div>
        <?php endif; ?>
        <?php if ($session->getFlashdata('success')): ?>
        <div class="mb-4 px-4 py-3 rounded border border-[#36B37E] bg-[#E3FCEF] text-[#006644] text-body-sm font-body-sm flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">check_circle</span>
            <?= esc($session->getFlashdata('success')) ?>
        </div>
        <?php endif; ?>

        <?= view($contentView, $contentData ?? []) ?>
    </div>
</div>
</body>
</html>
