<div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-card-padding max-w-lg">
    <?php if (empty($adAccounts)): ?>
    <p class="text-body-sm font-body-sm text-on-surface-variant mb-3">Tidak ada ad account ditemukan untuk akun ini.</p>
    <a href="<?= base_url('admin/ig-account') ?>" class="h-[36px] px-4 rounded border border-outline-variant text-body-sm font-body-sm text-on-surface-variant hover:bg-surface-container transition-colors inline-flex items-center">Kembali</a>
    <?php else: ?>
    <form method="post" action="<?= base_url('admin/ads-account/choose') ?>" class="space-y-3">
        <?= csrf_field() ?>

        <?php foreach ($adAccounts as $i => $account): ?>
        <label class="flex items-center gap-3 p-3 rounded border border-outline-variant hover:bg-surface-container-low cursor-pointer has-[:checked]:border-primary has-[:checked]:bg-primary-fixed/10">
            <input type="radio" name="ad_account_id" value="<?= esc($account['id']) ?>"
                   data-business-id="<?= esc($account['business_id'] ?? '') ?>"
                   <?= $i === 0 ? 'checked' : '' ?> required class="text-primary focus:ring-primary">
            <span class="text-body-sm font-body-sm text-on-surface"><?= esc($account['name']) ?> <span class="text-on-surface-variant font-data-mono">(<?= esc($account['id']) ?>)</span></span>
        </label>
        <?php endforeach; ?>

        <input type="hidden" name="business_manager_id" id="business_manager_id" value="<?= esc($adAccounts[0]['business_id'] ?? '') ?>">

        <button type="submit" class="h-[36px] px-4 rounded bg-primary text-on-primary text-body-sm font-body-sm font-medium hover:bg-primary-container hover:text-on-primary-container transition-colors">Pilih</button>
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
