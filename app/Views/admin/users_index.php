<div class="app-topbar" style="margin-bottom: var(--space-4)">
    <div></div>
    <a href="<?= base_url('admin/users/new') ?>" class="btn btn-primary">+ Tambah User</a>
</div>

<div class="card">
    <table>
        <thead>
        <tr>
            <th>Nama</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Login Terakhir</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
        <tr>
            <td><?= esc($user['name']) ?></td>
            <td><?= esc($user['username']) ?></td>
            <td><?= esc($user['email']) ?></td>
            <td><span class="badge badge-success"><?= esc($user['role_name']) ?></span></td>
            <td><?= $user['is_active'] ? 'Aktif' : 'Nonaktif' ?></td>
            <td><?= esc($user['last_login_at'] ?? '-') ?></td>
            <td><a href="<?= base_url('admin/users/' . $user['id'] . '/edit') ?>" class="btn">Edit</a></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
