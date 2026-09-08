<?php
/** @var string $month YYYY-MM */
/** @var array<string, list<array<string,mixed>>> $itemsByDay */

$firstTs   = strtotime($monthStart);
$daysInMon = (int) date('t', $firstTs);
// Monday-first grid: date('N') is 1 (Mon) .. 7 (Sun).
$leadBlank = (int) date('N', $firstTs) - 1;
$monthLabel = date('F Y', $firstTs);

$statusChip = static function (string $status): string {
    return match ($status) {
        'published'  => 'bg-[#E3FCEF] text-[#006644] border-[#9be7c4]',
        'pending'    => 'bg-primary-fixed text-on-primary-fixed border-primary-fixed-dim',
        'processing' => 'bg-[#FFF4E5] text-[#9a5b00] border-[#f0c990]',
        'cancelled'  => 'bg-surface-variant text-on-surface-variant border-outline-variant',
        default      => 'bg-[#FFEBE6] text-[#BF2600] border-[#f2b8ab]',
    };
};
$todayStr = date('Y-m-d');
?>

<div class="flex flex-wrap items-center justify-between gap-3 mb-gutter">
    <div class="inline-flex rounded-lg border border-outline-variant overflow-hidden text-body-sm font-body-sm">
        <a href="<?= base_url('publish') ?>" class="px-4 py-2 text-on-surface-variant hover:bg-surface-container">Riwayat</a>
        <a href="<?= base_url('publish/calendar') ?>" class="px-4 py-2 border-l border-outline-variant bg-primary text-on-primary">Kalender</a>
        <a href="<?= base_url('publish/performance') ?>" class="px-4 py-2 border-l border-outline-variant text-on-surface-variant hover:bg-surface-container">Performa</a>
    </div>
    <a href="<?= base_url('publish/new') ?>" class="h-[36px] px-4 rounded bg-primary text-on-primary text-body-sm font-body-sm font-medium hover:bg-primary-container hover:text-on-primary-container transition-colors inline-flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px]">add</span>
        Compose
    </a>
</div>

<div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-card-padding">
    <div class="flex items-center justify-between mb-4">
        <a href="<?= base_url('publish/calendar?month=' . $prevMonth) ?>" class="h-[32px] w-[32px] rounded border border-outline-variant inline-flex items-center justify-center text-on-surface-variant hover:bg-surface-container transition-colors">
            <span class="material-symbols-outlined text-[18px]">chevron_left</span>
        </a>
        <div class="text-headline-md font-headline-md text-on-surface"><?= esc($monthLabel) ?></div>
        <a href="<?= base_url('publish/calendar?month=' . $nextMonth) ?>" class="h-[32px] w-[32px] rounded border border-outline-variant inline-flex items-center justify-center text-on-surface-variant hover:bg-surface-container transition-colors">
            <span class="material-symbols-outlined text-[18px]">chevron_right</span>
        </a>
    </div>

    <div class="grid grid-cols-7 gap-1 text-label-caps font-label-caps text-on-surface-variant uppercase mb-1">
        <?php foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $d): ?>
        <div class="px-2 py-1"><?= $d ?></div>
        <?php endforeach; ?>
    </div>

    <div class="grid grid-cols-7 gap-1">
        <?php for ($b = 0; $b < $leadBlank; $b++): ?>
        <div class="min-h-[110px] rounded border border-transparent"></div>
        <?php endfor; ?>

        <?php for ($day = 1; $day <= $daysInMon; $day++): ?>
        <?php
            $dateStr = sprintf('%s-%02d', $month, $day);
            $dayItems = $itemsByDay[$dateStr] ?? [];
            $isToday = $dateStr === $todayStr;
        ?>
        <div class="min-h-[110px] rounded border <?= $isToday ? 'border-primary' : 'border-outline-variant' ?> bg-surface p-1.5 flex flex-col gap-1">
            <div class="text-[11px] font-data-mono <?= $isToday ? 'text-primary font-bold' : 'text-on-surface-variant' ?>"><?= $day ?></div>
            <?php foreach ($dayItems as $it): ?>
            <?php
                $anchor = $it['status'] === 'published' && $it['published_at'] ? $it['published_at'] : $it['scheduled_at'];
                $time   = date('H:i', strtotime((string) $anchor));
                $tip    = trim(ucfirst($it['status']) . ' · ' . $it['media_type'] . ' · ' . mb_substr((string) ($it['caption'] ?? ''), 0, 80));
                $chip   = '<span class="material-symbols-outlined text-[11px] align-middle">schedule</span> ' . esc($time) . ' ' . esc($it['media_type']);
            ?>
            <?php if ($it['status'] === 'pending'): ?>
            <a href="<?= base_url('publish/' . $it['id'] . '/edit') ?>" title="<?= esc($tip) ?>"
               class="block truncate text-[10px] leading-tight px-1 py-0.5 rounded border <?= $statusChip($it['status']) ?> hover:opacity-80"><?= $chip ?></a>
            <?php else: ?>
            <span title="<?= esc($tip) ?>"
                  class="block truncate text-[10px] leading-tight px-1 py-0.5 rounded border <?= $statusChip($it['status']) ?>"><?= $chip ?></span>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php endfor; ?>
    </div>

    <div class="flex flex-wrap gap-3 mt-4 text-[11px] text-on-surface-variant">
        <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded border bg-primary-fixed border-primary-fixed-dim"></span> Pending</span>
        <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded border bg-[#FFF4E5] border-[#f0c990]"></span> Processing</span>
        <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded border bg-[#E3FCEF] border-[#9be7c4]"></span> Published</span>
        <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded border bg-[#FFEBE6] border-[#f2b8ab]"></span> Failed</span>
        <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded border bg-surface-variant border-outline-variant"></span> Cancelled</span>
    </div>
</div>
