<div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-card-padding max-w-md">
    <form method="post" action="<?= base_url('admin/users') ?>" class="space-y-4">
        <?= csrf_field() ?>

        <div>
            <label for="name" class="block text-label-caps font-label-caps text-on-surface-variant mb-1">Nama</label>
            <input type="text" id="name" name="name" value="<?= esc(old('name')) ?>" required
                   class="w-full h-[36px] px-3 rounded border border-outline-variant bg-surface text-body-sm font-body-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
        </div>

        <div>
            <label for="username" class="block text-label-caps font-label-caps text-on-surface-variant mb-1">Username</label>
            <input type="text" id="username" name="username" value="<?= esc(old('username')) ?>" required
                   class="w-full h-[36px] px-3 rounded border border-outline-variant bg-surface text-body-sm font-body-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
        </div>

        <div>
            <label for="email" class="block text-label-caps font-label-caps text-on-surface-variant mb-1">Email</label>
            <input type="email" id="email" name="email" value="<?= esc(old('email')) ?>" required
                   class="w-full h-[36px] px-3 rounded border border-outline-variant bg-surface text-body-sm font-body-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
        </div>

        <div>
            <label for="password" class="block text-label-caps font-label-caps text-on-surface-variant mb-1">Password</label>
            <input type="password" id="password" name="password" required minlength="8"
                   class="w-full h-[36px] px-3 rounded border border-outline-variant bg-surface text-body-sm font-body-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
        </div>

        <div>
            <label for="password_confirm" class="block text-label-caps font-label-caps text-on-surface-variant mb-1">Konfirmasi Password</label>
            <input type="password" id="password_confirm" name="password_confirm" required minlength="8"
                   class="w-full h-[36px] px-3 rounded border border-outline-variant bg-surface text-body-sm font-body-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
        </div>

        <div>
            <label for="role_id" class="block text-label-caps font-label-caps text-on-surface-variant mb-1">Role</label>
            <select id="role_id" name="role_id" required
                    class="w-full h-[36px] px-3 rounded border border-outline-variant bg-surface text-body-sm font-body-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                <?php foreach ($roles as $role): ?>
                <option value="<?= esc($role['id']) ?>"><?= esc($role['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="h-[36px] px-4 rounded bg-primary text-on-primary text-body-sm font-body-sm font-medium hover:bg-primary-container hover:text-on-primary-container transition-colors">Simpan</button>
            <a href="<?= base_url('admin/users') ?>" class="h-[36px] px-4 rounded border border-outline-variant text-body-sm font-body-sm text-on-surface-variant flex items-center hover:bg-surface-container transition-colors">Batal</a>
        </div>
    </form>
</div>
