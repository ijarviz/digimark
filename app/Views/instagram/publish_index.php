<?php helper('format') ?>

<?php $tab = 'history'; ?>
<div class="flex flex-wrap items-center justify-between gap-3 mb-gutter">
    <div class="inline-flex rounded-lg border border-outline-variant overflow-hidden text-body-sm font-body-sm">
        <a href="<?= base_url('publish') ?>" class="px-4 py-2 <?= $tab === 'history' ? 'bg-primary text-on-primary' : 'text-on-surface-variant hover:bg-surface-container' ?>">Riwayat</a>
        <a href="<?= base_url('publish/calendar') ?>" class="px-4 py-2 border-l border-outline-variant text-on-surface-variant hover:bg-surface-container">Kalender</a>
        <a href="<?= base_url('publish/performance') ?>" class="px-4 py-2 border-l border-outline-variant text-on-surface-variant hover:bg-surface-container">Performa</a>
    </div>
    <a href="<?= base_url('publish/new') ?>" class="h-[36px] px-4 rounded bg-primary text-on-primary text-body-sm font-body-sm font-medium hover:bg-primary-container hover:text-on-primary-container transition-colors inline-flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px]">add</span>
        Compose
    </a>
</div>

<div class="bg-surface-container-lowest border border-outline-variant rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface border-b border-outline-variant text-label-caps font-label-caps text-on-surface-variant uppercase tracking-wider">
            <tr>
                <th class="p-table-cell-padding">Tipe</th>
                <th class="p-table-cell-padding">Caption</th>
                <th class="p-table-cell-padding">Jadwal</th>
                <th class="p-table-cell-padding">Status</th>
                <th class="p-table-cell-padding text-right">Likes</th>
                <th class="p-table-cell-padding text-right">Komentar</th>
                <th class="p-table-cell-padding text-right">Reach</th>
                <th class="p-table-cell-padding text-right">Saves</th>
                <th class="p-table-cell-padding">Retry</th>
                <th class="p-table-cell-padding">Aksi</th>
            </tr>
            </thead>
            <tbody class="text-body-sm font-body-sm text-on-surface divide-y divide-outline-variant">
            <?php if (empty($items)): ?>
            <tr><td colspan="10" class="p-table-cell-padding text-on-surface-variant">Belum ada item di antrian publish.</td></tr>
            <?php endif; ?>
            <?php $maxRetries = \App\Models\PublishQueueModel::MAX_RETRIES; ?>
            <?php foreach ($items as $item): $retryCount = $item['retry_count'] ?? 0; ?>
            <?php
                $pillClass = match ($item['status']) {
                    'published'             => 'bg-[#E3FCEF] text-[#006644]',
                    'processing', 'pending' => 'bg-primary-fixed text-on-primary-fixed',
                    'cancelled'             => 'bg-surface-variant text-on-surface-variant',
                    default                => 'bg-[#FFEBE6] text-[#BF2600]',
                };
                $isPublished = $item['status'] === 'published';
                $hasMetrics  = $isPublished && $item['m_snapshot_date'] !== null;
                $metric = static fn ($v) => $v === null ? '—' : format_compact_number((int) $v);
            ?>
            <tr class="h-[48px] hover:bg-surface-container-low transition-colors">
                <td class="p-table-cell-padding"><?= esc($item['media_type']) ?></td>
                <td class="p-table-cell-padding text-on-surface-variant truncate max-w-[200px]" title="<?= esc($item['caption'] ?? '') ?>"><?= esc(mb_substr($item['caption'] ?? '', 0, 40)) ?><?= mb_strlen($item['caption'] ?? '') > 40 ? '…' : '' ?></td>
                <td class="p-table-cell-padding text-on-surface-variant font-data-mono text-data-mono"><?= esc($item['scheduled_at']) ?></td>
                <td class="p-table-cell-padding">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium <?= $pillClass ?>"><?= esc(ucfirst($item['status'])) ?></span>
                </td>
                <td class="p-table-cell-padding text-right font-data-mono text-data-mono <?= $hasMetrics ? 'text-on-surface' : 'text-on-surface-variant' ?>" title="<?= $hasMetrics ? number_format((int) $item['m_likes'], 0, ',', '.') : '' ?>"><?= $hasMetrics ? $metric($item['m_likes']) : '—' ?></td>
                <td class="p-table-cell-padding text-right font-data-mono text-data-mono <?= $hasMetrics ? 'text-on-surface' : 'text-on-surface-variant' ?>"><?= $hasMetrics ? $metric($item['m_comments']) : '—' ?></td>
                <td class="p-table-cell-padding text-right font-data-mono text-data-mono <?= $hasMetrics ? 'text-on-surface' : 'text-on-surface-variant' ?>"><?= $hasMetrics ? $metric($item['m_reach']) : '—' ?></td>
                <td class="p-table-cell-padding text-right font-data-mono text-data-mono <?= $hasMetrics ? 'text-on-surface' : 'text-on-surface-variant' ?>"><?= $hasMetrics ? $metric($item['m_saves']) : '—' ?></td>
                <td class="p-table-cell-padding text-on-surface-variant font-data-mono text-data-mono"><?= $retryCount ?>/<?= $maxRetries ?></td>
                <td class="p-table-cell-padding">
                    <div class="flex items-center gap-2">
                        <?php if ($item['status'] === 'pending'): ?>
                        <a href="<?= base_url('publish/' . $item['id'] . '/edit') ?>" class="h-[28px] px-3 rounded border border-outline-variant text-body-sm font-body-sm text-on-surface-variant hover:bg-surface-container transition-colors inline-flex items-center">Edit</a>
                        <form method="post" action="<?= base_url('publish/' . $item['id'] . '/cancel') ?>" onsubmit="return confirm('Batalkan item antrian ini?');">
                            <?= csrf_field() ?>
                            <button type="submit" class="h-[28px] px-3 rounded border border-outline-variant text-body-sm font-body-sm text-on-surface-variant hover:bg-error-container hover:text-on-error-container transition-colors">Batalkan</button>
                        </form>
                        <?php elseif ($item['status'] === 'failed' && $retryCount < $maxRetries): ?>
                        <form method="post" action="<?= base_url('publish/' . $item['id'] . '/retry') ?>">
                            <?= csrf_field() ?>
                            <button type="submit" class="h-[28px] px-3 rounded border border-outline-variant text-body-sm font-body-sm text-on-surface-variant hover:bg-surface-container transition-colors">Retry</button>
                        </form>
                        <?php elseif ($item['status'] === 'failed'): ?>
                        <span class="text-body-sm font-body-sm text-on-surface-variant" title="<?= esc($item['error_message'] ?? '') ?>">Batas retry tercapai</span>
                        <?php elseif ($isPublished && ! empty($item['m_permalink'])): ?>
                        <a href="<?= esc($item['m_permalink']) ?>" target="_blank" rel="noopener" class="h-[28px] px-3 rounded border border-outline-variant text-body-sm font-body-sm text-primary hover:bg-surface-container transition-colors inline-flex items-center gap-1">
                            Lihat <span class="material-symbols-outlined text-[14px]">open_in_new</span>
                        </a>
                        <?php else: ?>
                        <span class="text-on-surface-variant">—</span>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
