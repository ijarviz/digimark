<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Libraries\AuditLogger;
use App\Models\UserModel;
use Config\Services;

class LoginController extends BaseController
{
    public function new()
    {
        if (session()->get('logged_in')) {
            return redirect()->to('/dashboard/instagram');
        }

        return view('layouts/auth', [
            'title'       => 'Login',
            'contentView' => 'auth/login',
            'contentData' => [],
        ]);
    }

    public function attempt()
    {
        $ip        = $this->request->getIPAddress();
        $throttler = Services::throttler();

        // Cache keys disallow {}()/\@: — IPv6 addresses (e.g. "::1") contain
        // colons, so the IP must be sanitized before use as a throttle key.
        $throttleKey = 'login_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $ip);

        // Consume capacity on every attempt (not just failures) so slow
        // retries can't be used to dodge the lockout window.
        if ($throttler->check($throttleKey, 5, MINUTE * 15) === false) {
            session()->setFlashdata('error', 'Terlalu banyak percobaan login. Coba lagi dalam beberapa menit.');

            return redirect()->to('/login');
        }

        $rules = [
            'username' => 'required',
            'password' => 'required',
        ];

        if (! $this->validate($rules)) {
            session()->setFlashdata('error', 'Username dan password wajib diisi.');

            return redirect()->to('/login')->withInput();
        }

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $userModel = new UserModel();
        $user      = $userModel->findActiveByUsername($username);

        if (! $user || ! $userModel->verifyPassword($user, $password)) {
            session()->setFlashdata('error', 'Username atau password salah.');

            return redirect()->to('/login')->withInput();
        }

        session()->regenerate();
        session()->set([
            'logged_in' => true,
            'user_id'   => $user['id'],
            'username'  => $user['username'],
            'role_id'   => $user['role_id'],
            'role_name' => $user['role_name'],
        ]);

        $userModel->touchLastLogin($user['id']);
        (new AuditLogger())->log($user['id'], 'login', 'user', $user['id']);

        return redirect()->to('/dashboard/instagram');
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to('/login');
    }
}
