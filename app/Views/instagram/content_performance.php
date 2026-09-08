<?php helper('format') ?>
<?php
/** @var array<string,mixed>|null $account */
/** @var list<array<string,mixed>> $rows */
/** @var array{posts:int,reach:int,engagement:int,eng_rate:float} $summary */

$sparkline = static function (array $points): string {
    $points = array_values(array_map('intval', $points));
    if (count($points) < 2) {
        return '<span class="text-on-surface-variant text-[11px]">—</span>';
    }
    $w = 90; $h = 24; $max = max($points); $min = min($points);
    $span = max(1, $max - $min);
    $step = $w / (count($points) - 1);
    $coords = [];
    foreach ($points as $i => $p) {
        $x = round($i * $step, 1);
        $y = round($h - (($p - $min) / $span) * $h, 1);
        $coords[] = "{$x},{$y}";
    }
    return '<svg width="' . $w . '" height="' . $h . '" viewBox="0 0 ' . $w . ' ' . $h . '" preserveAspectRatio="none" class="text-primary">'
        . '<polyline fill="none" stroke="currentColor" stroke-width="1.5" points="' . implode(' ', $coords) . '"/></svg>';
};

$sortLink = static function (string $key) use ($from, $to, $sort): string {
    $active = $sort === $key;
    $url = base_url('publish/performance?from=' . urlencode($from) . '&to=' . urlencode($to) . '&sort=' . $key);
    return '<a href="' . $url . '" class="' . ($active ? 'text-primary' : 'hover:text-primary') . ' inline-flex items-center gap-0.5">%s'
        . ($active ? '<span class="material-symbols-outlined text-[13px]">arrow_downward</span>' : '') . '</a>';
};
?>

<div class="flex flex-wrap items-center justify-between gap-3 mb-gutter">
    <div class="inline-flex rounded-lg border border-outline-variant overflow-hidden text-body-sm font-body-sm">
        <a href="<?= base_url('publish') ?>" class="px-4 py-2 text-on-surface-variant hover:bg-surface-container">Riwayat</a>
        <a href="<?= base_url('publish/calendar') ?>" class="px-4 py-2 border-l border-outline-variant text-on-surface-variant hover:bg-surface-container">Kalender</a>
        <a href="<?= base_url('publish/performance') ?>" class="px-4 py-2 border-l border-outline-variant bg-primary text-on-primary">Performa</a>
    </div>
    <a href="<?= base_url('publish/new') ?>" class="h-[36px] px-4 rounded bg-primary text-on-primary text-body-sm font-body-sm font-medium hover:bg-primary-container hover:text-on-primary-container transition-colors inline-flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px]">add</span>
        Compose
    </a>
</div>

<?php if (! $account): ?>
<div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-card-padding text-center text-on-surface-variant text-body-sm font-body-sm">
    Hubungkan akun Instagram dulu di <a href="<?= base_url('admin/ig-account') ?>" class="text-primary">IG Account</a> untuk melihat performa konten.
</div>
<?php else: ?>

<form method="get" action="<?= base_url('publish/performance') ?>" class="bg-surface-container-lowest border border-outline-variant rounded-lg p-3 mb-gutter flex flex-wrap items-end gap-3">
    <div class="flex flex-col gap-1">
        <label for="from" class="font-label-caps text-label-caps text-on-surface-variant">Dari</label>
        <input type="date" id="from" name="from" value="<?= esc($from, 'attr') ?>" class="bg-surface border border-outline-variant rounded px-3 py-2 text-body-sm font-body-sm text-on-surface">
    </div>
    <div class="flex flex-col gap-1">
        <label for="to" class="font-label-caps text-label-caps text-on-surface-variant">Sampai</label>
        <input type="date" id="to" name="to" value="<?= esc($to, 'attr') ?>" class="bg-surface border border-outline-variant rounded px-3 py-2 text-body-sm font-body-sm text-on-surface">
    </div>
    <input type="hidden" name="sort" value="<?= esc($sort, 'attr') ?>">
    <button type="submit" class="bg-primary text-on-primary font-body-md text-body-md px-4 py-2 rounded shadow hover:bg-primary-container hover:text-on-primary-container transition-all flex items-center gap-2">
        <span class="material-symbols-outlined text-sm">filter_alt</span> Terapkan
    </button>
</form>

