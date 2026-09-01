<?php $roleName = session()->get('role_name'); ?>

<!-- Connect Status Header -->
<div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-card-padding flex justify-between items-center mb-gutter">
    <?php if ($account): ?>
    <div class="flex items-center gap-4">
        <div class="w-10 h-10 rounded-full bg-[#E1306C]/10 flex items-center justify-center text-[#E1306C]">
            <span class="material-symbols-outlined text-[24px]">photo_camera</span>
        </div>
        <div>
            <div class="flex items-center gap-2">
                <span class="text-body-md font-body-md font-semibold text-on-surface">Connected to @<?= esc($account['ig_username']) ?></span>
                <?php $expired = strtotime($account['token_expires_at']) < time(); ?>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full <?= $expired ? 'bg-error-container text-on-error-container' : 'bg-[#36B37E]/10 text-[#006644]' ?> text-[10px] font-bold tracking-wider uppercase">
                    <span class="w-1.5 h-1.5 rounded-full <?= $expired ? 'bg-error' : 'bg-[#36B37E]' ?>"></span>
                    <?= $expired ? 'Token Expired' : 'Active Sync' ?>
                </span>
            </div>
            <p class="text-body-sm font-body-sm text-on-surface-variant flex items-center gap-1 mt-0.5">
                <span class="material-symbols-outlined text-[14px]">timer</span>
                Token expires: <span class="<?= $expired ? 'text-error' : 'text-secondary' ?> font-medium"><?= esc($account['token_expires_at']) ?></span>
            </p>
        </div>
    </div>
    <?php if ($roleName === 'admin'): ?>
    <a href="<?= base_url('admin/ig-account/connect') ?>" class="h-[36px] px-4 rounded border border-outline-variant bg-surface hover:bg-surface-container-high transition-colors text-body-sm font-body-sm font-medium text-on-surface flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px]">refresh</span>
        Refresh Token
    </a>
    <?php endif; ?>
    <?php else: ?>
    <div class="flex items-center gap-4">
        <div class="w-10 h-10 rounded-full bg-surface-variant flex items-center justify-center text-on-surface-variant">
            <span class="material-symbols-outlined text-[24px]">photo_camera</span>
        </div>
        <span class="text-body-md font-body-md text-on-surface-variant">Belum ada akun Instagram yang terhubung.</span>
    </div>
    <?php if ($roleName === 'admin'): ?>
    <a href="<?= base_url('admin/ig-account') ?>" class="h-[36px] px-4 rounded bg-primary text-on-primary hover:bg-primary-container hover:text-on-primary-container transition-colors text-body-sm font-body-sm font-medium flex items-center gap-2">
        Connect Instagram
    </a>
    <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Filter Bar -->
<form method="get" action="<?= base_url('dashboard/instagram') ?>" id="date-filter-form" class="bg-surface-container-lowest border border-outline-variant rounded p-3 flex flex-wrap gap-4 items-end justify-between mb-gutter">
    <div class="flex flex-wrap gap-4 items-end">
        <div>
            <label for="from" class="block text-label-caps font-label-caps text-on-surface-variant mb-1">Dari Tanggal</label>
            <input type="date" id="from" name="from" value="<?= esc($from) ?>"
                   class="h-[36px] px-3 rounded border border-outline-variant bg-surface text-body-sm font-body-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
        </div>
        <div>
            <label for="to" class="block text-label-caps font-label-caps text-on-surface-variant mb-1">Sampai Tanggal</label>
            <input type="date" id="to" name="to" value="<?= esc($to) ?>"
                   class="h-[36px] px-3 rounded border border-outline-variant bg-surface text-body-sm font-body-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
        </div>
        <button type="submit" class="h-[36px] px-4 rounded bg-primary text-on-primary text-body-sm font-body-sm hover:bg-primary-container hover:text-on-primary-container transition-colors">Terapkan</button>
    </div>

    <div class="flex items-center gap-2">
        <span class="text-label-caps font-label-caps text-on-surface-variant mr-1">TAMPILAN:</span>
        <label class="px-3 py-1 rounded-full border border-primary text-primary font-body-sm text-body-sm bg-primary-fixed/20 cursor-pointer has-[:checked]:bg-primary has-[:checked]:text-on-primary">
            <input type="radio" name="view-mode" value="organic" checked class="hidden">Organik
        </label>
        <label class="px-3 py-1 rounded-full border border-outline-variant text-on-surface-variant font-body-sm text-body-sm cursor-pointer hover:bg-surface-container has-[:checked]:bg-primary has-[:checked]:text-on-primary has-[:checked]:border-primary">
            <input type="radio" name="view-mode" value="ads" class="hidden">Ads
        </label>
        <label class="px-3 py-1 rounded-full border border-outline-variant text-on-surface-variant font-body-sm text-body-sm cursor-pointer hover:bg-surface-container has-[:checked]:bg-primary has-[:checked]:text-on-primary has-[:checked]:border-primary">
            <input type="radio" name="view-mode" value="combined" class="hidden">Gabungan
        </label>
    </div>
</form>

