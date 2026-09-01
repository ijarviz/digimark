<form method="get" action="<?= base_url('dashboard/tiktok') ?>" class="bg-surface-container-lowest border border-outline-variant rounded p-3 flex gap-4 items-end mb-gutter">
    <div>
        <label for="date" class="block text-label-caps font-label-caps text-on-surface-variant mb-1">Tanggal</label>
        <input type="date" id="date" name="date" value="<?= esc($date) ?>"
               class="h-[36px] px-3 rounded border border-outline-variant bg-surface text-body-sm font-body-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
    </div>
    <button type="submit" class="h-[36px] px-4 rounded bg-primary text-on-primary text-body-sm font-body-sm hover:bg-primary-container hover:text-on-primary-container transition-colors">Terapkan</button>
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
