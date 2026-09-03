<div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-card-padding mb-gutter flex items-center justify-between">
    <div class="flex items-center gap-3">
        <span class="material-symbols-outlined <?= $monitoringEnabled ? 'text-tertiary' : 'text-error' ?>"><?= $monitoringEnabled ? 'monitor_heart' : 'pause_circle' ?></span>
        <div>
            <div class="font-body-md text-body-md text-on-surface">Job Monitoring: <strong><?= $monitoringEnabled ? 'Aktif' : 'Nonaktif' ?></strong></div>
            <div class="text-body-sm font-body-sm text-on-surface-variant">
                <?= $monitoringEnabled
                    ? 'Snapshot/publish job berjalan sesuai jadwal.'
                    : 'Semua snapshot/publish job dilewati sampai diaktifkan lagi.' ?>
            </div>
        </div>
    </div>
    <form method="post" action="<?= base_url('admin/job-logs/toggle-monitoring') ?>">
        <?= csrf_field() ?>
        <button type="submit" class="<?= $monitoringEnabled ? 'bg-error text-on-error' : 'bg-primary text-on-primary' ?> font-body-md text-body-md px-4 py-2 rounded shadow hover:opacity-90 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-sm"><?= $monitoringEnabled ? 'pause_circle' : 'play_circle' ?></span>
            <?= $monitoringEnabled ? 'Nonaktifkan Job Monitoring' : 'Aktifkan Job Monitoring' ?>
        </button>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-gutter mb-gutter">
    <div class="bg-surface-container-lowest p-card-padding border border-outline-variant rounded-lg">
        <h3 class="text-label-caps font-label-caps text-on-surface-variant mb-2">24H Success Rate</h3>
        <?php if ($successRate24h === null): ?>
        <span class="text-headline-lg font-headline-lg text-on-surface-variant">-</span>
        <p class="text-body-sm font-body-sm text-on-surface-variant mt-1">Belum ada job dalam 24 jam terakhir.</p>
        <?php else: ?>
        <span class="text-headline-lg font-headline-lg text-on-surface"><?= esc($successRate24h) ?>%</span>
        <?php endif; ?>
    </div>
    <div class="bg-surface-container-lowest p-card-padding border border-outline-variant rounded-lg">
        <h3 class="text-label-caps font-label-caps text-on-surface-variant mb-2">Jobs Today</h3>
        <span class="text-headline-lg font-headline-lg text-on-surface"><?= esc($jobsToday) ?></span>
    </div>
    <div class="bg-surface-container-lowest p-card-padding border border-outline-variant rounded-lg">
        <h3 class="text-label-caps font-label-caps text-on-surface-variant mb-2">Failed Today</h3>
        <div class="flex items-center gap-2">
            <?php if ($failedToday > 0): ?>
            <span class="material-symbols-outlined text-error icon-fill">warning</span>
            <?php endif; ?>
            <span class="text-headline-lg font-headline-lg <?= $failedToday > 0 ? 'text-error' : 'text-on-surface' ?>"><?= esc($failedToday) ?></span>
        </div>
    </div>
</div>

<div class="bg-surface-container-lowest border border-outline-variant rounded-lg overflow-hidden mb-gutter">
    <div class="px-card-padding py-3 border-b border-outline-variant bg-surface-container-low">
        <h2 class="text-headline-md font-headline-md text-on-surface">Recent Job Runs</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
            <tr class="bg-surface-container-low text-label-caps font-label-caps text-on-surface-variant">
                <th class="p-table-cell-padding">Job Name</th>
                <th class="p-table-cell-padding">Status</th>
                <th class="p-table-cell-padding">Started At</th>
                <th class="p-table-cell-padding">Finished At</th>
                <th class="p-table-cell-padding">Message</th>
            </tr>
            </thead>
            <tbody class="text-body-sm font-body-sm divide-y divide-outline-variant">
            <?php if (empty($jobLogs)): ?>
            <tr><td colspan="5" class="p-table-cell-padding text-on-surface-variant">Belum ada job yang berjalan.</td></tr>
            <?php endif; ?>
            <?php foreach ($jobLogs as $log): ?>
            <?php
                $pillClass = match ($log['status']) {
                    'success' => 'bg-[#E3FCEF] text-[#006644]',
                    'partial' => 'bg-primary-fixed text-on-primary-fixed',
                    default   => 'bg-[#FFEBE6] text-[#BF2600]',
                };
            ?>
            <tr class="hover:bg-surface-container-low transition-colors h-[48px]">
                <td class="p-table-cell-padding font-data-mono text-data-mono text-on-surface"><?= esc($log['job_name']) ?></td>
                <td class="p-table-cell-padding">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium <?= $pillClass ?>"><?= esc(ucfirst($log['status'])) ?></span>
                </td>
                <td class="p-table-cell-padding text-on-surface-variant font-data-mono text-data-mono"><?= esc($log['started_at']) ?></td>
                <td class="p-table-cell-padding text-on-surface-variant font-data-mono text-data-mono"><?= esc($log['finished_at'] ?? '-') ?></td>
                <td class="p-table-cell-padding <?= $log['status'] === 'failed' ? 'text-error' : 'text-on-surface-variant' ?> truncate max-w-[300px]" title="<?= esc($log['error_message'] ?? '') ?>"><?= esc($log['error_message'] ?? '-') ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="bg-surface-container-lowest border border-outline-variant rounded-lg overflow-hidden">
    <div class="px-card-padding py-3 border-b border-outline-variant bg-surface-container-low">
        <h2 class="text-headline-md font-headline-md text-on-surface">Audit Log</h2>
    </div>
    <div class="p-card-padding space-y-4">
        <?php if (empty($auditLogs)): ?>
        <p class="text-body-sm font-body-sm text-on-surface-variant">Belum ada aktivitas.</p>
        <?php endif; ?>
        <?php foreach ($auditLogs as $log): ?>
        <div class="flex items-start gap-3">
            <div class="w-8 h-8 rounded bg-surface-container-high flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[16px] text-on-surface-variant">history_edu</span>
            </div>
            <div>
                <p class="text-body-sm font-body-sm text-on-surface">
                    <strong><?= esc($log['username'] ?? 'system') ?></strong>
                    <?= esc(str_replace('_', ' ', $log['action'])) ?>
                    <?php if ($log['entity_type']): ?>
                    <span class="font-data-mono bg-surface-container px-1 py-0.5 rounded text-[12px]"><?= esc($log['entity_type']) ?><?= $log['entity_id'] ? ' #' . esc($log['entity_id']) : '' ?></span>
                    <?php endif; ?>
                </p>
                <p class="text-label-caps font-label-caps text-on-surface-variant mt-1 font-data-mono"><?= esc($log['created_at']) ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
