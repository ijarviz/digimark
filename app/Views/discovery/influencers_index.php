<?php helper('format') ?>

<div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-card-padding mb-gutter">
    <form method="get" action="<?= base_url('discovery/influencers') ?>" class="flex flex-wrap items-end gap-3">
        <div class="flex flex-col gap-1">
            <label for="disc-q" class="font-label-caps text-label-caps text-on-surface-variant">Keyword / Niche / Hashtag</label>
            <input type="text" id="disc-q" name="q" value="<?= esc($keyword) ?>" placeholder="mis. skincare, otomotif, kuliner jakarta" required
                   class="bg-surface border border-outline-variant rounded px-3 py-2 text-body-sm font-body-sm text-on-surface w-72">
        </div>
        <div class="flex flex-col gap-1">
            <label for="disc-platform" class="font-label-caps text-label-caps text-on-surface-variant">Platform</label>
            <select id="disc-platform" name="platform" class="bg-surface border border-outline-variant rounded px-3 py-2 text-body-sm font-body-sm text-on-surface">
                <option value="all" <?= $platform === 'all' ? 'selected' : '' ?>>Instagram + TikTok</option>
                <option value="instagram" <?= $platform === 'instagram' ? 'selected' : '' ?>>Instagram saja</option>
                <option value="tiktok" <?= $platform === 'tiktok' ? 'selected' : '' ?>>TikTok saja</option>
            </select>
        </div>
        <button type="submit" class="bg-primary text-on-primary font-body-md text-body-md px-4 py-2 rounded shadow hover:bg-primary-container hover:text-on-primary-container transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-sm">person_search</span>
            Cari
        </button>
        <?php if ($keyword !== ''): ?>
        <a href="<?= base_url('discovery/influencers') ?>" class="text-body-sm font-body-sm text-on-surface-variant hover:text-primary px-2 py-2">Reset</a>
        <?php endif; ?>
    </form>
    <p class="text-body-sm font-body-sm text-on-surface-variant mt-3">
        Hasil pencarian <strong>akun</strong> (bukan video) berdasarkan keyword — maksimal 15 akun per platform per pencarian.
        Follower &amp; bio diambil pada saat pencarian, bukan data live. Bisa makan waktu sampai ~1 menit.
    </p>
</div>

<?php foreach ($errors as $err): ?>
<div class="mb-gutter px-4 py-3 rounded border border-error bg-error-container/40 text-on-error-container text-body-sm font-body-sm flex items-center gap-2">
    <span class="material-symbols-outlined text-[18px] text-error">error</span>
    <?= esc($err) ?>
</div>
<?php endforeach; ?>

<?php if ($keyword === ''): ?>
<div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-card-padding text-center text-on-surface-variant text-body-sm font-body-sm">
    Masukkan keyword untuk mulai mencari akun influencer.
</div>
<?php elseif ($results === [] && $errors === []): ?>
<div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-card-padding text-center text-on-surface-variant text-body-sm font-body-sm">
    Tidak ada akun ditemukan untuk &ldquo;<?= esc($keyword) ?>&rdquo;.
</div>
<?php else: ?>

<?php if ($results !== []): ?>
<div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-card-padding mb-gutter flex flex-wrap items-end gap-3">
    <div class="flex flex-col gap-1">
        <label for="disc-min-followers" class="font-label-caps text-label-caps text-on-surface-variant">Min. Followers</label>
        <input type="number" id="disc-min-followers" min="0" step="1000" placeholder="0"
               class="bg-surface border border-outline-variant rounded px-3 py-2 text-body-sm font-body-sm text-on-surface w-40">
    </div>
    <div class="flex flex-col gap-1">
        <label for="disc-platform-filter" class="font-label-caps text-label-caps text-on-surface-variant">Filter Platform</label>
        <select id="disc-platform-filter" class="bg-surface border border-outline-variant rounded px-3 py-2 text-body-sm font-body-sm text-on-surface">
            <option value="">Semua</option>
            <option value="instagram">Instagram</option>
            <option value="tiktok">TikTok</option>
        </select>
    </div>
    <span id="disc-count" class="text-body-sm font-body-sm text-on-surface-variant"></span>
</div>