<div id="organic-section">
    <!-- Metric Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-gutter mb-gutter">
        <div class="bg-surface-container-lowest border border-outline-variant rounded p-card-padding hover:shadow-[0_2px_4px_rgba(0,0,0,0.05)] transition-shadow">
            <div class="flex justify-between items-start mb-2">
                <span class="text-label-caps font-label-caps text-on-surface-variant">Follower Terbaru</span>
                <span class="material-symbols-outlined text-outline">group</span>
            </div>
            <div class="flex items-end gap-3">
                <span class="text-headline-lg font-headline-lg text-on-surface leading-none" id="stat-followers">-</span>
                <span class="flex items-center text-body-sm font-body-sm font-medium leading-none" id="stat-followers-trend"></span>
            </div>
        </div>
        <div class="bg-surface-container-lowest border border-outline-variant rounded p-card-padding hover:shadow-[0_2px_4px_rgba(0,0,0,0.05)] transition-shadow">
            <div class="flex justify-between items-start mb-2">
                <span class="text-label-caps font-label-caps text-on-surface-variant">Total Profile Visits</span>
                <span class="material-symbols-outlined text-outline">visibility</span>
            </div>
            <div class="flex items-end gap-3">
                <span class="text-headline-lg font-headline-lg text-on-surface leading-none" id="stat-visits">-</span>
            </div>
        </div>
        <div class="bg-surface-container-lowest border border-outline-variant rounded p-card-padding hover:shadow-[0_2px_4px_rgba(0,0,0,0.05)] transition-shadow">
            <div class="flex justify-between items-start mb-2">
                <span class="text-label-caps font-label-caps text-on-surface-variant">Total Reach (Profil)</span>
                <span class="material-symbols-outlined text-outline">travel_explore</span>
            </div>
            <div class="flex items-end gap-3">
                <span class="text-headline-lg font-headline-lg text-on-surface leading-none" id="stat-reach">-</span>
                <span class="flex items-center text-body-sm font-body-sm font-medium leading-none" id="stat-reach-trend"></span>
            </div>
        </div>
    </div>

    <!-- Profile Chart -->
    <div class="bg-surface-container-lowest border border-outline-variant rounded mb-gutter">
        <div class="p-card-padding border-b border-outline-variant">
            <h3 class="text-headline-md font-headline-md text-on-surface">Follower &amp; Profile Visit Harian</h3>
        </div>
        <div class="p-card-padding">
            <canvas id="profile-chart" height="90"></canvas>
        </div>
    </div>

    <!-- Content Table -->
    <div class="bg-surface-container-lowest border border-outline-variant rounded-lg overflow-hidden">
        <div class="p-card-padding border-b border-outline-variant">
            <h3 class="text-headline-md font-headline-md text-on-surface">Insight per Konten (Organik)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="content-table">
                <thead class="bg-surface border-b border-outline-variant text-label-caps font-label-caps text-on-surface-variant uppercase tracking-wider">
                <tr>
                    <th class="p-table-cell-padding">Konten</th>
                    <th class="p-table-cell-padding">Tipe</th>
                    <th class="p-table-cell-padding">Diposting</th>
                    <th class="p-table-cell-padding text-right">Reach</th>
                    <th class="p-table-cell-padding text-right">Impressions</th>
                    <th class="p-table-cell-padding text-right">Likes</th>
                    <th class="p-table-cell-padding text-right">Comments</th>
                    <th class="p-table-cell-padding text-right">Shares</th>
                    <th class="p-table-cell-padding text-right">Saves</th>
                    <th class="p-table-cell-padding text-right">Plays</th>
                </tr>
                </thead>
                <tbody class="text-body-sm font-body-sm text-on-surface">
                <tr><td colspan="10" class="p-table-cell-padding text-on-surface-variant">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="ads-section" hidden>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-gutter mb-gutter">
        <div class="bg-surface-container-lowest border border-outline-variant rounded p-card-padding">
            <div class="flex justify-between items-start mb-2">
                <span class="text-label-caps font-label-caps text-on-surface-variant">Total Ad Reach</span>
                <span class="material-symbols-outlined text-outline">campaign</span>
            </div>
            <span class="text-headline-lg font-headline-lg text-on-surface leading-none" id="stat-ad-reach">-</span>
        </div>
        <div class="bg-surface-container-lowest border border-outline-variant rounded p-card-padding">
            <div class="flex justify-between items-start mb-2">
                <span class="text-label-caps font-label-caps text-on-surface-variant">Total Ad Impressions</span>
                <span class="material-symbols-outlined text-outline">visibility</span>
            </div>
            <span class="text-headline-lg font-headline-lg text-on-surface leading-none" id="stat-ad-impressions">-</span>
        </div>
        <div class="bg-surface-container-lowest border border-outline-variant rounded p-card-padding">
            <div class="flex justify-between items-start mb-2">
                <span class="text-label-caps font-label-caps text-on-surface-variant">Total Spend</span>
                <span class="material-symbols-outlined text-outline">payments</span>
            </div>
            <span class="text-headline-lg font-headline-lg text-on-surface leading-none" id="stat-ad-spend">-</span>
        </div>
    </div>

    <div class="bg-surface-container-lowest border border-outline-variant rounded-lg overflow-hidden">
        <div class="p-card-padding border-b border-outline-variant">
            <h3 class="text-headline-md font-headline-md text-on-surface">Performa Campaign</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="ads-table">
                <thead class="bg-surface border-b border-outline-variant text-label-caps font-label-caps text-on-surface-variant uppercase tracking-wider">
                <tr>
                    <th class="p-table-cell-padding">Campaign</th>
                    <th class="p-table-cell-padding">Campaign ID</th>
                    <th class="p-table-cell-padding text-right">Reach</th>
                    <th class="p-table-cell-padding text-right">Impressions</th>
                    <th class="p-table-cell-padding text-right">Spend</th>
                    <th class="p-table-cell-padding">Konten Terkait</th>
                </tr>
                </thead>
                <tbody class="text-body-sm font-body-sm text-on-surface">
                <tr><td colspan="6" class="p-table-cell-padding text-on-surface-variant">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
window.__IG_CHART_DATA_URL__ = <?= json_encode(base_url('dashboard/instagram/chart-data') . '?from=' . $from . '&to=' . $to) ?>;
</script>
