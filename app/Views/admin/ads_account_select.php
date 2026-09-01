<div class="card">
    <?php if (empty($adAccounts)): ?>
        <p class="text-muted">Tidak ada ad account ditemukan untuk akun ini.</p>
        <a href="<?= base_url('admin/ig-account') ?>" class="btn">Kembali</a>
    <?php else: ?>
        <form method="post" action="<?= base_url('admin/ads-account/choose') ?>">
            <?= csrf_field() ?>

            <?php foreach ($adAccounts as $i => $account): ?>
            <div class="form-group">
                <label>
                    <input type="radio" name="ad_account_id" value="<?= esc($account['id']) ?>"
                           data-business-id="<?= esc($account['business_id'] ?? '') ?>"
                           <?= $i === 0 ? 'checked' : '' ?> required>
                    <?= esc($account['name']) ?> (<?= esc($account['id']) ?>)
                </label>
            </div>
            <?php endforeach; ?>

            <input type="hidden" name="business_manager_id" id="business_manager_id" value="<?= esc($adAccounts[0]['business_id'] ?? '') ?>">

            <button type="submit" class="btn btn-primary">Pilih</button>
        </form>
    <?php endif; ?>
</div>

<script>
document.querySelectorAll('input[name="ad_account_id"]').forEach(function (radio) {
    radio.addEventListener('change', function () {
        document.getElementById('business_manager_id').value = this.dataset.businessId || '';
    });
});
</script>
