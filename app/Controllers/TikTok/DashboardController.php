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

        $urlQuery     = trim((string) $this->request->getGet('url'));
        $creatorQuery = trim((string) $this->request->getGet('creator'));
        $source       = (string) $this->request->getGet('source');

        $minViews    = $this->parseMinFilter((string) $this->request->getGet('min_views'));
        $minLikes    = $this->parseMinFilter((string) $this->request->getGet('min_likes'));
        $minComments = $this->parseMinFilter((string) $this->request->getGet('min_comments'));
        $minShares   = $this->parseMinFilter((string) $this->request->getGet('min_shares'));
        $minSaves    = $this->parseMinFilter((string) $this->request->getGet('min_saves'));

        $linkModel    = new TiktokLinkModel();
        $insightModel = new TiktokInsightDailyModel();

        $builder = $linkModel->orderBy('created_at', 'DESC');

        if ($urlQuery !== '') {
            $builder->like('url', $urlQuery);
        }

        if ($creatorQuery !== '') {
            $builder->like('creator_handle', $creatorQuery);
        }

        if ($source !== '' && in_array($source, ['oauth', 'scrape'], true)) {
            $builder->where('data_source', $source);
        }

        $links = $builder->findAll();

        foreach ($links as &$link) {
            $link['insight'] = $insightModel->forLinkAndDate($link['id'], $date)
                ?? $insightModel->latestByLink($link['id']);
        }
        unset($link);

        $links = $this->applyMinFilters($links, [
            'views'    => $minViews,
            'likes'    => $minLikes,
            'comments' => $minComments,
            'shares'   => $minShares,
            'saves'    => $minSaves,
        ]);

        return view('layouts/main', [
            'title'       => 'TikTok Metrics',
            'subtitle'    => 'Ringkasan performa harian untuk setiap link TikTok yang dilacak.',
            'activeNav'   => 'tiktok-dashboard',
            'contentView' => 'tiktok/dashboard',
            'contentData' => [
                'links'   => $links,
                'date'    => $date,
                'filters' => [
                    'url'          => $urlQuery,
                    'creator'      => $creatorQuery,
                    'source'       => $source,
                    'min_views'    => $minViews,
                    'min_likes'    => $minLikes,
                    'min_comments' => $minComments,
                    'min_shares'   => $minShares,
                    'min_saves'    => $minSaves,
                ],
            ],
        ]);
    }

    /**
     * Empty/non-numeric input means "no filter", not zero — otherwise a
     * blank field would hide every row with a genuine zero metric.
     */
    private function parseMinFilter(string $value): ?int
    {
        if (trim($value) === '' || ! is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    /**
     * Applied in PHP, not SQL: each link's insight comes from a separate
     * date-keyed lookup above (forLinkAndDate/latestByLink), not a join, so
     * a link with no insight row never satisfies a minimum threshold.
     */
    private function applyMinFilters(array $links, array $minimums): array
    {
        $minimums = array_filter($minimums, static fn ($min) => $min !== null);

        if ($minimums === []) {
            return $links;
        }

        return array_values(array_filter($links, static function (array $link) use ($minimums) {
            foreach ($minimums as $field => $min) {
                $value = $link['insight'][$field] ?? null;

                if ($value === null || (int) $value < $min) {
                    return false;
                }
            }

            return true;
        }));
    }
}