<div class="bg-surface-container-lowest border border-outline-variant rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse" id="disc-table">
            <thead class="bg-surface-container-low border-b border-outline-variant">
            <tr>
                <th class="py-3 px-4 font-label-caps text-label-caps text-on-surface-variant">Akun</th>
                <th class="py-3 px-4 font-label-caps text-label-caps text-on-surface-variant">Platform</th>
                <th class="py-3 px-4 font-label-caps text-label-caps text-on-surface-variant text-right">Followers</th>
                <th class="py-3 px-4 font-label-caps text-label-caps text-on-surface-variant">Bio</th>
                <th class="py-3 px-4 font-label-caps text-label-caps text-on-surface-variant w-44">Aksi</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant">
            <?php foreach ($results as $r): $initial = strtoupper(substr($r['handle'], 0, 1)); ?>
            <tr class="hover:bg-surface-container-low transition-colors" data-followers="<?= (int) ($r['followers'] ?? 0) ?>" data-platform="<?= esc($r['platform']) ?>">
                <td class="py-2 px-4">
                    <div class="flex items-center gap-2">
                        <?php if (! empty($r['avatar_url'])): ?>
                        <img src="<?= esc($r['avatar_url']) ?>" alt="" class="w-8 h-8 rounded-full object-cover shrink-0 bg-surface-variant" referrerpolicy="no-referrer" onerror="this.style.display='none'">
                        <?php else: ?>
                        <div class="w-8 h-8 rounded-full bg-surface-variant text-on-surface-variant flex items-center justify-center text-[11px] font-bold shrink-0"><?= esc($initial) ?></div>
                        <?php endif; ?>
                        <div class="min-w-0">
                            <a href="<?= esc($r['profile_url']) ?>" target="_blank" rel="noopener" class="font-body-md text-body-md font-medium text-on-surface hover:text-primary flex items-center gap-1 truncate max-w-[200px]">
                                @<?= esc($r['handle']) ?>
                                <?php if (! empty($r['verified'])): ?>
                                <span class="material-symbols-outlined text-[13px] text-primary icon-fill" title="Verified">verified</span>
                                <?php endif; ?>
                            </a>
                            <div class="text-[11px] text-on-surface-variant truncate max-w-[200px]"><?= esc($r['name']) ?></div>
                        </div>
                    </div>
                </td>
                <td class="py-2 px-4">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-label-caps tracking-wide border <?= $r['platform'] === 'tiktok' ? 'bg-surface-container-highest text-on-surface-variant border-outline-variant' : 'bg-primary-fixed text-on-primary-fixed border-primary-fixed-dim' ?>">
                        <?= $r['platform'] === 'tiktok' ? 'TIKTOK' : 'INSTAGRAM' ?>
                    </span>
                </td>
                <td class="py-2 px-4 text-right font-data-mono text-data-mono text-on-surface" title="<?= isset($r['followers']) ? number_format($r['followers'], 0, ',', '.') : '' ?>">
                    <?= isset($r['followers']) ? format_compact_number($r['followers']) : '-' ?>
                </td>
                <td class="py-2 px-4 text-on-surface-variant text-body-sm font-body-sm truncate max-w-[280px]" title="<?= esc($r['bio'] ?? '') ?>">
                    <?= esc($r['bio'] ?: '-') ?>
                </td>
                <td class="py-2 px-4">
                    <?php if ($r['platform'] === 'tiktok' && $canSave): ?>
                        <?php if (! empty($r['already_tracked'])): ?>
                        <span class="text-[11px] text-tertiary flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">check_circle</span>Sudah ada
                        </span>
                        <?php else: ?>
                        <button type="button"
                            class="disc-save-btn inline-flex items-center gap-1 border border-outline-variant text-on-surface px-2 py-1 rounded text-[12px] hover:bg-surface-container-low transition-colors disabled:opacity-50"
                            data-platform="<?= esc($r['platform']) ?>"
                            data-handle="<?= esc($r['handle']) ?>"
                            data-profile-url="<?= esc($r['profile_url']) ?>"
                            data-followers="<?= (int) ($r['followers'] ?? 0) ?>">
                            <span class="material-symbols-outlined text-[14px]">bookmark_add</span>
                            Simpan ke tracking
                        </button>
                        <?php endif; ?>
                    <?php elseif ($r['platform'] === 'instagram'): ?>
                        <span class="text-[11px] text-on-surface-variant" title="Belum ada tabel tracking untuk Instagram">&mdash;</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php endif; ?>

<script>
window.__DISCOVERY_SAVE_URL__ = <?= json_encode(base_url('discovery/influencers/save')) ?>;
window.__CSRF_HEADER__ = <?= json_encode(csrf_header()) ?>;
window.__CSRF_TOKEN__ = <?= json_encode(csrf_hash()) ?>;
</script>
