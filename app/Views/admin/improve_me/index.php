<?php

/**
 * Improve Me (Jarvis Power). UI/UX ported from automedia's
 * app/Views/admin/improve_me/index.php — same prompt card, requests table,
 * status badges, and "Lihat Hasil" result modal — rebuilt in digimark's
 * Tailwind design system, with the Bootstrap modal swapped for a native
 * <dialog> (digimark ships no JS framework).
 */

$__statusBadge = static function (string $status): string {
    [$label, $cls] = match ($status) {
        'submitted'   => ['Terkirim', 'bg-surface-container-high text-on-surface-variant'],
        'in_progress' => ['Sedang Diproses', 'bg-primary-fixed text-on-primary-fixed'],
        'done'        => ['Selesai', 'bg-[#E3FCEF] text-[#006644]'],
        default       => ['Gagal', 'bg-[#FFEBE6] text-[#BF2600]'],
    };

    return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium ' . $cls . '">' . esc($label) . '</span>';
};

// Lightweight, safe formatting for the routine's GitHub comment (plain text
// with light markdown: **bold**, `code`, bare URLs, "---" dividers,
// blank-line paragraphs) — esc() first so nothing in the comment can inject
// HTML, then a few regexes turn the already-escaped tokens into real markup.
// No markdown library pulled in for this.
$__formatResult = static function (string $raw): string {
    $blocks = preg_split('/\n{2,}/', trim($raw)) ?: [];
    $html   = '';

    foreach ($blocks as $block) {
        $block = trim($block);

        if ($block === '') {
            continue;
        }

        if ($block === '---') {
            $html .= '<hr class="my-4 border-outline-variant">';
            continue;
        }

        $escaped = esc($block, 'html');
        $escaped = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $escaped);
        $escaped = preg_replace('/`([^`]+?)`/', '<code>$1</code>', $escaped);
        $escaped = preg_replace('/(https?:\/\/[^\s<]+)/', '<a href="$1" target="_blank" rel="noopener" class="text-primary underline">$1</a>', $escaped);

        $html .= '<p>' . nl2br($escaped, false) . '</p>';
    }

    return $html !== '' ? $html : '<p class="text-on-surface-variant mb-0">Belum ada hasil.</p>';
};
?>

<p class="text-body-sm font-body-sm text-on-surface-variant mb-gutter max-w-3xl">
    Tulis permintaan perbaikan/pengembangan untuk codebase Digimark. Prompt ini dikirim sebagai GitHub issue
    berlabel <code class="font-data-mono bg-surface-container px-1 py-0.5 rounded text-[12px]">improve-me</code> di repo,
    yang otomatis memicu AI (Claude Code) untuk mengerjakannya di branch baru dan membuka
    <strong>Pull Request</strong> untuk direview — AI tidak pernah push langsung ke
    <code class="font-data-mono bg-surface-container px-1 py-0.5 rounded text-[12px]">main</code>.
</p>

<div class="bg-surface-container-lowest border border-outline-variant rounded-lg overflow-hidden mb-gutter">
    <div class="px-card-padding py-3 border-b border-outline-variant bg-surface-container-low">
        <h2 class="text-headline-md font-headline-md text-on-surface">Kirim Prompt Baru</h2>
    </div>
    <div class="p-card-padding">
        <form method="post" action="<?= base_url('admin/improve-me/submit') ?>">
            <?= csrf_field() ?>
            <label for="improve-prompt" class="block text-label-caps font-label-caps text-on-surface-variant mb-1">Prompt</label>
            <textarea id="improve-prompt" name="prompt" rows="5" required
                class="w-full bg-surface border border-outline-variant rounded px-3 py-2 text-body-sm font-body-sm text-on-surface"
                placeholder="Contoh: Tambahkan filter tanggal di halaman TikTok Metrics."></textarea>
            <button type="submit"
                class="mt-3 bg-primary text-on-primary font-body-md text-body-md px-4 py-2 rounded shadow hover:bg-primary-container hover:text-on-primary-container transition-all flex items-center gap-2"
                onclick="return confirm('Kirim prompt ini ke AI? AI akan membuat perubahan kode nyata dan membuka Pull Request.')">
                <span class="material-symbols-outlined text-sm">smart_toy</span>
                Kirim ke AI
            </button>
        </form>
    </div>
</div>

