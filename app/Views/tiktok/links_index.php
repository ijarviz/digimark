<div class="flex justify-between items-center mb-gutter">
    <div class="bg-surface-container-lowest border border-outline-variant rounded p-3 flex items-center gap-2">
        <span class="material-symbols-outlined text-outline text-sm">link</span>
        <span class="text-body-sm font-body-sm text-on-surface-variant">Showing <strong class="text-on-surface"><?= count($links) ?></strong> tracked entities</span>
    </div>
    <?php if ($canEdit): ?>
    <a href="<?= base_url('tiktok/links/new') ?>" class="bg-primary text-on-primary font-body-md text-body-md px-4 py-2 rounded shadow hover:bg-primary-container hover:text-on-primary-container transition-all flex items-center gap-2">
        <span class="material-symbols-outlined text-sm">add_link</span>
        Add TikTok Link
    </a>
    <?php endif; ?>
</div>

<div class="bg-surface-container-lowest border border-outline-variant rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface-container-low border-b border-outline-variant">
            <tr>
                <th class="py-3 px-4 font-label-caps text-label-caps text-on-surface-variant w-12 text-center">Status</th>
                <th class="py-3 px-4 font-label-caps text-label-caps text-on-surface-variant">Creator</th>
                <th class="py-3 px-4 font-label-caps text-label-caps text-on-surface-variant">Catatan / Segment</th>
                <th class="py-3 px-4 font-label-caps text-label-caps text-on-surface-variant">Budget</th>
                <th class="py-3 px-4 font-label-caps text-label-caps text-on-surface-variant">Source</th>
                <th class="py-3 px-4 font-label-caps text-label-caps text-on-surface-variant text-right">Views</th>
                <th class="py-3 px-4 font-label-caps text-label-caps text-on-surface-variant text-right">Engagement</th>
                <th class="py-3 px-4 font-label-caps text-label-caps text-on-surface-variant">Last Snapshot</th>
                <?php if ($canEdit): ?>
                <th class="py-3 px-4 font-label-caps text-label-caps text-on-surface-variant text-center w-28">Actions</th>
                <?php endif; ?>
            </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant">
            <?php if (empty($links)): ?>
            <tr><td colspan="9" class="py-4 px-4 text-on-surface-variant text-body-sm font-body-sm">Belum ada link TikTok.</td></tr>
            <?php endif; ?>
            <?php foreach ($links as $link): $insight = $link['latest_insight']; $initial = strtoupper(substr(ltrim($link['creator_handle'] ?? $link['url'], '@'), 0, 1)); ?>
            <tr class="hover:bg-surface-container-low transition-colors group h-12">
                <td class="py-2 px-4 text-center">
                    <?php if ($link['is_active']): ?>
                    <div class="w-6 h-6 rounded-full bg-tertiary-container text-on-tertiary-container flex items-center justify-center mx-auto" title="Aktif">
                        <span class="material-symbols-outlined text-[14px]">check</span>
                    </div>
                    <?php else: ?>
                    <div class="w-6 h-6 rounded-full bg-surface-variant text-on-surface-variant flex items-center justify-center mx-auto" title="Nonaktif">
                        <span class="material-symbols-outlined text-[14px]">pause</span>
                    </div>
                    <?php endif; ?>
                </td>
                <td class="py-2 px-4">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full bg-surface-variant text-on-surface-variant flex items-center justify-center text-[10px] font-bold shrink-0"><?= esc($initial) ?></div>
                        <div class="min-w-0">
                            <a href="<?= esc($link['url']) ?>" target="_blank" rel="noopener" class="font-body-md text-body-md font-medium text-on-surface hover:text-primary flex items-center gap-1 truncate max-w-[180px]">
                                <?= esc($link['creator_handle'] ?: 'Link #' . $link['id']) ?>
                                <span class="material-symbols-outlined text-[12px]">open_in_new</span>
                            </a>
                        </div>
                    </div>
                </td>
                <td class="py-2 px-4">
                    <div class="font-body-sm text-body-sm text-on-surface-variant truncate max-w-[160px]" title="<?= esc($link['affiliate_note'] ?? '') ?>"><?= esc($link['affiliate_note'] ?: '-') ?></div>
                    <div class="text-[10px] text-on-surface-variant mt-0.5"><?= esc($link['segment'] ? ucfirst($link['segment']) : '') ?><?= $link['segment'] && $link['gender'] ? ' &middot; ' : '' ?><?= esc($link['gender'] ? ucfirst($link['gender']) : '') ?></div>
                </td>
                <td class="py-2 px-4 font-data-mono text-data-mono text-on-surface-variant">
                    <?= $link['budget'] !== null ? 'Rp ' . number_format((float) $link['budget'], 0, ',', '.') : '-' ?>
                </td>
                <td class="py-2 px-4">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-label-caps tracking-wide border <?= $link['data_source'] === 'oauth' ? 'bg-primary-fixed text-on-primary-fixed border-primary-fixed-dim' : 'bg-surface-container-highest text-on-surface-variant border-outline-variant' ?>">
                        <?= esc(strtoupper($link['data_source'])) ?>
                    </span>
                </td>
                <td class="py-2 px-4 text-right">
                    <div class="font-data-mono text-data-mono text-on-surface"><?= $insight['views'] ?? '--' ?></div>
                </td>
                <td class="py-2 px-4 text-right">
                    <div class="flex justify-end gap-3 text-on-surface-variant">
                        <div class="flex items-center gap-1" title="Likes">
                            <span class="material-symbols-outlined text-[14px]">favorite</span>
                            <span class="font-data-mono text-data-mono"><?= $insight['likes'] ?? '--' ?></span>
                        </div>
                        <div class="flex items-center gap-1" title="Comments">
                            <span class="material-symbols-outlined text-[14px]">chat_bubble</span>
                            <span class="font-data-mono text-data-mono"><?= $insight['comments'] ?? '--' ?></span>
                        </div>
                        <div class="flex items-center gap-1" title="Shares">
                            <span class="material-symbols-outlined text-[14px]">share</span>
                            <span class="font-data-mono text-data-mono"><?= $insight['shares'] ?? '--' ?></span>
                        </div>
                        <div class="flex items-center gap-1" title="Saves">
                            <span class="material-symbols-outlined text-[14px]">bookmark</span>
                            <span class="font-data-mono text-data-mono"><?= $insight['saves'] ?? '--' ?></span>
                        </div>
                    </div>
                </td>
                <td class="py-2 px-4">
                    <div class="font-data-mono text-data-mono text-on-surface-variant"><?= esc($insight['snapshot_date'] ?? '-') ?></div>
                    <div class="text-[10px] text-on-surface-variant">Sync: <?= esc($link['last_synced_at'] ?? '-') ?></div>
                </td>
                <?php if ($canEdit): ?>
                <td class="py-2 px-4 text-center">
                    <form method="post" action="<?= base_url('tiktok/links/' . $link['id'] . '/toggle-active') ?>">
                        <?= csrf_field() ?>
                        <button type="submit" class="p-1 rounded text-on-surface-variant hover:bg-surface-variant hover:text-primary transition-colors" title="<?= $link['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>">
                            <span class="material-symbols-outlined text-[18px]"><?= $link['is_active'] ? 'pause_circle' : 'play_circle' ?></span>
                        </button>
                    </form>
                </td>
                <?php endif; ?>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
