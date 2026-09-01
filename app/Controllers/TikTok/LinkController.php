<?php

namespace App\Controllers\TikTok;

use App\Controllers\BaseController;
use App\Libraries\AuditLogger;
use App\Models\TiktokInsightDailyModel;
use App\Models\TiktokLinkModel;

class LinkController extends BaseController
{
    public function index()
    {
        $linkModel    = new TiktokLinkModel();
        $insightModel = new TiktokInsightDailyModel();

        $links = $linkModel->orderBy('created_at', 'DESC')->findAll();

        foreach ($links as &$link) {
            $link['latest_insight'] = $insightModel->latestByLink($link['id']);
        }
        unset($link);

        return view('layouts/main', [
            'title'       => 'TikTok Links',
            'activeNav'   => 'tiktok-links',
            'contentView' => 'tiktok/links_index',
            'contentData' => [
                'links'   => $links,
                'canEdit' => $this->canManageLinks(),
            ],
        ]);
    }

    public function new()
    {
        $this->requireCanManageLinks();

        return view('layouts/main', [
            'title'       => 'Tambah TikTok Link',
            'activeNav'   => 'tiktok-links',
            'contentView' => 'tiktok/link_form',
            'contentData' => [],
        ]);
    }

    public function create()
    {
        $this->requireCanManageLinks();

        $linkModel = new TiktokLinkModel();

        $data = [
            'url'             => $this->request->getPost('url'),
            'creator_handle'  => $this->request->getPost('creator_handle'),
            'affiliate_note'  => $this->request->getPost('affiliate_note'),
            'data_source'     => $this->request->getPost('data_source') ?: 'scrape',
            'added_by'        => session()->get('user_id'),
            'created_at'      => date('Y-m-d H:i:s'),
        ];

        if (! $linkModel->validate($data)) {
            session()->setFlashdata('error', implode(' ', $linkModel->errors()));

            return redirect()->to('/tiktok/links/new')->withInput();
        }

        $linkId = $linkModel->insert($data);

        (new AuditLogger())->log(session()->get('user_id'), 'add_tiktok_link', 'tiktok_link', $linkId, ['url' => $data['url']]);

        session()->setFlashdata('success', 'Link TikTok berhasil ditambahkan.');

        return redirect()->to('/tiktok/links');
    }

    private function canManageLinks(): bool
    {
        return in_array(session()->get('role_name'), ['admin', 'content_manager'], true);
    }

    private function requireCanManageLinks(): void
    {
        if (! $this->canManageLinks()) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }
    }
}
