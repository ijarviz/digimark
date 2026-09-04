<?php
/**
 * Shared page shell (Kinetic Analytics design system â€” see stitch/kinetic_analytics/DESIGN.md).
 * Controllers call:
 *   echo view('layouts/main', ['title' => ..., 'subtitle' => ..., 'activeNav' => ..., 'contentView' => 'instagram/dashboard', 'contentData' => [...]]);
 */
$session  = session();
$roleName = $session->get('role_name');
$username = $session->get('username');
$initial  = $username ? strtoupper(substr($username, 0, 1)) : '?';

$navLinkClass = static function (string $key) use ($activeNav) {
    return ($activeNav ?? '') === $key
        ? 'flex items-center gap-3 px-4 py-3 bg-primary-container text-on-primary-container border-l-4 border-primary rounded-r-full'
        : 'flex items-center gap-3 px-4 py-3 text-surface-variant hover:bg-surface-container-high hover:text-white transition-colors rounded-r-full';
};

// Append ?v=<file mtime> to local asset URLs. Cloudflare edge-caches
// /assets/* for hours (cache-control: max-age=14400 from the origin), so
// without this a deployed JS/CSS change keeps serving stale until the TTL
// lapses or someone purges the CDN. The versioned URL is a fresh cache key,
// so it's fetched from origin on the first hit after a deploy.
$assetUrl = static function (string $src): string {
    $path = (string) parse_url($src, PHP_URL_PATH);
    $file = FCPATH . ltrim($path, '/');

    if (! is_file($file)) {
        return $src;
    }

    return $src . (str_contains($src, '?') ? '&' : '?') . 'v=' . filemtime($file);
};
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Dashboard') ?> - Social Orchestrator</title>
    <?= view('layouts/partials/tailwind_head') ?>
    <?php if (! empty($extraHeadScripts)): foreach ($extraHeadScripts as $src): ?>
    <script src="<?= esc($assetUrl($src), 'attr') ?>"></script>
    <?php endforeach; endif; ?>
