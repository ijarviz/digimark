<div class="card" style="max-width: 560px">
    <p class="text-muted">
        Kredensial Meta App di bawah ini digunakan untuk kedua fitur: <strong>Instagram Content Publish</strong>
        (compose &amp; jadwal publish) dan <strong>Ads Read</strong> (sinkronisasi insight ads) — keduanya memakai
        Meta App &amp; token yang sama, jadi cukup satu pengaturan di sini.
    </p>

    <form method="post" action="<?= base_url('admin/api-settings') ?>">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="app_id">App ID</label>
            <input type="text" id="app_id" name="app_id" value="<?= esc(old('app_id', $config['app_id'] ?? '')) ?>" required>
        </div>

        <div class="form-group">
            <label for="app_secret">App Secret</label>
            <input type="password" id="app_secret" name="app_secret"
                   placeholder="<?= ! empty($config['app_secret_encrypted']) ? 'Kosongkan jika tidak ingin mengubah' : 'Masukkan App Secret' ?>">
            <?php if (! empty($config['app_secret_encrypted'])): ?>
            <p class="text-muted">App Secret sudah tersimpan (terenkripsi). Isi hanya jika ingin menggantinya.</p>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="redirect_uri">Redirect URI</label>
            <input type="url" id="redirect_uri" name="redirect_uri"
                   value="<?= esc(old('redirect_uri', $config['redirect_uri'] ?? base_url('admin/ig-account/callback'))) ?>" required>
            <p class="text-muted">Harus sama persis dengan Valid OAuth Redirect URI yang didaftarkan di Meta App Dashboard.</p>
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>

<?php if (! empty($config['updated_at'])): ?>
<p class="text-muted">Terakhir diperbarui: <?= esc($config['updated_at']) ?></p>
<?php endif; ?>
