<div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-card-padding max-w-md">
    <p class="text-body-md font-body-md font-semibold text-on-surface"><?= esc($user['username']) ?></p>
    <p class="text-body-sm font-body-sm text-on-surface-variant mb-4"><?= esc($user['email']) ?></p>

    <form method="post" action="<?= base_url('admin/users/' . $user['id'] . '/update') ?>" class="space-y-4">
        <?= csrf_field() ?>

        <div>
            <label for="role_id" class="block text-label-caps font-label-caps text-on-surface-variant mb-1">Role</label>
            <select id="role_id" name="role_id" required
                    class="w-full h-[36px] px-3 rounded border border-outline-variant bg-surface text-body-sm font-body-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                <?php foreach ($roles as $role): ?>
                <option value="<?= esc($role['id']) ?>" <?= (int) $role['id'] === (int) $user['role_id'] ? 'selected' : '' ?>>
                    <?= esc($role['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="password" class="block text-label-caps font-label-caps text-on-surface-variant mb-1">Password Baru</label>
            <input type="password" id="password" name="password" minlength="8" placeholder="Kosongkan jika tidak ingin mengubah password"
                   class="w-full h-[36px] px-3 rounded border border-outline-variant bg-surface text-body-sm font-body-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
        </div>

        <div>
            <label for="password_confirm" class="block text-label-caps font-label-caps text-on-surface-variant mb-1">Konfirmasi Password Baru</label>
            <input type="password" id="password_confirm" name="password_confirm" minlength="8" placeholder="Ulangi password baru"
                   class="w-full h-[36px] px-3 rounded border border-outline-variant bg-surface text-body-sm font-body-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
        </div>

        <div>
            <label class="flex items-center gap-2 text-body-sm font-body-sm text-on-surface">
                <input type="checkbox" name="is_active" value="1" <?= $user['is_active'] ? 'checked' : '' ?>
                    <?= (int) $user['id'] === (int) session()->get('user_id') ? 'disabled' : '' ?>
                    class="rounded border-outline-variant text-primary focus:ring-primary">
                Aktif
            </label>
            <?php if ((int) $user['id'] === (int) session()->get('user_id')): ?>
            <input type="hidden" name="is_active" value="1">
            <p class="text-body-sm font-body-sm text-on-surface-variant mt-1">Tidak bisa menonaktifkan akun sendiri.</p>
            <?php endif; ?>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="h-[36px] px-4 rounded bg-primary text-on-primary text-body-sm font-body-sm font-medium hover:bg-primary-container hover:text-on-primary-container transition-colors">Simpan</button>
            <a href="<?= base_url('admin/users') ?>" class="h-[36px] px-4 rounded border border-outline-variant text-body-sm font-body-sm text-on-surface-variant flex items-center hover:bg-surface-container transition-colors">Batal</a>
        </div>
    </form>
</div>
