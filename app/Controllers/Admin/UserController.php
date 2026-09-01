<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
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
}
