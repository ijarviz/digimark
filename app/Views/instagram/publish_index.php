<div class="app-topbar" style="margin-bottom: var(--space-4)">
    <div></div>
    <a href="<?= base_url('publish/new') ?>" class="btn btn-primary">+ Compose</a>
</div>

<div class="card">
    <table>
        <thead>
        <tr>
            <th>Tipe</th>
            <th>Caption</th>
            <th>Jadwal</th>
            <th>Status</th>
            <th>Retry</th>
            <th>Hasil / Error</th>
            <th>Aksi</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($items)): ?>
        <tr><td colspan="7" class="text-muted">Belum ada item di antrian publish.</td></tr>
        <?php endif; ?>
        <?php $maxRetries = \App\Models\PublishQueueModel::MAX_RETRIES; ?>
        <?php foreach ($items as $item): $retryCount = $item['retry_count'] ?? 0; ?>
        <tr>
            <td><?= esc($item['media_type']) ?></td>
            <td><?= esc(mb_substr($item['caption'] ?? '', 0, 40)) ?></td>
            <td><?= esc($item['scheduled_at']) ?></td>
            <td>
                <?php
                $badgeClass = match ($item['status']) {
                    'published' => 'badge-success',
                    'processing', 'pending' => 'badge-warning',
                    default => 'badge-danger',
                };
                ?>
                <span class="badge <?= $badgeClass ?>"><?= esc($item['status']) ?></span>
            </td>
            <td class="text-muted"><?= $retryCount ?>/<?= $maxRetries ?></td>
            <td class="text-muted">
                <?= esc($item['ig_media_id_result'] ?? $item['error_message'] ?? '-') ?>
            </td>
            <td>
                <?php if ($item['status'] === 'failed' && $retryCount < $maxRetries): ?>
                <form method="post" action="<?= base_url('publish/' . $item['id'] . '/retry') ?>" style="display:inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn">Retry</button>
                </form>
                <?php elseif ($item['status'] === 'failed'): ?>
                <span class="text-muted">Batas retry tercapai</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
