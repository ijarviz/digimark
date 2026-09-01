<div class="card" style="max-width: 420px">
    <p><strong>Username:</strong> <?= esc($user['username']) ?></p>
    <p class="text-muted"><?= esc($user['email']) ?></p>

    <form method="post" action="<?= base_url('admin/users/' . $user['id'] . '/update') ?>">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="role_id">Role</label>
            <select id="role_id" name="role_id" required>
                <?php foreach ($roles as $role): ?>
                <option value="<?= esc($role['id']) ?>" <?= (int) $role['id'] === (int) $user['role_id'] ? 'selected' : '' ?>>
                    <?= esc($role['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="password">Password Baru</label>
            <input type="password" id="password" name="password" minlength="8" placeholder="Kosongkan jika tidak ingin mengubah password">
        </div>

        <div class="form-group">
            <label for="password_confirm">Konfirmasi Password Baru</label>
            <input type="password" id="password_confirm" name="password_confirm" minlength="8" placeholder="Ulangi password baru">
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="is_active" value="1" <?= $user['is_active'] ? 'checked' : '' ?>
                    <?= (int) $user['id'] === (int) session()->get('user_id') ? 'disabled' : '' ?>>
                Aktif
            </label>
            <?php if ((int) $user['id'] === (int) session()->get('user_id')): ?>
            <input type="hidden" name="is_active" value="1">
            <p class="text-muted">Tidak bisa menonaktifkan akun sendiri.</p>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="<?= base_url('admin/users') ?>" class="btn">Batal</a>
    </form>
</div>
