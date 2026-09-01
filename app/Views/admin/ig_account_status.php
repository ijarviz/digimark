<div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-card-padding mb-gutter">
    <?php if ($account): ?>
    <div class="flex items-center gap-4">
        <div class="w-10 h-10 rounded-full bg-[#E1306C]/10 flex items-center justify-center text-[#E1306C]">
            <span class="material-symbols-outlined text-[24px]">photo_camera</span>
        </div>
        <div class="flex-1">
            <p class="text-body-md font-body-md font-semibold text-on-surface">@<?= esc($account['ig_username']) ?></p>
            <p class="text-body-sm font-body-sm text-on-surface-variant">IG Business ID: <span class="font-data-mono text-data-mono"><?= esc($account['ig_business_id']) ?></span></p>
            <p class="text-body-sm font-body-sm text-on-surface-variant">Terhubung sejak: <?= esc($account['connected_at']) ?></p>
            <?php $expired = strtotime($account['token_expires_at']) < time(); ?>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium mt-2 <?= $expired ? 'bg-[#FFEBE6] text-[#BF2600]' : 'bg-[#E3FCEF] text-[#006644]' ?>">
                Token expires: <?= esc($account['token_expires_at']) ?>
            </span>
        </div>
        <a href="<?= base_url('admin/ig-account/connect') ?>" class="h-[36px] px-4 rounded bg-primary text-on-primary text-body-sm font-body-sm font-medium hover:bg-primary-container hover:text-on-primary-container transition-colors flex items-center">Reconnect</a>
    </div>
    <?php else: ?>
    <div class="flex items-center justify-between">
        <p class="text-body-sm font-body-sm text-on-surface-variant">Belum ada akun Instagram yang terhubung.</p>
        <a href="<?= base_url('admin/ig-account/connect') ?>" class="h-[36px] px-4 rounded bg-primary text-on-primary text-body-sm font-body-sm font-medium hover:bg-primary-container hover:text-on-primary-container transition-colors flex items-center">Connect Instagram</a>
    </div>
    <?php endif; ?>
</div>

<?php if ($account): ?>
<div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-card-padding">
    <h3 class="text-headline-md font-headline-md text-on-surface mb-3">Ads Account</h3>
    <?php if (! empty($account['ad_account_id'])): ?>
    <div class="flex items-center justify-between">
        <div>
            <p class="text-body-sm font-body-sm text-on-surface">Ad Account: <span class="font-data-mono text-data-mono"><?= esc($account['ad_account_id']) ?></span></p>
            <?php if (! empty($account['business_manager_id'])): ?>
            <p class="text-body-sm font-body-sm text-on-surface-variant">Business Manager ID: <span class="font-data-mono text-data-mono"><?= esc($account['business_manager_id']) ?></span></p>
            <?php endif; ?>
            <p class="text-body-sm font-body-sm text-on-surface-variant">Terhubung sejak: <?= esc($account['ads_connected_at'] ?? '-') ?></p>
        </div>
        <a href="<?= base_url('admin/ads-account/select') ?>" class="h-[36px] px-4 rounded border border-outline-variant text-body-sm font-body-sm text-on-surface-variant hover:bg-surface-container transition-colors flex items-center">Ganti Ad Account</a>
    </div>
    <?php else: ?>
    <p class="text-body-sm font-body-sm text-on-surface-variant mb-3">Belum ada ad account yang dipilih. Pastikan izin <span class="font-data-mono bg-surface-container px-1 py-0.5 rounded">ads_read</span> sudah diberikan (reconnect di atas kalau perlu), lalu pilih ad account.</p>
    <a href="<?= base_url('admin/ads-account/select') ?>" class="h-[36px] px-4 rounded bg-primary text-on-primary text-body-sm font-body-sm font-medium hover:bg-primary-container hover:text-on-primary-container transition-colors inline-flex items-center">Pilih Ad Account</a>
    <?php endif; ?>
</div>
<?php endif; ?>
