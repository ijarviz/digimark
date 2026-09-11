<div class="flex justify-end mb-gutter">
    <a href="<?= base_url('admin/users/new') ?>" class="h-[36px] px-4 rounded bg-primary text-on-primary text-body-sm font-body-sm font-medium hover:bg-primary-container hover:text-on-primary-container transition-colors inline-flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px]">person_add</span>
        Tambah User
    </a>
</div>

<form method="get" action="<?= base_url('admin/users') ?>" class="bg-surface-container-lowest border border-outline-variant rounded-lg p-3 mb-gutter flex flex-wrap items-end gap-3">
    <div class="flex flex-col gap-1">
        <label for="filter-q" class="font-label-caps text-label-caps text-on-surface-variant">Cari user</label>
        <input type="text" id="filter-q" name="q" value="<?= esc($filters['q']) ?>" placeholder="Nama, username, atau email..." class="bg-surface border border-outline-variant rounded px-3 py-2 text-body-sm font-body-sm text-on-surface w-64">
    </div>
    <div class="flex flex-col gap-1">
        <label for="filter-role" class="font-label-caps text-label-caps text-on-surface-variant">Role</label>
        <select id="filter-role" name="role_id" class="bg-surface border border-outline-variant rounded px-3 py-2 text-body-sm font-body-sm text-on-surface">
            <option value="">Semua Role</option>
            <?php foreach ($roles as $role): ?>
            <option value="<?= esc((string) $role['id']) ?>" <?= $filters['role_id'] === (string) $role['id'] ? 'selected' : '' ?>><?= esc($role['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="flex flex-col gap-1">
        <label for="filter-status" class="font-label-caps text-label-caps text-on-surface-variant">Status</label>
        <select id="filter-status" name="status" class="bg-surface border border-outline-variant rounded px-3 py-2 text-body-sm font-body-sm text-on-surface">
            <option value="">Semua Status</option>
            <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Aktif</option>
            <option value="inactive" <?= $filters['status'] === 'inactive' ? 'selected' : '' ?>>Nonaktif</option>
        </select>
    </div>
    <button type="submit" class="bg-primary text-on-primary font-body-md text-body-md px-4 py-2 rounded shadow hover:bg-primary-container hover:text-on-primary-container transition-all flex items-center gap-2">
        <span class="material-symbols-outlined text-sm">filter_alt</span>
        Filter
    </button>
    <?php if ($filters['q'] !== '' || $filters['role_id'] !== '' || $filters['status'] !== ''): ?>
    <a href="<?= base_url('admin/users') ?>" class="text-body-sm font-body-sm text-on-surface-variant hover:text-primary px-2 py-2">Reset</a>
    <?php endif; ?>
</form>

<div class="bg-surface-container-lowest border border-outline-variant rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface border-b border-outline-variant text-label-caps font-label-caps text-on-surface-variant uppercase tracking-wider">
            <tr>
                <th class="p-table-cell-padding">Nama</th>
                <th class="p-table-cell-padding">Username</th>
                <th class="p-table-cell-padding">Email</th>
                <th class="p-table-cell-padding">Role</th>
                <th class="p-table-cell-padding">Status</th>
                <th class="p-table-cell-padding">Login Terakhir</th>
                <th class="p-table-cell-padding"></th>
            </tr>
            </thead>
            <tbody class="text-body-sm font-body-sm text-on-surface">
            <?php if (empty($users)): ?>
            <tr><td colspan="7" class="p-table-cell-padding text-on-surface-variant">Tidak ada user yang cocok dengan filter.</td></tr>
            <?php endif; ?>
            <?php foreach ($users as $user): ?>
            <tr class="h-[48px] border-b border-outline-variant hover:bg-surface-container-low transition-colors">
                <td class="p-table-cell-padding font-medium"><?= esc($user['name']) ?></td>
                <td class="p-table-cell-padding text-on-surface-variant"><?= esc($user['username']) ?></td>
                <td class="p-table-cell-padding text-on-surface-variant"><?= esc($user['email']) ?></td>
                <td class="p-table-cell-padding">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-primary-fixed text-on-primary-fixed"><?= esc($user['role_name']) ?></span>
                </td>
                <td class="p-table-cell-padding">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium <?= $user['is_active'] ? 'bg-[#E3FCEF] text-[#006644]' : 'bg-[#FFEBE6] text-[#BF2600]' ?>">
                        <?= $user['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                    </span>
                </td>
                <td class="p-table-cell-padding text-on-surface-variant font-data-mono text-data-mono"><?= esc($user['last_login_at'] ?? '-') ?></td>
                <td class="p-table-cell-padding">
                    <a href="<?= base_url('admin/users/' . $user['id'] . '/edit') ?>" class="p-1 rounded text-on-surface-variant hover:bg-surface-variant hover:text-primary transition-colors inline-flex" title="Edit">
                        <span class="material-symbols-outlined text-[18px]">edit</span>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
