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
