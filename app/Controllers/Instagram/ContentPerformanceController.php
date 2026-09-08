<?php

namespace App\Controllers\Instagram;

use App\Controllers\BaseController;
use App\Models\IgAccountModel;
use App\Models\IgContentInsightDailyModel;

/**
 * Content Performance — per-post Instagram insight table built from the
 * daily snapshot tables (never a live Graph call, same as the dashboards).
 * Covers every synced post for the connected account, including ones
 * published through the Publish queue (ProcessPublishQueue links them into
 * ig_content on publish). Read-only; gated with the publish group so it
 * sits alongside the composer.
 */
class ContentPerformanceController extends BaseController
{
    public function index()
    {
        if (! in_array(session()->get('role_name'), ['admin', 'content_manager'], true)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }

        $from = $this->request->getGet('from') ?: date('Y-m-d', strtotime('-30 days'));
        $to   = $this->request->getGet('to') ?: date('Y-m-d');
        $sort = (string) $this->request->getGet('sort') ?: 'engagement';

        $account = (new IgAccountModel())->getActiveAccount();

        $rows    = [];
        $summary = ['posts' => 0, 'reach' => 0, 'engagement' => 0, 'eng_rate' => 0.0];

        if ($account) {
            $insightModel = new IgContentInsightDailyModel();
            $rows         = $insightModel->getContentTotalsInRange($account['id'], $from, $to);

            // Per-post daily engagement series -> tiny sparklines, keyed by content id.
            $series = [];
            foreach ($insightModel->getDailySeriesInRange($account['id'], $from, $to) as $point) {
                $series[(int) $point['ig_content_id']][] = (int) $point['engagement'];
            }

            foreach ($rows as &$row) {
                $reach          = (int) $row['reach'];
                $engagement     = (int) $row['likes'] + (int) $row['comments'] + (int) $row['saves'] + (int) $row['shares'];
                $row['engagement']   = $engagement;
                $row['eng_rate']     = $reach > 0 ? round($engagement / $reach * 100, 2) : 0.0;
                $row['spark']        = $series[(int) $row['id']] ?? [];

                $summary['reach']      += $reach;
                $summary['engagement'] += $engagement;
            }
            unset($row);

            $summary['posts']    = count($rows);
            $summary['eng_rate'] = $summary['reach'] > 0
                ? round($summary['engagement'] / $summary['reach'] * 100, 2)
                : 0.0;

            $rows = $this->sortRows($rows, $sort);
        }

        return view('layouts/main', [
            'title'       => 'Content Performance',
            'subtitle'    => 'Performa per-post Instagram dari snapshot harian.',
            'activeNav'   => 'ig-content-performance',
            'contentView' => 'instagram/content_performance',
            'contentData' => [
                'account' => $account,
                'from'    => $from,
                'to'      => $to,
                'sort'    => $sort,
                'rows'    => $rows,
                'summary' => $summary,
            ],
        ]);
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    private function sortRows(array $rows, string $sort): array
    {
        $key = match ($sort) {
            'reach'       => 'reach',
            'likes'       => 'likes',
            'comments'    => 'comments',
            'saves'       => 'saves',
            'eng_rate'    => 'eng_rate',
            'recent'      => 'posted_at',
            default       => 'engagement',
        };

        usort($rows, static function (array $a, array $b) use ($key): int {
            if ($key === 'posted_at') {
                return strcmp((string) $b['posted_at'], (string) $a['posted_at']);
            }

            return ($b[$key] ?? 0) <=> ($a[$key] ?? 0);
        });

        return $rows;
    }
}
