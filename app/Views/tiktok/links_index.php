<?php helper('format') ?>
<div class="flex justify-between items-center mb-gutter">
    <div class="bg-surface-container-lowest border border-outline-variant rounded p-3 flex items-center gap-2">
        <span class="material-symbols-outlined text-outline text-sm">link</span>
        <span class="text-body-sm font-body-sm text-on-surface-variant">Showing <strong class="text-on-surface"><?= count($links) ?></strong> tracked entities</span>
    </div>
    <?php if ($canEdit): ?>
    <div class="flex items-center gap-3">
        <span id="refresh-all-status" class="text-body-sm font-body-sm text-on-surface-variant"></span>
        <button type="button" id="refresh-all-btn" class="bg-surface-container-lowest border border-outline-variant text-on-surface font-body-md text-body-md px-4 py-2 rounded hover:bg-surface-container-low transition-all flex items-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed" title="Refresh views & engagement untuk semua link, satu per satu">
            <span id="refresh-all-icon" class="material-symbols-outlined text-sm">refresh</span>
            <span id="refresh-all-label">Refresh All</span>
        </button>
        <a href="<?= base_url('tiktok/links/new') ?>" class="bg-primary text-on-primary font-body-md text-body-md px-4 py-2 rounded shadow hover:bg-primary-container hover:text-on-primary-container transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-sm">add_link</span>
            Add TikTok Link
        </a>
    </div>
    <?php endif; ?>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-gutter">
    <div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-3">
        <div class="font-label-caps text-label-caps text-on-surface-variant">Total Views</div>
        <div class="font-data-mono text-data-mono text-on-surface text-lg mt-1" title="<?= number_format($summary['total_views'], 0, ',', '.') ?>"><?= format_compact_number($summary['total_views']) ?></div>
    </div>
    <div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-3">
        <div class="font-label-caps text-label-caps text-on-surface-variant">Total Engagement</div>
        <div class="font-data-mono text-data-mono text-on-surface text-lg mt-1" title="<?= number_format($summary['total_engagement'], 0, ',', '.') ?>"><?= format_compact_number($summary['total_engagement']) ?></div>
        <div class="flex flex-wrap gap-x-3 gap-y-0.5 mt-1 text-on-surface-variant">
            <div class="flex items-center gap-1" title="Likes: <?= number_format($summary['total_likes'], 0, ',', '.') ?>">
                <span class="material-symbols-outlined text-[12px]">favorite</span>
                <span class="font-data-mono text-data-mono text-[11px]"><?= format_compact_number($summary['total_likes']) ?></span>
            </div>
            <div class="flex items-center gap-1" title="Comments: <?= number_format($summary['total_comments'], 0, ',', '.') ?>">
                <span class="material-symbols-outlined text-[12px]">chat_bubble</span>
                <span class="font-data-mono text-data-mono text-[11px]"><?= format_compact_number($summary['total_comments']) ?></span>
            </div>
            <div class="flex items-center gap-1" title="Shares: <?= number_format($summary['total_shares'], 0, ',', '.') ?>">
                <span class="material-symbols-outlined text-[12px]">share</span>
                <span class="font-data-mono text-data-mono text-[11px]"><?= format_compact_number($summary['total_shares']) ?></span>
            </div>
            <div class="flex items-center gap-1" title="Saves: <?= number_format($summary['total_saves'], 0, ',', '.') ?>">
                <span class="material-symbols-outlined text-[12px]">bookmark</span>
                <span class="font-data-mono text-data-mono text-[11px]"><?= format_compact_number($summary['total_saves']) ?></span>
            </div>
        </div>
    </div>
    <div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-3">
        <div class="font-label-caps text-label-caps text-on-surface-variant">Average Views</div>
        <div class="font-data-mono text-data-mono text-on-surface text-lg mt-1" title="<?= number_format($summary['avg_views'], 0, ',', '.') ?>"><?= format_compact_number((int) round($summary['avg_views'])) ?></div>
    </div>
    <div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-3">
        <div class="font-label-caps text-label-caps text-on-surface-variant">Engagement Rate</div>
        <div class="font-data-mono text-data-mono text-on-surface text-lg mt-1"><?= number_format($summary['engagement_rate'], 2) ?>%</div>
    </div>
    <div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-3">
        <div class="font-label-caps text-label-caps text-on-surface-variant">Share Rate</div>
        <div class="font-data-mono text-data-mono text-on-surface text-lg mt-1"><?= number_format($summary['share_rate'], 2) ?>%</div>
    </div>
    <div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-3">
        <div class="font-label-caps text-label-caps text-on-surface-variant">Save Rate</div>
        <div class="font-data-mono text-data-mono text-on-surface text-lg mt-1"><?= number_format($summary['save_rate'], 2) ?>%</div>
    </div>
    <div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-3">
        <div class="font-label-caps text-label-caps text-on-surface-variant">Total Budget + Voucher (KOL)</div>
        <div class="font-data-mono text-data-mono text-on-surface text-lg mt-1">Rp <?= number_format($summary['total_budget'], 0, ',', '.') ?></div>
    </div>
    <div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-3">
        <div class="font-label-caps text-label-caps text-on-surface-variant">Total Setelah Pajak 2,5%</div>
        <div class="font-data-mono text-data-mono text-on-surface text-lg mt-1">Rp <?= number_format($summary['total_budget_after_tax'], 0, ',', '.') ?></div>
    </div>