</head>
<body class="bg-surface text-on-surface font-body-md text-body-md min-h-screen">
<div class="flex min-h-screen">
    <!-- SideNavBar -->
    <aside class="h-screen w-64 fixed left-0 top-0 border-r border-outline-variant bg-on-surface flex flex-col overflow-y-auto z-50">
        <div class="p-6">
            <h1 class="text-headline-lg font-headline-lg font-bold text-white">Social Orchestrator</h1>
            <p class="text-body-sm font-body-sm text-surface-variant mt-1">Admin Console</p>
        </div>

        <nav class="flex-1 px-2 mt-2 space-y-1">
            <a href="<?= base_url('dashboard/instagram') ?>" class="<?= $navLinkClass('ig-dashboard') ?>">
                <span class="material-symbols-outlined">analytics</span>
                <span class="text-body-md font-body-md">Instagram Insight</span>
            </a>
            <a href="<?= base_url('dashboard/tiktok') ?>" class="<?= $navLinkClass('tiktok-dashboard') ?>">
                <span class="material-symbols-outlined">insights</span>
                <span class="text-body-md font-body-md">TikTok Metrics</span>
            </a>
            <a href="<?= base_url('tiktok/links') ?>" class="<?= $navLinkClass('tiktok-links') ?>">
                <span class="material-symbols-outlined">link</span>
                <span class="text-body-md font-body-md">TikTok Links</span>
            </a>
            <?php if (in_array($roleName, ['admin', 'content_manager'], true)): ?>
            <a href="<?= base_url('publish') ?>" class="<?= $navLinkClass('ig-publish') ?>">
                <span class="material-symbols-outlined">send</span>
                <span class="text-body-md font-body-md">Publish Instagram</span>
            </a>
            <?php endif; ?>

            <?php if ($roleName === 'admin'): ?>
            <div class="px-4 pt-4 pb-1 text-label-caps font-label-caps text-surface-variant/70">Admin</div>
            <a href="<?= base_url('admin/users') ?>" class="<?= $navLinkClass('admin-users') ?>">
                <span class="material-symbols-outlined">group</span>
                <span class="text-body-md font-body-md">Users</span>
            </a>
            <a href="<?= base_url('admin/api-settings') ?>" class="<?= $navLinkClass('admin-api-settings') ?>">
                <span class="material-symbols-outlined">key</span>
                <span class="text-body-md font-body-md">API Settings</span>
            </a>
            <a href="<?= base_url('admin/ig-account') ?>" class="<?= $navLinkClass('admin-ig-account') ?>">
                <span class="material-symbols-outlined">account_circle</span>
                <span class="text-body-md font-body-md">IG Account</span>
            </a>
            <a href="<?= base_url('admin/job-logs') ?>" class="<?= $navLinkClass('admin-job-logs') ?>">
                <span class="material-symbols-outlined">terminal</span>
                <span class="text-body-md font-body-md">Job Monitoring</span>
            </a>

            <div class="px-4 pt-4 pb-1 text-label-caps font-label-caps text-surface-variant/70">Jarvis Power</div>
            <a href="<?= base_url('admin/improve-me') ?>" class="<?= $navLinkClass('admin-improve-me') ?>">
                <span class="material-symbols-outlined">auto_fix_high</span>
                <span class="text-body-md font-body-md">Improve Me</span>
            </a>
            <?php endif; ?>
        </nav>

        <?php if ($roleName === 'admin'): ?>
        <div class="p-4 mt-auto">
            <a href="<?= base_url('admin/ig-account') ?>" class="w-full flex items-center justify-center gap-2 bg-primary text-on-primary py-2 px-4 rounded text-body-sm font-body-sm hover:bg-primary-container hover:text-on-primary-container transition-colors">
                <span class="material-symbols-outlined text-[16px]">add</span>
                Connect Account
            </a>
        </div>
        <?php endif; ?>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="flex-1 ml-64 flex flex-col min-h-screen bg-background">
        <!-- TopNavBar -->
        <header class="sticky top-0 z-40 border-b border-outline-variant bg-surface-container-lowest flex justify-between items-center h-16 px-margin-page">
            <div class="flex items-center gap-4 w-1/3">
                <div class="relative w-full max-w-md">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[20px]">search</span>
                    <input type="text" placeholder="Search accounts, jobs, or metrics..." disabled
                           class="w-full pl-10 pr-4 py-2 bg-surface rounded border border-outline-variant text-body-sm font-body-sm h-[36px] outline-none cursor-not-allowed opacity-70">
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" class="hover:bg-surface-container rounded-full p-2 transition-all opacity-60 cursor-not-allowed text-on-surface-variant" title="Belum tersedia" disabled>
                    <span class="material-symbols-outlined">notifications</span>
                </button>
                <button type="button" class="hover:bg-surface-container rounded-full p-2 transition-all opacity-60 cursor-not-allowed text-on-surface-variant" title="Belum tersedia" disabled>
                    <span class="material-symbols-outlined">history</span>
                </button>
                <div class="h-8 w-px bg-outline-variant mx-2"></div>
                <div class="relative group">
                    <button type="button" class="flex items-center gap-2 hover:bg-surface-container p-1 pr-3 rounded-full transition-colors border border-transparent hover:border-outline-variant">
                        <div class="w-8 h-8 rounded-full bg-primary text-on-primary flex items-center justify-center text-body-sm font-body-sm font-semibold">
                            <?= esc($initial) ?>
                        </div>
                        <span class="text-body-sm font-body-sm text-on-surface font-medium"><?= esc($username ?? '') ?></span>
                        <span class="material-symbols-outlined text-[18px] text-on-surface-variant">expand_more</span>
                    </button>
                    <div class="absolute right-0 mt-1 w-44 bg-surface-container-lowest border border-outline-variant rounded shadow-lg py-1 hidden group-hover:block z-50">
                        <div class="px-4 py-2 text-body-sm font-body-sm text-on-surface-variant border-b border-outline-variant">
                            <?= esc(ucfirst(str_replace('_', ' ', $roleName ?? ''))) ?>
                        </div>
                        <a href="<?= base_url('logout') ?>" class="block px-4 py-2 text-body-sm font-body-sm text-on-surface hover:bg-surface-container-low">Logout</a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Canvas -->
        <main class="flex-1 overflow-y-auto p-margin-page">
            <div class="max-w-[1600px] mx-auto">
                <div class="mb-gutter">
                    <h2 class="text-headline-lg font-headline-lg text-on-surface"><?= esc($title ?? '') ?></h2>
                    <?php if (! empty($subtitle)): ?>
                    <p class="text-on-surface-variant text-body-sm font-body-sm mt-1"><?= esc($subtitle) ?></p>
                    <?php endif; ?>
                </div>

                <?php if ($session->getFlashdata('error')): ?>
                <div class="mb-gutter px-4 py-3 rounded border border-error bg-error-container/40 text-on-error-container text-body-sm font-body-sm flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] text-error">error</span>
                    <?= esc($session->getFlashdata('error')) ?>
                </div>
                <?php endif; ?>
                <?php if ($session->getFlashdata('success')): ?>
                <div class="mb-gutter px-4 py-3 rounded border border-[#36B37E] bg-[#E3FCEF] text-[#006644] text-body-sm font-body-sm flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">check_circle</span>
                    <?= esc($session->getFlashdata('success')) ?>
                </div>
                <?php endif; ?>

                <?= view($contentView, $contentData ?? []) ?>
            </div>
        </main>
    </div>
</div>
<?php if (! empty($extraBodyScripts)): foreach ($extraBodyScripts as $src): ?>
<script src="<?= esc($assetUrl($src), 'attr') ?>"></script>
<?php endforeach; endif; ?>
</body>
</html>
