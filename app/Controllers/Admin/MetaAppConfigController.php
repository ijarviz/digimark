<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\AuditLogger;
use App\Models\MetaAppConfigModel;
use Config\Services;

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
        // Trim before validating/saving: copy-pasting the App ID from the Meta
        // App Dashboard commonly drags along a leading/trailing space or
        // newline, which Facebook's OAuth dialog then rejects with "Invalid
        // App ID: The provided app ID does not look like a valid app ID."
        $data = [
            'app_id'       => trim((string) $this->request->getPost('app_id')),
            'redirect_uri' => trim((string) $this->request->getPost('redirect_uri')),
            'app_secret'   => trim((string) $this->request->getPost('app_secret')),
        ];

        $rules = [
            'app_id'       => 'required|max_length[100]|regex_match[/^[0-9]+$/]',
            'redirect_uri' => 'required|max_length[255]|valid_url_strict[http,https]',
            'app_secret'   => 'permit_empty|max_length[255]',
        ];

        $messages = [
            'app_id' => [
                'regex_match' => 'App ID Meta hanya berisi angka — periksa kembali, mungkin ada karakter atau spasi yang tidak sengaja ikut ter-copy.',
            ],
        ];

        $validation = Services::validation();
        $validation->setRules($rules, $messages);

        if (! $validation->run($data)) {
            session()->setFlashdata('error', implode(' ', $validation->getErrors()));

            return redirect()->to('/admin/api-settings')->withInput();
        }

        $configModel = new MetaAppConfigModel();

        $configModel->saveConfig(
            $data['app_id'],
            $data['app_secret'] !== '' ? $data['app_secret'] : null,
            $data['redirect_uri'],
            session()->get('user_id')
        );

        (new AuditLogger())->log(session()->get('user_id'), 'update_meta_app_config', 'meta_app_config', null, [
            'app_secret_changed' => $data['app_secret'] !== '',
        ]);

        session()->setFlashdata('success', 'Pengaturan API berhasil disimpan.');

        return redirect()->to('/admin/api-settings');
    }
}
