<div class="card">
    <?php if ($account): ?>
        <p><strong>Akun terhubung:</strong> @<?= esc($account['ig_username']) ?></p>
        <p class="text-muted">IG Business ID: <?= esc($account['ig_business_id']) ?></p>
        <p class="text-muted">Terhubung sejak: <?= esc($account['connected_at']) ?></p>
        <p>
            <span class="badge <?= strtotime($account['token_expires_at']) < time() ? 'badge-danger' : 'badge-success' ?>">
                Token expires: <?= esc($account['token_expires_at']) ?>
            </span>
        </p>
        <a href="<?= base_url('admin/ig-account/connect') ?>" class="btn btn-primary">Reconnect</a>
    <?php else: ?>
        <p class="text-muted">Belum ada akun Instagram yang terhubung.</p>
        <a href="<?= base_url('admin/ig-account/connect') ?>" class="btn btn-primary">Connect Instagram</a>
    <?php endif; ?>
</div>

<?php if ($account): ?>
<div class="card">
    <h2>Ads Account</h2>
    <?php if (! empty($account['ad_account_id'])): ?>
        <p><strong>Ad Account:</strong> <?= esc($account['ad_account_id']) ?></p>
        <?php if (! empty($account['business_manager_id'])): ?>
        <p class="text-muted">Business Manager ID: <?= esc($account['business_manager_id']) ?></p>
        <?php endif; ?>
        <p class="text-muted">Terhubung sejak: <?= esc($account['ads_connected_at'] ?? '-') ?></p>
        <a href="<?= base_url('admin/ads-account/select') ?>" class="btn">Ganti Ad Account</a>
    <?php else: ?>
        <p class="text-muted">Belum ada ad account yang dipilih. Pastikan izin <code>ads_read</code> sudah diberikan (reconnect di atas kalau perlu), lalu pilih ad account.</p>
        <a href="<?= base_url('admin/ads-account/select') ?>" class="btn btn-primary">Pilih Ad Account</a>
    <?php endif; ?>
</div>
<?php endif; ?>
