<div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-card-padding max-w-lg">
    <p class="text-body-sm font-body-sm text-on-surface-variant mb-4">
        Kredensial Meta App di bawah ini digunakan untuk kedua fitur: <strong class="text-on-surface">Instagram Content Publish</strong>
        (compose &amp; jadwal publish) dan <strong class="text-on-surface">Ads Read</strong> (sinkronisasi insight ads) — keduanya memakai
        Meta App &amp; token yang sama, jadi cukup satu pengaturan di sini.
    </p>

    <form method="post" action="<?= base_url('admin/api-settings') ?>" class="space-y-4">
        <?= csrf_field() ?>

        <div>
            <label for="app_id" class="block text-label-caps font-label-caps text-on-surface-variant mb-1">App ID</label>
            <input type="text" id="app_id" name="app_id" value="<?= esc(old('app_id', $config['app_id'] ?? '')) ?>" required
                   class="w-full h-[36px] px-3 rounded border border-outline-variant bg-surface text-body-sm font-body-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
        </div>

        <div>
            <label for="app_secret" class="block text-label-caps font-label-caps text-on-surface-variant mb-1">App Secret</label>
            <input type="password" id="app_secret" name="app_secret"
                   placeholder="<?= ! empty($config['app_secret_encrypted']) ? 'Kosongkan jika tidak ingin mengubah' : 'Masukkan App Secret' ?>"
                   class="w-full h-[36px] px-3 rounded border border-outline-variant bg-surface text-body-sm font-body-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
            <?php if (! empty($config['app_secret_encrypted'])): ?>
            <p class="text-body-sm font-body-sm text-on-surface-variant mt-1">App Secret sudah tersimpan (terenkripsi). Isi hanya jika ingin menggantinya.</p>
            <?php endif; ?>
        </div>

        <div>
            <label for="redirect_uri" class="block text-label-caps font-label-caps text-on-surface-variant mb-1">Redirect URI</label>
            <input type="url" id="redirect_uri" name="redirect_uri"
                   value="<?= esc(old('redirect_uri', $config['redirect_uri'] ?? base_url('admin/ig-account/callback'))) ?>" required
                   class="w-full h-[36px] px-3 rounded border border-outline-variant bg-surface text-body-sm font-body-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
            <p class="text-body-sm font-body-sm text-on-surface-variant mt-1">Harus sama persis dengan Valid OAuth Redirect URI yang didaftarkan di Meta App Dashboard.</p>
        </div>

        <button type="submit" class="h-[36px] px-4 rounded bg-primary text-on-primary text-body-sm font-body-sm font-medium hover:bg-primary-container hover:text-on-primary-container transition-colors">Simpan</button>
    </form>
</div>

<?php if (! empty($config['updated_at'])): ?>
<p class="text-body-sm font-body-sm text-on-surface-variant mt-3">Terakhir diperbarui: <?= esc($config['updated_at']) ?></p>
<?php endif; ?>