</div>

<form method="get" action="<?= base_url('tiktok/links') ?>" class="bg-surface-container-lowest border border-outline-variant rounded-lg p-3 mb-gutter flex flex-wrap items-end gap-3">
    <div class="flex flex-col gap-1">
        <label for="filter-q" class="font-label-caps text-label-caps text-on-surface-variant">Search campaign / creator</label>
        <input type="text" id="filter-q" name="q" value="<?= esc($filters['q']) ?>" placeholder="Nama campaign atau @creator..." class="bg-surface border border-outline-variant rounded px-3 py-2 text-body-sm font-body-sm text-on-surface w-64">
    </div>
    <div class="flex flex-col gap-1">
        <label for="filter-date-from" class="font-label-caps text-label-caps text-on-surface-variant">Upload date dari</label>
        <input type="date" id="filter-date-from" name="date_from" value="<?= esc($filters['date_from']) ?>" class="bg-surface border border-outline-variant rounded px-3 py-2 text-body-sm font-body-sm text-on-surface">
    </div>
    <div class="flex flex-col gap-1">
        <label for="filter-date-to" class="font-label-caps text-label-caps text-on-surface-variant">Upload date sampai</label>
        <input type="date" id="filter-date-to" name="date_to" value="<?= esc($filters['date_to']) ?>" class="bg-surface border border-outline-variant rounded px-3 py-2 text-body-sm font-body-sm text-on-surface">
    </div>
    <div class="flex flex-col gap-1">
        <label for="filter-segment" class="font-label-caps text-label-caps text-on-surface-variant">Segment</label>
        <select id="filter-segment" name="segment" class="bg-surface border border-outline-variant rounded px-3 py-2 text-body-sm font-body-sm text-on-surface">
            <option value="">Semua Segment</option>
            <?php foreach ($segments as $segment): ?>
            <option value="<?= esc($segment) ?>" <?= $filters['segment'] === $segment ? 'selected' : '' ?>><?= esc(\App\Models\TiktokLinkModel::SEGMENT_LABELS[$segment] ?? $segment) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="bg-primary text-on-primary font-body-md text-body-md px-4 py-2 rounded shadow hover:bg-primary-container hover:text-on-primary-container transition-all flex items-center gap-2">
        <span class="material-symbols-outlined text-sm">filter_alt</span>
        Filter
    </button>
    <?php if ($filters['q'] !== '' || $filters['date_from'] !== '' || $filters['date_to'] !== '' || $filters['segment'] !== ''): ?>
    <a href="<?= base_url('tiktok/links') ?>" class="text-body-sm font-body-sm text-on-surface-variant hover:text-primary px-2 py-2">Reset</a>
    <?php endif; ?>
