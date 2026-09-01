<div class="flex justify-end mb-gutter">
    <a href="<?= base_url('admin/users/new') ?>" class="h-[36px] px-4 rounded bg-primary text-on-primary text-body-sm font-body-sm font-medium hover:bg-primary-container hover:text-on-primary-container transition-colors inline-flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px]">person_add</span>
        Tambah User
    </a>
</div>

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
