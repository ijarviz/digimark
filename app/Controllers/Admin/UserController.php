<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\AuditLogger;
use App\Models\RoleModel;
use App\Models\UserModel;

class UserController extends BaseController
{
    public function index()
    {
        $users = (new UserModel())
            ->select('users.*, roles.name as role_name')
            ->join('roles', 'roles.id = users.role_id')
            ->orderBy('users.created_at', 'DESC')
            ->findAll();

        return view('layouts/main', [
            'title'       => 'Kelola User',
            'activeNav'   => 'admin-users',
            'contentView' => 'admin/users_index',
            'contentData' => ['users' => $users],
        ]);
    }

    public function new()
    {
        $roles = (new RoleModel())->findAll();

        return view('layouts/main', [
            'title'       => 'Tambah User',
            'activeNav'   => 'admin-users',
            'contentView' => 'admin/user_form',
            'contentData' => ['roles' => $roles],
        ]);
    }

    public function create()
    {
        $userModel = new UserModel();

        $rules = [
            'name'             => 'required|max_length[150]',
            'email'            => 'required|valid_email|is_unique[users.email]',
            'username'         => 'required|max_length[100]|is_unique[users.username]',
            'password'         => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
            // is_not_unique[roles.id] confirms the submitted id actually
            // exists — without this, a bad value passes validation and
            // only fails later as an uncaught FK constraint error (500).
            'role_id'          => 'required|is_natural_no_zero|is_not_unique[roles.id]',
        ];

        $messages = [
            'password_confirm' => ['matches' => 'Konfirmasi password tidak cocok.'],
            'role_id'           => ['is_not_unique' => 'Role tidak valid.'],
        ];

        if (! $this->validate($rules, $messages)) {
            session()->setFlashdata('error', implode(' ', $this->validator->getErrors()));

            return redirect()->to('/admin/users/new')->withInput();
        }

        $now = date('Y-m-d H:i:s');

        $userModel->insert([
            'name'                 => $this->request->getPost('name'),
            'email'                => $this->request->getPost('email'),
            'username'             => $this->request->getPost('username'),
            'password_hash'        => UserModel::hashPassword($this->request->getPost('password')),
            'role_id'              => $this->request->getPost('role_id'),
            'is_active'            => 1,
            'created_at'           => $now,
            'updated_at'           => $now,
            'password_changed_at'  => $now,
        ]);

        session()->setFlashdata('success', 'User berhasil dibuat.');

        return redirect()->to('/admin/users');
    }

    public function edit(int $id)
    {
        $userModel = new UserModel();
        $user      = $userModel->find($id);

        if (! $user) {
            session()->setFlashdata('error', 'User tidak ditemukan.');

            return redirect()->to('/admin/users');
        }

        $roles = (new RoleModel())->findAll();

        return view('layouts/main', [
            'title'       => 'Edit User',
            'activeNav'   => 'admin-users',
            'contentView' => 'admin/user_edit',
            'contentData' => ['user' => $user, 'roles' => $roles],
        ]);
    }

    public function update(int $id)
    {
        $userModel = new UserModel();
        $user      = $userModel->find($id);

        if (! $user) {
            session()->setFlashdata('error', 'User tidak ditemukan.');

            return redirect()->to('/admin/users');
        }

        $rules = [
            'role_id'          => 'required|is_natural_no_zero|is_not_unique[roles.id]',
            // Empty password means "keep the current one" — only enforce
            // min_length/confirmation when the admin actually typed one.
            'password'         => 'permit_empty|min_length[8]',
            'password_confirm' => 'permit_empty|matches[password]',
        ];

        $messages = [
            'password_confirm' => ['matches' => 'Konfirmasi password tidak cocok.'],
            'role_id'           => ['is_not_unique' => 'Role tidak valid.'],
        ];

        if (! $this->validate($rules, $messages)) {
            session()->setFlashdata('error', implode(' ', $this->validator->getErrors()));

            return redirect()->to("/admin/users/{$id}/edit")->withInput();
        }

        $newRoleId = (int) $this->request->getPost('role_id');
        $password  = $this->request->getPost('password');
        $isActive  = $this->request->getPost('is_active') === '1' ? 1 : 0;
        $isSelf    = $id === (int) session()->get('user_id');

        // Guard against an admin locking themselves out by changing their
        // own role away from admin, or deactivating their own account.
        if ($isSelf) {
            $currentRole = (new RoleModel())->find($user['role_id']);
            $isCurrentlyAdmin = $currentRole && $currentRole['name'] === 'admin';

            if ($isCurrentlyAdmin && $newRoleId !== (int) $user['role_id']) {
                session()->setFlashdata('error', 'Tidak bisa mengubah role akun sendiri.');

                return redirect()->to("/admin/users/{$id}/edit")->withInput();
            }

            if ($isActive === 0) {
                session()->setFlashdata('error', 'Tidak bisa menonaktifkan akun sendiri.');

                return redirect()->to("/admin/users/{$id}/edit")->withInput();
            }
        }

        $roleChanged     = $newRoleId !== (int) $user['role_id'];
        $passwordChanged = ! empty($password);
        $activeChanged   = $isActive !== (int) $user['is_active'];

        $now = date('Y-m-d H:i:s');

        $data = [
            'role_id'    => $newRoleId,
            'is_active'  => $isActive,
            'updated_at' => $now,
        ];

        if ($passwordChanged) {
            $data['password_hash']       = UserModel::hashPassword($password);
            $data['password_changed_at'] = $now;
        }

        $userModel->update($id, $data);

        $auditLogger = new AuditLogger();

        if ($roleChanged) {
            $auditLogger->log(session()->get('user_id'), 'change_user_role', 'user', $id, [
                'from_role_id' => (int) $user['role_id'],
                'to_role_id'   => $newRoleId,
            ]);
        }

        if ($passwordChanged) {
            $auditLogger->log(session()->get('user_id'), 'reset_user_password', 'user', $id);
        }

        if ($activeChanged) {
            $auditLogger->log(session()->get('user_id'), $isActive ? 'activate_user' : 'deactivate_user', 'user', $id);
        }

        session()->setFlashdata('success', 'User berhasil diperbarui.');

        return redirect()->to('/admin/users');
    }
}
