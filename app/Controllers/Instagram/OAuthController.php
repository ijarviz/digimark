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

        // Reconnecting the same ig_business_id updates its existing row
        // (preserving any already-selected ad_account_id) instead of
        // accumulating duplicate rows — previously every callback inserted
        // a fresh row and "the active account" was just "highest id".
        $accountId = $accountModel->upsertConnection([
            'ig_business_id'         => $result['ig_business_id'],
            'ig_username'            => $result['ig_username'],
            'fb_page_id'             => $result['fb_page_id'],
            'access_token_encrypted' => $tokenService->encrypt($result['access_token']),
            'token_expires_at'       => $result['expires_at']->format('Y-m-d H:i:s'),
            'connected_by'           => session()->get('user_id'),
            'connected_at'           => date('Y-m-d H:i:s'),
        ]);

        (new AuditLogger())->log(session()->get('user_id'), 'connect_account', 'ig_account', $accountId);

        $flash = 'Akun Instagram berhasil terhubung: @' . $result['ig_username'];
        $flash .= ' ' . $this->tryAutoResolveAdAccount($accountModel, $accountId, $result['access_token']);

        session()->setFlashdata('success', trim($flash));

        return redirect()->to('/admin/ig-account');
    }

    /**
     * Best-effort: if ads_read was granted and the user has exactly one
     * ad account, auto-select it so the admin doesn't need an extra step.
     * Any failure here is non-fatal — the IG connection itself already
     * succeeded by the time this runs.
     */
    private function tryAutoResolveAdAccount(IgAccountModel $accountModel, int $accountId, string $accessToken): string
    {
        try {
            $adAccounts = (new InstagramOAuthService())->listAdAccounts($accessToken);
        } catch (\Throwable $e) {
            log_message('error', '[ig-oauth] listAdAccounts failed: ' . $e->getMessage());

            return 'Ad account belum terhubung (gagal mengambil daftar — coba pilih manual di halaman Ads Account).';
        }

        if (count($adAccounts) === 1) {
            $accountModel->update($accountId, [
                'ad_account_id'       => $adAccounts[0]['id'],
                'business_manager_id' => $adAccounts[0]['business_id'],
                'ads_connected_at'    => date('Y-m-d H:i:s'),
            ]);

            return 'Ad account otomatis terhubung: ' . $adAccounts[0]['name'] . '.';
        }

        if (count($adAccounts) > 1) {
            return 'Ditemukan beberapa ad account — silakan pilih di halaman Ads Account.';
        }

        return 'Tidak ada ad account ditemukan untuk akun ini.';
    }

    private function requireAdmin(): void
    {
        if (session()->get('role_name') !== 'admin') {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }
    }
}
