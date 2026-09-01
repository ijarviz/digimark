<?php

namespace App\Controllers\TikTok;

use App\Controllers\BaseController;
use App\Models\TiktokInsightDailyModel;
use App\Models\TiktokLinkModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $date = $this->request->getGet('date') ?: date('Y-m-d');

        $linkModel    = new TiktokLinkModel();
        $insightModel = new TiktokInsightDailyModel();

        $links = $linkModel->orderBy('created_at', 'DESC')->findAll();

        foreach ($links as &$link) {
            $link['insight'] = $insightModel->forLinkAndDate($link['id'], $date)
                ?? $insightModel->latestByLink($link['id']);
        }
        unset($link);

        return view('layouts/main', [
            'title'       => 'TikTok Metrics',
            'subtitle'    => 'Ringkasan performa harian untuk setiap link TikTok yang dilacak.',
            'activeNav'   => 'tiktok-dashboard',
            'contentView' => 'tiktok/dashboard',
            'contentData' => ['links' => $links, 'date' => $date],
        ]);
    }
}
