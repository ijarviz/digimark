<div class="card">
    <h2>Job Run Log</h2>
    <table>
        <thead>
        <tr><th>Job</th><th>Status</th><th>Started</th><th>Finished</th><th>Error</th></tr>
        </thead>
        <tbody>
        <?php if (empty($jobLogs)): ?>
        <tr><td colspan="5" class="text-muted">Belum ada job yang berjalan.</td></tr>
        <?php endif; ?>
        <?php foreach ($jobLogs as $log): ?>
        <tr>
            <td><?= esc($log['job_name']) ?></td>
            <td>
                <span class="badge badge-<?= $log['status'] === 'success' ? 'success' : ($log['status'] === 'partial' ? 'warning' : 'danger') ?>">
                    <?= esc($log['status']) ?>
                </span>
            </td>
            <td><?= esc($log['started_at']) ?></td>
            <td><?= esc($log['finished_at'] ?? '-') ?></td>
            <td class="text-muted"><?= esc($log['error_message'] ?? '-') ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="card">
    <h2>Audit Log</h2>
    <table>
        <thead>
        <tr><th>User</th><th>Action</th><th>Entity</th><th>Waktu</th></tr>
        </thead>
        <tbody>
        <?php if (empty($auditLogs)): ?>
        <tr><td colspan="4" class="text-muted">Belum ada aktivitas.</td></tr>
        <?php endif; ?>
        <?php foreach ($auditLogs as $log): ?>
        <tr>
            <td><?= esc($log['username'] ?? 'system') ?></td>
            <td><?= esc($log['action']) ?></td>
            <td><?= esc(($log['entity_type'] ?? '') . ($log['entity_id'] ? ' #' . $log['entity_id'] : '')) ?></td>
            <td><?= esc($log['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
