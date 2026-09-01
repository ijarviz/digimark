<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\AuditLogger;
use App\Models\MetaAppConfigModel;

/**
 * Admin settings menu for the Meta App credentials (App ID / App Secret /
 * Redirect URI) that power both Instagram Content Publish (Fase 3) and
 * Ads Read (Fase 2) — one shared app per the "same app" design decision,
 * so one settings form covers both instead of two separate credential sets.
 */
class MetaAppConfigController extends BaseController
{
    public function edit()
    {
        $config = (new MetaAppConfigModel())->getConfig();

        return view('layouts/main', [
            'title'       => 'API Settings (Meta App)',
            'activeNav'   => 'admin-api-settings',
            'contentView' => 'admin/meta_app_config_edit',
            'contentData' => ['config' => $config],
        ]);
    }

    public function update()
    {
        $rules = [
            'app_id'       => 'required|max_length[100]',
            'redirect_uri' => 'required|max_length[255]|valid_url_strict[http,https]',
            'app_secret'   => 'permit_empty|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            session()->setFlashdata('error', implode(' ', $this->validator->getErrors()));

            return redirect()->to('/admin/api-settings')->withInput();
        }

        $configModel = new MetaAppConfigModel();
        $appSecret   = $this->request->getPost('app_secret');

        $configModel->saveConfig(
            $this->request->getPost('app_id'),
            $appSecret !== '' ? $appSecret : null,
            $this->request->getPost('redirect_uri'),
            session()->get('user_id')
        );

        (new AuditLogger())->log(session()->get('user_id'), 'update_meta_app_config', 'meta_app_config', null, [
            'app_secret_changed' => $appSecret !== '',
        ]);

        session()->setFlashdata('success', 'Pengaturan API berhasil disimpan.');

        return redirect()->to('/admin/api-settings');
    }
}
