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

        $account = (new IgAccountModel())->getActiveAccount();

        return view('layouts/main', [
            'title'       => 'Instagram Content Insights',
            'subtitle'    => 'Analyze cross-platform performance metrics and content telemetry.',
            'activeNav'   => 'ig-dashboard',
            'contentView' => 'instagram/dashboard',
            'contentData' => ['from' => $from, 'to' => $to, 'account' => $account],
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

        $profileModel = new IgProfileInsightDailyModel();
        $profile      = $profileModel->getSeriesInRange($account['id'], $from, $to);
        $content      = (new IgContentInsightDailyModel())->getContentTotalsInRange($account['id'], $from, $to);
        $ads          = (new IgAdsInsightDailyModel())->getCampaignsInRange($from, $to);
        $trends       = $this->computeTrends($profileModel, $account['id'], $from, $to, $profile);

        return $this->response->setJSON(['profile' => $profile, 'content' => $content, 'ads' => $ads, 'trends' => $trends]);
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

    /**
     * Real period-over-period % change (current range vs. the immediately
     * preceding range of equal length) for follower count and total reach —
     * no fabricated sparklines, just an actual comparison against data we have.
     *
     * @return array{follower: array{change: float|null}, reach: array{change: float|null}}
     */
    private function computeTrends(IgProfileInsightDailyModel $profileModel, int $accountId, string $from, string $to, array $currentProfile): array
    {
        $rangeDays = (strtotime($to) - strtotime($from)) / 86400 + 1;
        $prevTo    = date('Y-m-d', strtotime($from . ' -1 day'));
        $prevFrom  = date('Y-m-d', strtotime($prevTo . ' -' . ($rangeDays - 1) . ' days'));

        $previousProfile = $profileModel->getSeriesInRange($accountId, $prevFrom, $prevTo);

        $currentFollower  = $currentProfile ? (int) end($currentProfile)['follower_count'] : null;
        $previousFollower = $previousProfile ? (int) end($previousProfile)['follower_count'] : null;

        $currentReach  = array_sum(array_column($currentProfile, 'reach'));
        $previousReach = array_sum(array_column($previousProfile, 'reach'));

        return [
            'follower' => ['change' => $this->percentChange($previousFollower, $currentFollower)],
            'reach'    => ['change' => $this->percentChange($previousReach, $currentReach)],
        ];
    }

    private function percentChange(?float $previous, ?float $current): ?float
    {
        if ($previous === null || $current === null || $previous == 0.0) {
            return null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