<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-gutter">
    <div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-3">
        <div class="font-label-caps text-label-caps text-on-surface-variant">Post (rentang ini)</div>
        <div class="font-data-mono text-data-mono text-on-surface text-lg mt-1"><?= number_format($summary['posts'], 0, ',', '.') ?></div>
    </div>
    <div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-3">
        <div class="font-label-caps text-label-caps text-on-surface-variant">Total Reach</div>
        <div class="font-data-mono text-data-mono text-on-surface text-lg mt-1" title="<?= number_format($summary['reach'], 0, ',', '.') ?>"><?= format_compact_number($summary['reach']) ?></div>
    </div>
    <div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-3">
        <div class="font-label-caps text-label-caps text-on-surface-variant">Total Engagement</div>
        <div class="font-data-mono text-data-mono text-on-surface text-lg mt-1" title="<?= number_format($summary['engagement'], 0, ',', '.') ?>"><?= format_compact_number($summary['engagement']) ?></div>
    </div>
    <div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-3">
        <div class="font-label-caps text-label-caps text-on-surface-variant">Avg Engagement Rate</div>
        <div class="font-data-mono text-data-mono text-on-surface text-lg mt-1"><?= number_format($summary['eng_rate'], 2) ?>%</div>
    </div>
</div>

<div class="bg-surface-container-lowest border border-outline-variant rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface border-b border-outline-variant text-label-caps font-label-caps text-on-surface-variant uppercase tracking-wider">
            <tr>
                <th class="p-table-cell-padding"><?= sprintf($sortLink('recent'), 'Post') ?></th>
                <th class="p-table-cell-padding text-right"><?= sprintf($sortLink('reach'), 'Reach') ?></th>
                <th class="p-table-cell-padding text-right">Impr.</th>
                <th class="p-table-cell-padding text-right"><?= sprintf($sortLink('likes'), 'Likes') ?></th>
                <th class="p-table-cell-padding text-right"><?= sprintf($sortLink('comments'), 'Komentar') ?></th>
                <th class="p-table-cell-padding text-right"><?= sprintf($sortLink('saves'), 'Saves') ?></th>
                <th class="p-table-cell-padding text-right"><?= sprintf($sortLink('engagement'), 'Engagement') ?></th>
                <th class="p-table-cell-padding text-right"><?= sprintf($sortLink('eng_rate'), 'Eng. Rate') ?></th>
                <th class="p-table-cell-padding">Tren</th>
            </tr>
            </thead>
            <tbody class="text-body-sm font-body-sm text-on-surface divide-y divide-outline-variant">
            <?php if ($rows === []): ?>
            <tr><td colspan="9" class="p-table-cell-padding text-on-surface-variant">Belum ada data insight untuk rentang tanggal ini.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $r): ?>
            <tr class="hover:bg-surface-container-low transition-colors">
                <td class="p-table-cell-padding max-w-[280px]">
                    <div class="flex items-center gap-1">
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] bg-surface-container-highest text-on-surface-variant border border-outline-variant uppercase"><?= esc($r['media_type']) ?></span>
                        <?php if (! empty($r['permalink'])): ?>
                        <a href="<?= esc($r['permalink']) ?>" target="_blank" rel="noopener" class="text-primary"><span class="material-symbols-outlined text-[14px]">open_in_new</span></a>
                        <?php endif; ?>
                    </div>
                    <div class="text-on-surface-variant truncate mt-0.5" title="<?= esc($r['caption'] ?? '') ?>"><?= esc(mb_substr($r['caption'] ?? '', 0, 60)) ?><?= mb_strlen($r['caption'] ?? '') > 60 ? '…' : '' ?></div>
                    <div class="text-[10px] text-on-surface-variant font-data-mono mt-0.5"><?= esc(date('d M Y', strtotime((string) $r['posted_at']))) ?></div>
                </td>
                <td class="p-table-cell-padding text-right font-data-mono text-data-mono" title="<?= number_format((int) $r['reach'], 0, ',', '.') ?>"><?= format_compact_number((int) $r['reach']) ?></td>
                <td class="p-table-cell-padding text-right font-data-mono text-data-mono text-on-surface-variant"><?= format_compact_number((int) $r['impressions']) ?></td>
                <td class="p-table-cell-padding text-right font-data-mono text-data-mono"><?= format_compact_number((int) $r['likes']) ?></td>
                <td class="p-table-cell-padding text-right font-data-mono text-data-mono"><?= format_compact_number((int) $r['comments']) ?></td>
                <td class="p-table-cell-padding text-right font-data-mono text-data-mono"><?= format_compact_number((int) $r['saves']) ?></td>
                <td class="p-table-cell-padding text-right font-data-mono text-data-mono font-medium" title="<?= number_format((int) $r['engagement'], 0, ',', '.') ?>"><?= format_compact_number((int) $r['engagement']) ?></td>
                <td class="p-table-cell-padding text-right font-data-mono text-data-mono"><?= number_format((float) $r['eng_rate'], 2) ?>%</td>
                <td class="p-table-cell-padding"><?= $sparkline($r['spark'] ?? []) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<p class="text-body-sm font-body-sm text-on-surface-variant mt-3">
    Angka diakumulasi dari snapshot harian (<code class="font-data-mono">snapshot:ig-content</code>) dalam rentang tanggal terpilih — bukan panggilan live ke Instagram.
</p>
<?php endif; ?>