</form>

<div class="bg-surface-container-lowest border border-outline-variant rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface-container-low border-b border-outline-variant">
            <tr>
                <th class="py-3 px-4 font-label-caps text-label-caps text-on-surface-variant w-12 text-center">Status</th>
                <th class="py-3 px-4 font-label-caps text-label-caps text-on-surface-variant">Creator</th>
                <th class="py-3 px-4 font-label-caps text-label-caps text-on-surface-variant">Catatan / Segment</th>
                <th class="py-3 px-4 font-label-caps text-label-caps text-on-surface-variant">Budget + Voucher</th>
                <th class="py-3 px-4 font-label-caps text-label-caps text-on-surface-variant">Setelah Pajak 2,5%</th>
                <th class="py-3 px-4 font-label-caps text-label-caps text-on-surface-variant">Source</th>
                <th class="py-3 px-4 font-label-caps text-label-caps text-on-surface-variant">Upload Date</th>
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
            <tr><td colspan="11" class="py-4 px-4 text-on-surface-variant text-body-sm font-body-sm">Belum ada link TikTok.</td></tr>
            <?php endif; ?>
            <?php foreach ($links as $link): $insight = $link['latest_insight']; $initial = strtoupper(substr(ltrim($link['creator_handle'] ?? $link['url'], '@'), 0, 1)); ?>
            <tr class="hover:bg-surface-container-low transition-colors group h-12" data-link-id="<?= $link['id'] ?>">
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
                            <span id="spinner-<?= $link['id'] ?>" class="items-center gap-1 text-primary mt-0.5" style="display:none" title="Sedang di-refresh...">
                                <span class="material-symbols-outlined text-[12px] animate-spin">progress_activity</span>
                                <span class="text-[10px] font-body-sm">Refreshing...</span>
                            </span>
                        </div>
                    </div>
                </td>
                <td class="py-2 px-4">
                    <div class="font-body-sm text-body-sm text-on-surface-variant truncate max-w-[160px]" title="<?= esc($link['affiliate_note'] ?? '') ?>"><?= esc($link['affiliate_note'] ?: '-') ?></div>
                    <div class="text-[10px] text-on-surface-variant mt-0.5"><?= esc($link['segment'] ? (\App\Models\TiktokLinkModel::SEGMENT_LABELS[$link['segment']] ?? $link['segment']) : '') ?><?= $link['segment'] && $link['gender'] ? ' &middot; ' : '' ?><?= esc($link['gender'] ? ucfirst($link['gender']) : '') ?></div>
                </td>
                <td class="py-2 px-4 font-data-mono text-data-mono text-on-surface-variant">
                    <?= $link['budget'] !== null ? 'Rp ' . number_format((float) $link['budget'], 0, ',', '.') : '-' ?>
                </td>
                <td class="py-2 px-4 font-data-mono text-data-mono text-on-surface-variant">
                    <?= $link['budget'] !== null ? 'Rp ' . number_format((float) $link['budget'] * (1 - 0.025), 0, ',', '.') : '-' ?>
                </td>
                <td class="py-2 px-4">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-label-caps tracking-wide border <?= $link['data_source'] === 'oauth' ? 'bg-primary-fixed text-on-primary-fixed border-primary-fixed-dim' : 'bg-surface-container-highest text-on-surface-variant border-outline-variant' ?>">
                        <?= esc(strtoupper($link['data_source'])) ?>
                    </span>
                </td>
                <td class="py-2 px-4 font-data-mono text-data-mono text-on-surface-variant">
                    <span id="posted-<?= $link['id'] ?>"><?= $link['video_posted_at'] ? esc(date('d M Y', strtotime($link['video_posted_at']))) : '-' ?></span>
                </td>
                <td class="py-2 px-4 text-right">
                    <div id="views-<?= $link['id'] ?>" class="font-data-mono text-data-mono text-on-surface" title="<?= isset($insight['views']) ? number_format((int) $insight['views'], 0, ',', '.') : '' ?>"><?= format_compact_number($insight['views'] ?? null) ?></div>
                </td>
                <td class="py-2 px-4 text-right">
                    <div class="flex justify-end gap-3 text-on-surface-variant">
                        <div id="likes-wrap-<?= $link['id'] ?>" class="flex items-center gap-1" title="Likes: <?= isset($insight['likes']) ? number_format((int) $insight['likes'], 0, ',', '.') : '--' ?>">
                            <span class="material-symbols-outlined text-[14px]">favorite</span>
                            <span id="likes-<?= $link['id'] ?>" class="font-data-mono text-data-mono"><?= format_compact_number($insight['likes'] ?? null) ?></span>
                        </div>
                        <div id="comments-wrap-<?= $link['id'] ?>" class="flex items-center gap-1" title="Comments: <?= isset($insight['comments']) ? number_format((int) $insight['comments'], 0, ',', '.') : '--' ?>">
                            <span class="material-symbols-outlined text-[14px]">chat_bubble</span>
                            <span id="comments-<?= $link['id'] ?>" class="font-data-mono text-data-mono"><?= format_compact_number($insight['comments'] ?? null) ?></span>
                        </div>
                        <div id="shares-wrap-<?= $link['id'] ?>" class="flex items-center gap-1" title="Shares: <?= isset($insight['shares']) ? number_format((int) $insight['shares'], 0, ',', '.') : '--' ?>">
                            <span class="material-symbols-outlined text-[14px]">share</span>
                            <span id="shares-<?= $link['id'] ?>" class="font-data-mono text-data-mono"><?= format_compact_number($insight['shares'] ?? null) ?></span>
                        </div>
                        <div id="saves-wrap-<?= $link['id'] ?>" class="flex items-center gap-1" title="Saves: <?= isset($insight['saves']) ? number_format((int) $insight['saves'], 0, ',', '.') : '--' ?>">
                            <span class="material-symbols-outlined text-[14px]">bookmark</span>
                            <span id="saves-<?= $link['id'] ?>" class="font-data-mono text-data-mono"><?= format_compact_number($insight['saves'] ?? null) ?></span>
                        </div>
                    </div>
                </td>
                <td class="py-2 px-4">
                    <div id="snapshot-<?= $link['id'] ?>" class="font-data-mono text-data-mono text-on-surface-variant"><?= esc($insight['snapshot_date'] ?? '-') ?></div>
                    <div class="text-[10px] text-on-surface-variant">Sync: <span id="synced-<?= $link['id'] ?>"><?= esc($link['last_synced_at'] ?? '-') ?></span></div>
                </td>
                <?php if ($canEdit): ?>
                <td class="py-2 px-4 text-center">
                    <div class="flex items-center justify-center gap-1">
                        <form method="post" action="<?= base_url('tiktok/links/' . $link['id'] . '/delete') ?>" onsubmit="return confirm('Hapus link ini beserta seluruh riwayat metrics-nya? Tindakan ini tidak bisa dibatalkan.');">
                            <?= csrf_field() ?>
                            <button type="submit" class="p-1 rounded text-on-surface-variant hover:bg-error-container hover:text-on-error-container transition-colors" title="Hapus link">
                                <span class="material-symbols-outlined text-[18px]">delete</span>
                            </button>
                        </form>
                    </div>
                </td>
                <?php endif; ?>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
window.__TIKTOK_LINKS_BASE_URL__ = <?= json_encode(base_url('tiktok/links')) ?>;
</script>
