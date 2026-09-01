<div class="card" style="max-width: 420px">
    <form method="post" action="<?= base_url('admin/users') ?>">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="name">Nama</label>
            <input type="text" id="name" name="name" value="<?= esc(old('name')) ?>" required>
        </div>

        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?= esc(old('username')) ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= esc(old('email')) ?>" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required minlength="8">
        </div>

        <div class="form-group">
            <label for="role_id">Role</label>
            <select id="role_id" name="role_id" required>
                <?php foreach ($roles as $role): ?>
                <option value="<?= esc($role['id']) ?>"><?= esc($role['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="<?= base_url('admin/users') ?>" class="btn">Batal</a>
    </form>
</div>
