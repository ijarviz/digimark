<?php

namespace App\Controllers\Instagram;

use App\Controllers\BaseController;
use App\Libraries\AuditLogger;
use App\Libraries\Instagram\InstagramOAuthService;
use App\Libraries\Instagram\InstagramTokenService;
use App\Models\IgAccountModel;

/**
 * Admin-only: connect/reconnect the single Instagram Business account.
 * Route group already restricts this controller to role:admin; the
 * checks below are defense-in-depth in case routing is ever misconfigured.
 */
class OAuthController extends BaseController
{
    public function status()
    {
        $this->requireAdmin();

        $account = (new IgAccountModel())->getActiveAccount();

        return view('layouts/main', [
            'title'       => 'Koneksi Instagram',
            'activeNav'   => 'admin-ig-account',
            'contentView' => 'admin/ig_account_status',
            'contentData' => ['account' => $account],
        ]);
    }

    public function connect()
    {
        $this->requireAdmin();

        return redirect()->to((new InstagramOAuthService())->getAuthorizeUrl());
    }

    public function callback()
    {
        $this->requireAdmin();

        $code = $this->request->getGet('code');

        if (! $code) {
            session()->setFlashdata('error', 'Otorisasi Instagram gagal atau dibatalkan.');

            return redirect()->to('/admin/ig-account');
        }

        try {
            $result = (new InstagramOAuthService())->handleCallback($code);
        } catch (\Throwable $e) {
            log_message('error', '[ig-oauth] ' . $e->getMessage());
            session()->setFlashdata('error', 'Gagal menghubungkan akun Instagram: ' . $e->getMessage());

            return redirect()->to('/admin/ig-account');
        }

        $tokenService = new InstagramTokenService();
        $accountModel = new IgAccountModel();

        $accountId = $accountModel->insert([
            'ig_business_id'         => $result['ig_business_id'],
            'ig_username'            => $result['ig_username'],
            'fb_page_id'             => $result['fb_page_id'],
            'access_token_encrypted' => $tokenService->encrypt($result['access_token']),
            'token_expires_at'       => $result['expires_at']->format('Y-m-d H:i:s'),
            'connected_by'           => session()->get('user_id'),
            'connected_at'           => date('Y-m-d H:i:s'),
        ]);

        (new AuditLogger())->log(session()->get('user_id'), 'connect_account', 'ig_account', $accountId);

        session()->setFlashdata('success', 'Akun Instagram berhasil terhubung: @' . $result['ig_username']);

        return redirect()->to('/admin/ig-account');
    }

    private function requireAdmin(): void
    {
        if (session()->get('role_name') !== 'admin') {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }
    }
}