<div class="bg-surface-container-lowest border border-outline-variant rounded-lg overflow-hidden">
    <div class="px-card-padding py-3 border-b border-outline-variant bg-surface-container-low">
        <h2 class="text-headline-md font-headline-md text-on-surface">Riwayat Permintaan</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
            <tr class="bg-surface-container-low text-label-caps font-label-caps text-on-surface-variant">
                <th class="p-table-cell-padding">Waktu</th>
                <th class="p-table-cell-padding">Prompt</th>
                <th class="p-table-cell-padding">Diminta Oleh</th>
                <th class="p-table-cell-padding">Status</th>
                <th class="p-table-cell-padding">GitHub Issue</th>
                <th class="p-table-cell-padding">Hasil</th>
            </tr>
            </thead>
            <tbody class="text-body-sm font-body-sm divide-y divide-outline-variant">
            <?php if ($requests === []): ?>
            <tr><td colspan="6" class="p-table-cell-padding text-on-surface-variant text-center">Belum ada permintaan.</td></tr>
            <?php endif; ?>
            <?php foreach ($requests as $req): ?>
            <tr class="hover:bg-surface-container-low transition-colors">
                <td class="p-table-cell-padding font-data-mono text-data-mono text-on-surface-variant text-nowrap"><?= esc($req['created_at']) ?></td>
                <td class="p-table-cell-padding text-on-surface max-w-[360px]">
                    <?= esc(mb_substr($req['prompt'], 0, 120)) ?><?= mb_strlen($req['prompt']) > 120 ? '…' : '' ?>
                </td>
                <td class="p-table-cell-padding text-on-surface-variant">
                    <?= $req['requested_by_name'] !== null ? esc($req['requested_by_name']) : '-' ?>
                </td>
                <td class="p-table-cell-padding" title="<?= esc($req['error_message'] ?? '') ?>"><?= $__statusBadge($req['status']) ?></td>
                <td class="p-table-cell-padding font-data-mono text-data-mono">
                    <?php if (! empty($req['github_issue_url'])): ?>
                        <a href="<?= esc($req['github_issue_url']) ?>" target="_blank" rel="noopener" class="text-primary hover:underline">#<?= (int) $req['github_issue_number'] ?></a>
                    <?php else: ?>
                        <span class="text-on-surface-variant">-</span>
                    <?php endif; ?>
                </td>
                <td class="p-table-cell-padding">
                    <?php if (! empty($req['result_pr_url']) || ! empty($req['result_comment'])): ?>
                        <button type="button"
                            class="inline-flex items-center gap-1 border border-outline-variant text-on-surface px-2 py-1 rounded text-[12px] hover:bg-surface-container-low transition-colors"
                            onclick="document.getElementById('hasil-modal-<?= (int) $req['id'] ?>').showModal()">
                            <span class="material-symbols-outlined text-[14px]">visibility</span>
                            Lihat Hasil
                        </button>
                    <?php else: ?>
                        <span class="text-on-surface-variant">-</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php foreach ($requests as $req): ?>
    <?php if (! empty($req['result_pr_url']) || ! empty($req['result_comment'])): ?>
    <dialog id="hasil-modal-<?= (int) $req['id'] ?>" class="improve-dialog">
        <div class="bg-surface-container-lowest text-on-surface rounded-lg overflow-hidden">
            <div class="flex items-start justify-between gap-4 px-card-padding py-3 border-b border-outline-variant bg-surface-container-low">
                <div>
                    <h3 class="text-headline-md font-headline-md text-on-surface mb-0.5">Hasil Eksekusi AI</h3>
                    <div class="text-label-caps font-label-caps text-on-surface-variant font-data-mono">
                        GitHub Issue
                        <a href="<?= esc($req['github_issue_url'] ?? '#') ?>" target="_blank" rel="noopener" class="text-primary hover:underline">#<?= (int) $req['github_issue_number'] ?></a>
                        &middot; <?= esc($req['created_at']) ?>
                    </div>
                </div>
                <button type="button" class="text-on-surface-variant hover:text-on-surface p-1 rounded hover:bg-surface-container" onclick="this.closest('dialog').close()" aria-label="Tutup">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            <div class="p-card-padding max-h-[70vh] overflow-y-auto">
                <div class="text-label-caps font-label-caps text-on-surface-variant mb-1">Prompt</div>
                <div class="whitespace-pre-line bg-surface-container rounded px-3 py-2 text-body-sm font-body-sm text-on-surface mb-4"><?= esc($req['prompt']) ?></div>

                <?php if (! empty($req['result_pr_url'])): ?>
                <a href="<?= esc($req['result_pr_url']) ?>" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 bg-primary text-on-primary text-body-sm font-body-sm px-4 py-2 rounded shadow hover:bg-primary-container hover:text-on-primary-container transition-all mb-4">
                    <span class="material-symbols-outlined text-[16px]">merge</span>
                    Lihat Pull Request &rarr;
                </a>
                <?php endif; ?>

                <div class="text-label-caps font-label-caps text-on-surface-variant mb-1">Hasil / Penjelasan AI</div>
                <div class="improve-result text-body-sm font-body-sm text-on-surface">
                    <?= $req['result_comment'] !== null ? $__formatResult($req['result_comment']) : '<p class="text-on-surface-variant mb-0">Belum ada penjelasan tertulis — lihat Pull Request di atas.</p>' ?>
                </div>
            </div>
        </div>
    </dialog>
    <?php endif; ?>
<?php endforeach; ?>

<style>
    dialog.improve-dialog {
        width: 92%;
        max-width: 44rem;
        padding: 0;
        border: none;
        border-radius: .5rem;
        background: transparent;
        color: inherit;
    }
    dialog.improve-dialog::backdrop {
        background: rgba(0, 0, 0, .5);
    }
    .improve-result p { margin-bottom: .85rem; line-height: 1.6; }
    .improve-result p:last-child { margin-bottom: 0; }
    .improve-result code {
        background: rgba(0, 0, 0, .06);
        padding: .1rem .35rem;
        border-radius: .25rem;
        font-size: .85em;
    }
</style>
