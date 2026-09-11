<form method="get" action="<?= base_url('dashboard/tiktok') ?>" class="bg-surface-container-lowest border border-outline-variant rounded p-3 flex flex-wrap gap-4 items-end mb-gutter">
    <div>
        <label for="filter-url" class="block text-label-caps font-label-caps text-on-surface-variant mb-1">URL</label>
        <input type="text" id="filter-url" name="url" value="<?= esc($filters['url']) ?>" placeholder="Cari URL..."
               class="h-[36px] px-3 rounded border border-outline-variant bg-surface text-body-sm font-body-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary w-56">
    </div>
    <div>
        <label for="filter-creator" class="block text-label-caps font-label-caps text-on-surface-variant mb-1">Creator</label>
        <input type="text" id="filter-creator" name="creator" value="<?= esc($filters['creator']) ?>" placeholder="@creator..."
               class="h-[36px] px-3 rounded border border-outline-variant bg-surface text-body-sm font-body-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary w-40">
    </div>
    <div>
        <label for="filter-source" class="block text-label-caps font-label-caps text-on-surface-variant mb-1">Source</label>
        <select id="filter-source" name="source"
                class="h-[36px] px-3 rounded border border-outline-variant bg-surface text-body-sm font-body-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
            <option value="">Semua Source</option>
            <option value="oauth" <?= $filters['source'] === 'oauth' ? 'selected' : '' ?>>OAuth</option>
            <option value="scrape" <?= $filters['source'] === 'scrape' ? 'selected' : '' ?>>Scrape</option>
        </select>
    </div>
    <div>
        <label for="filter-min-views" class="block text-label-caps font-label-caps text-on-surface-variant mb-1">Min. Views</label>
        <input type="number" min="0" id="filter-min-views" name="min_views" value="<?= esc((string) ($filters['min_views'] ?? '')) ?>"
               class="h-[36px] px-3 rounded border border-outline-variant bg-surface text-body-sm font-body-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary w-24">
    </div>
    <div>
        <label for="filter-min-likes" class="block text-label-caps font-label-caps text-on-surface-variant mb-1">Min. Likes</label>
        <input type="number" min="0" id="filter-min-likes" name="min_likes" value="<?= esc((string) ($filters['min_likes'] ?? '')) ?>"
               class="h-[36px] px-3 rounded border border-outline-variant bg-surface text-body-sm font-body-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary w-24">
    </div>
    <div>
        <label for="filter-min-comments" class="block text-label-caps font-label-caps text-on-surface-variant mb-1">Min. Comments</label>
        <input type="number" min="0" id="filter-min-comments" name="min_comments" value="<?= esc((string) ($filters['min_comments'] ?? '')) ?>"
               class="h-[36px] px-3 rounded border border-outline-variant bg-surface text-body-sm font-body-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary w-24">
    </div>
    <div>
        <label for="filter-min-shares" class="block text-label-caps font-label-caps text-on-surface-variant mb-1">Min. Shares</label>
        <input type="number" min="0" id="filter-min-shares" name="min_shares" value="<?= esc((string) ($filters['min_shares'] ?? '')) ?>"
               class="h-[36px] px-3 rounded border border-outline-variant bg-surface text-body-sm font-body-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary w-24">
    </div>
    <div>
        <label for="filter-min-saves" class="block text-label-caps font-label-caps text-on-surface-variant mb-1">Min. Saves</label>
        <input type="number" min="0" id="filter-min-saves" name="min_saves" value="<?= esc((string) ($filters['min_saves'] ?? '')) ?>"
               class="h-[36px] px-3 rounded border border-outline-variant bg-surface text-body-sm font-body-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary w-24">
    </div>
    <div>
        <label for="date" class="block text-label-caps font-label-caps text-on-surface-variant mb-1">Tanggal Data</label>
        <input type="date" id="date" name="date" value="<?= esc($date) ?>"
               class="h-[36px] px-3 rounded border border-outline-variant bg-surface text-body-sm font-body-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
    </div>
    <button type="submit" class="h-[36px] px-4 rounded bg-primary text-on-primary text-body-sm font-body-sm hover:bg-primary-container hover:text-on-primary-container transition-colors">Terapkan</button>
    <?php if ($filters['url'] !== '' || $filters['creator'] !== '' || $filters['source'] !== '' || $filters['min_views'] !== null || $filters['min_likes'] !== null || $filters['min_comments'] !== null || $filters['min_shares'] !== null || $filters['min_saves'] !== null): ?>
    <a href="<?= base_url('dashboard/tiktok') ?>" class="h-[36px] inline-flex items-center text-body-sm font-body-sm text-on-surface-variant hover:text-primary px-2">Reset</a>
    <?php endif; ?>
</form>

<div class="bg-surface-container-lowest border border-outline-variant rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface border-b border-outline-variant text-label-caps font-label-caps text-on-surface-variant uppercase tracking-wider">
            <tr>
                <th class="p-table-cell-padding">URL</th>
                <th class="p-table-cell-padding">Creator</th>
                <th class="p-table-cell-padding">Source</th>
                <th class="p-table-cell-padding text-right">Views</th>
                <th class="p-table-cell-padding text-right">Likes</th>
                <th class="p-table-cell-padding text-right">Comments</th>
                <th class="p-table-cell-padding text-right">Shares</th>
                <th class="p-table-cell-padding text-right">Saves</th>
                <th class="p-table-cell-padding">Tanggal Data</th>
            </tr>
            </thead>
            <tbody class="text-body-sm font-body-sm text-on-surface">
            <?php if (empty($links)): ?>
            <tr><td colspan="9" class="p-table-cell-padding text-on-surface-variant">Belum ada link TikTok.</td></tr>
            <?php endif; ?>
            <?php foreach ($links as $link): $insight = $link['insight']; ?>
            <tr class="h-[48px] border-b border-outline-variant hover:bg-surface-container-low transition-colors">
                <td class="p-table-cell-padding"><a href="<?= esc($link['url']) ?>" target="_blank" rel="noopener" class="text-primary hover:underline"><?= esc($link['url']) ?></a></td>
                <td class="p-table-cell-padding text-on-surface-variant"><?= esc($link['creator_handle'] ?? '-') ?></td>
                <td class="p-table-cell-padding">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-label-caps tracking-wide border <?= $link['data_source'] === 'oauth' ? 'bg-primary-fixed text-on-primary-fixed border-primary-fixed-dim' : 'bg-surface-container-highest text-on-surface-variant border-outline-variant' ?>">
                        <?= esc(strtoupper($link['data_source'])) ?>
                    </span>
                </td>
                <td class="p-table-cell-padding text-right font-data-mono text-data-mono"><?= $insight['views'] ?? '-' ?></td>
                <td class="p-table-cell-padding text-right font-data-mono text-data-mono"><?= $insight['likes'] ?? '-' ?></td>
                <td class="p-table-cell-padding text-right font-data-mono text-data-mono"><?= $insight['comments'] ?? '-' ?></td>
                <td class="p-table-cell-padding text-right font-data-mono text-data-mono"><?= $insight['shares'] ?? '-' ?></td>
                <td class="p-table-cell-padding text-right font-data-mono text-data-mono"><?= $insight['saves'] ?? '-' ?></td>
                <td class="p-table-cell-padding text-on-surface-variant font-data-mono text-data-mono"><?= esc($insight['snapshot_date'] ?? '-') ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
