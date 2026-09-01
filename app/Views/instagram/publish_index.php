<div class="flex justify-end mb-gutter">
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
                <th class="p-table-cell-padding">Retry</th>
                <th class="p-table-cell-padding">Hasil / Error</th>
                <th class="p-table-cell-padding">Aksi</th>
            </tr>
            </thead>
            <tbody class="text-body-sm font-body-sm text-on-surface">
            <?php if (empty($items)): ?>
            <tr><td colspan="7" class="p-table-cell-padding text-on-surface-variant">Belum ada item di antrian publish.</td></tr>
            <?php endif; ?>
            <?php $maxRetries = \App\Models\PublishQueueModel::MAX_RETRIES; ?>
            <?php foreach ($items as $item): $retryCount = $item['retry_count'] ?? 0; ?>
            <?php
                $pillClass = match ($item['status']) {
                    'published' => 'bg-[#E3FCEF] text-[#006644]',
                    'processing', 'pending' => 'bg-primary-fixed text-on-primary-fixed',
                    default => 'bg-[#FFEBE6] text-[#BF2600]',
                };
            ?>
            <tr class="h-[48px] border-b border-outline-variant hover:bg-surface-container-low transition-colors">
                <td class="p-table-cell-padding"><?= esc($item['media_type']) ?></td>
                <td class="p-table-cell-padding text-on-surface-variant truncate max-w-[200px]"><?= esc(mb_substr($item['caption'] ?? '', 0, 40)) ?></td>
                <td class="p-table-cell-padding text-on-surface-variant font-data-mono text-data-mono"><?= esc($item['scheduled_at']) ?></td>
                <td class="p-table-cell-padding">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium <?= $pillClass ?>"><?= esc(ucfirst($item['status'])) ?></span>
                </td>
                <td class="p-table-cell-padding text-on-surface-variant font-data-mono text-data-mono"><?= $retryCount ?>/<?= $maxRetries ?></td>
                <td class="p-table-cell-padding text-on-surface-variant truncate max-w-[240px]" title="<?= esc($item['ig_media_id_result'] ?? $item['error_message'] ?? '') ?>">
                    <?= esc($item['ig_media_id_result'] ?? $item['error_message'] ?? '-') ?>
                </td>
                <td class="p-table-cell-padding">
                    <?php if ($item['status'] === 'failed' && $retryCount < $maxRetries): ?>
                    <form method="post" action="<?= base_url('publish/' . $item['id'] . '/retry') ?>">
                        <?= csrf_field() ?>
                        <button type="submit" class="h-[28px] px-3 rounded border border-outline-variant text-body-sm font-body-sm text-on-surface-variant hover:bg-surface-container transition-colors">Retry</button>
                    </form>
                    <?php elseif ($item['status'] === 'failed'): ?>
                    <span class="text-body-sm font-body-sm text-on-surface-variant">Batas retry tercapai</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
