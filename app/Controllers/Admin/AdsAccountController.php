<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\AuditLogger;
use App\Libraries\Instagram\InstagramOAuthService;
use App\Libraries\Instagram\InstagramTokenService;
use App\Models\IgAccountModel;

/**
 * Manual ad-account picker for when the connected user has more than one
 * ad account (OAuthController::callback() auto-selects when there's
 * exactly one). Admin-only, same account/token as Fase 1's IG connection.
 */
class AdsAccountController extends BaseController
{
    public function select()
    {
        $this->requireAdmin();

        $account = (new IgAccountModel())->getActiveAccount();

        if (! $account) {
            session()->setFlashdata('error', 'Hubungkan akun Instagram terlebih dahulu.');

            return redirect()->to('/admin/ig-account');
        }

        try {
            $accessToken = (new InstagramTokenService())->decrypt($account['access_token_encrypted']);
            $adAccounts  = (new InstagramOAuthService())->listAdAccounts($accessToken);
        } catch (\Throwable $e) {
            session()->setFlashdata('error', 'Gagal mengambil daftar ad account: ' . $e->getMessage());

            return redirect()->to('/admin/ig-account');
        }

        return view('layouts/main', [
            'title'       => 'Pilih Ad Account',
            'activeNav'   => 'admin-ig-account',
            'contentView' => 'admin/ads_account_select',
            'contentData' => ['adAccounts' => $adAccounts],
        ]);
    }

    public function choose()
    {
        $this->requireAdmin();

        $accountModel = new IgAccountModel();
        $account      = $accountModel->getActiveAccount();

        if (! $account) {
            session()->setFlashdata('error', 'Hubungkan akun Instagram terlebih dahulu.');

            return redirect()->to('/admin/ig-account');
        }

        $adAccountId  = $this->request->getPost('ad_account_id');
        $businessId   = $this->request->getPost('business_manager_id') ?: null;

        if (! $adAccountId) {
            session()->setFlashdata('error', 'Pilih salah satu ad account.');

            return redirect()->to('/admin/ads-account/select');
        }

        $accountModel->update($account['id'], [
            'ad_account_id'       => $adAccountId,
            'business_manager_id' => $businessId,
            'ads_connected_at'    => date('Y-m-d H:i:s'),
        ]);

        (new AuditLogger())->log(session()->get('user_id'), 'connect_ads_account', 'ig_account', $account['id'], ['ad_account_id' => $adAccountId]);

        session()->setFlashdata('success', 'Ad account berhasil dipilih: ' . $adAccountId);

        return redirect()->to('/admin/ig-account');
    }

    private function requireAdmin(): void
    {
        if (session()->get('role_name') !== 'admin') {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }
    }
}
