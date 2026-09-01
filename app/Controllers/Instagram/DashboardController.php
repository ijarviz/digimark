<?php

namespace App\Controllers\Instagram;

use App\Controllers\BaseController;
use App\Models\IgAccountModel;
use App\Models\IgAdsInsightDailyModel;
use App\Models\IgContentInsightDailyModel;
use App\Models\IgProfileInsightDailyModel;

class DashboardController extends BaseController
{
    public function index()
    {
        [$from, $to] = $this->resolveDateRange();

        return view('layouts/main', [
            'title'       => 'Instagram Insight',
            'activeNav'   => 'ig-dashboard',
            'contentView' => 'instagram/dashboard',
            'contentData' => ['from' => $from, 'to' => $to],
            'extraBodyScripts' => [
                'https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js',
                base_url('assets/js/instagram-dashboard.js'),
            ],
        ]);
    }

    public function chartData()
    {
        [$from, $to] = $this->resolveDateRange();

        $account = (new IgAccountModel())->getActiveAccount();

        if (! $account) {
            return $this->response->setJSON(['profile' => [], 'content' => [], 'ads' => []]);
        }

        $profile = (new IgProfileInsightDailyModel())->getSeriesInRange($account['id'], $from, $to);
        $content = (new IgContentInsightDailyModel())->getContentTotalsInRange($account['id'], $from, $to);
        $ads     = (new IgAdsInsightDailyModel())->getCampaignsInRange($from, $to);

        return $this->response->setJSON(['profile' => $profile, 'content' => $content, 'ads' => $ads]);
    }

    /**
     * @return array{0: string, 1: string} [from, to] as Y-m-d, defaulting to the last 30 days.
     */
    private function resolveDateRange(): array
    {
        $from = $this->request->getGet('from') ?: date('Y-m-d', strtotime('-30 days'));
        $to   = $this->request->getGet('to') ?: date('Y-m-d');

        return [$from, $to];
    }
}
