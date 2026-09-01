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
            'name'     => 'required|max_length[150]',
            'email'    => 'required|valid_email|is_unique[users.email]',
            'username' => 'required|max_length[100]|is_unique[users.username]',
            'password' => 'required|min_length[8]',
            'role_id'  => 'required|is_natural_no_zero',
        ];

        if (! $this->validate($rules)) {
            session()->setFlashdata('error', implode(' ', $this->validator->getErrors()));

            return redirect()->to('/admin/users/new')->withInput();
        }

        $userModel->insert([
            'name'          => $this->request->getPost('name'),
            'email'         => $this->request->getPost('email'),
            'username'      => $this->request->getPost('username'),
            'password_hash' => UserModel::hashPassword($this->request->getPost('password')),
            'role_id'       => $this->request->getPost('role_id'),
            'is_active'     => 1,
            'created_at'    => date('Y-m-d H:i:s'),
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
            'role_id'  => 'required|is_natural_no_zero',
            // Empty password means "keep the current one" — only enforce
            // min_length when the admin actually typed a new one.
            'password' => 'permit_empty|min_length[8]',
        ];

        if (! $this->validate($rules)) {
            session()->setFlashdata('error', implode(' ', $this->validator->getErrors()));

            return redirect()->to("/admin/users/{$id}/edit")->withInput();
        }

        $newRoleId = (int) $this->request->getPost('role_id');
        $password  = $this->request->getPost('password');

        // Guard against an admin locking themselves out by changing their
        // own role away from admin.
        if ($id === (int) session()->get('user_id') && $newRoleId !== (int) $user['role_id']) {
            $currentRole = (new RoleModel())->find($user['role_id']);

            if ($currentRole && $currentRole['name'] === 'admin') {
                session()->setFlashdata('error', 'Tidak bisa mengubah role akun sendiri.');

                return redirect()->to("/admin/users/{$id}/edit")->withInput();
            }
        }

        $roleChanged     = $newRoleId !== (int) $user['role_id'];
        $passwordChanged = ! empty($password);

        $data = ['role_id' => $newRoleId];

        if ($passwordChanged) {
            $data['password_hash'] = UserModel::hashPassword($password);
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

        session()->setFlashdata('success', 'User berhasil diperbarui.');

        return redirect()->to('/admin/users');
    }
}
