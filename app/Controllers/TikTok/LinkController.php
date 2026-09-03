<?php

namespace App\Controllers\TikTok;

use App\Controllers\BaseController;
use App\Libraries\AuditLogger;
use App\Libraries\TikTok\ScrapeTikTokDataSource;
use App\Libraries\TikTok\TikTokDataSourceException;
use App\Libraries\TikTok\TikTokTrackingService;
use App\Models\TiktokInsightDailyModel;
use App\Models\TiktokLinkModel;

class LinkController extends BaseController
{
    public function index()
    {
        $linkModel    = new TiktokLinkModel();
        $insightModel = new TiktokInsightDailyModel();

        $search   = trim((string) $this->request->getGet('q'));
        $dateFrom = (string) $this->request->getGet('date_from');
        $dateTo   = (string) $this->request->getGet('date_to');
        $segment  = (string) $this->request->getGet('segment');

        $builder = $linkModel->orderBy('created_at', 'DESC');

        if ($search !== '') {
            $builder->groupStart()
                ->like('creator_handle', $search)
                ->orLike('affiliate_note', $search)
                ->groupEnd();
        }

        if ($dateFrom !== '') {
            $builder->where('video_posted_at >=', $dateFrom . ' 00:00:00');
        }

        if ($dateTo !== '') {
            $builder->where('video_posted_at <=', $dateTo . ' 23:59:59');
        }

        if ($segment !== '' && in_array($segment, TiktokLinkModel::SEGMENTS, true)) {
            $builder->where('segment', $segment);
        }

        $links = $builder->findAll();

        foreach ($links as &$link) {
            $link['latest_insight'] = $insightModel->latestByLink($link['id']);
        }
        unset($link);

        return view('layouts/main', [
            'title'       => 'TikTok Tracking Modules',
            'subtitle'    => 'Monitor and orchestrate metrics for individual TikTok video links.',
            'activeNav'   => 'tiktok-links',
            'contentView' => 'tiktok/links_index',
            'contentData' => [
                'links'   => $links,
                'canEdit' => $this->canManageLinks(),
                'filters' => [
                    'q'         => $search,
                    'date_from' => $dateFrom,
                    'date_to'   => $dateTo,
                    'segment'   => $segment,
                ],
                'segments' => TiktokLinkModel::SEGMENTS,
                'summary' => $this->buildSummary($links),
            ],
        ]);
    }

    /**
     * Aggregates the (already filtered) links currently shown on the page —
     * so the summary reflects whatever search/date filter is active, not
     * the whole table. Engagement rate/share rate/save rate are computed
     * from totals (sum of engagement / sum of views), not averaged
     * per-link, which is the standard way to summarize multiple posts.
     */
    private function buildSummary(array $links): array
    {
        $totalViews    = 0;
        $totalLikes    = 0;
        $totalComments = 0;
        $totalShares   = 0;
        $totalSaves    = 0;
        $linksWithData       = 0;
        $totalBudget         = 0.0;
        $totalBudgetAfterTax = 0.0;

        foreach ($links as $link) {
            $insight = $link['latest_insight'];

            if ($insight) {
                $totalViews    += (int) $insight['views'];
                $totalLikes    += (int) $insight['likes'];
                $totalComments += (int) $insight['comments'];
                $totalShares   += (int) $insight['shares'];
                $totalSaves    += (int) $insight['saves'];
                $linksWithData++;
            }

            if ($link['budget'] !== null) {
                $budget = (float) $link['budget'];
                $totalBudget         += $budget;
                // Summed per-creator (not totalBudget * 0.975 in one shot),
                // so this stays correct if tax ever varies per creator.
                $totalBudgetAfterTax += $budget * (1 - 0.025);
            }
        }

        $totalEngagement = $totalLikes + $totalComments + $totalShares + $totalSaves;

        return [
            'total_views'            => $totalViews,
            'total_engagement'       => $totalEngagement,
            'total_likes'            => $totalLikes,
            'total_comments'         => $totalComments,
            'total_shares'           => $totalShares,
            'total_saves'            => $totalSaves,
            'avg_views'              => $linksWithData > 0 ? $totalViews / $linksWithData : 0,
            'engagement_rate'        => $totalViews > 0 ? ($totalEngagement / $totalViews) * 100 : 0,
            'share_rate'             => $totalViews > 0 ? ($totalShares / $totalViews) * 100 : 0,
            'save_rate'              => $totalViews > 0 ? ($totalSaves / $totalViews) * 100 : 0,
            'total_budget'           => $totalBudget,
            'total_budget_after_tax' => $totalBudgetAfterTax,
        ];
    }

    public function new()
    {
        $this->requireCanManageLinks();

        return view('layouts/main', [
            'title'       => 'Tambah TikTok Link',
            'activeNav'   => 'tiktok-links',
            'contentView' => 'tiktok/link_form',
            'contentData' => [
                'segments' => TiktokLinkModel::SEGMENTS,
                'genders'  => TiktokLinkModel::GENDERS,
            ],
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
            'segment'         => $this->request->getPost('segment') ?: null,
            'gender'          => $this->request->getPost('gender') ?: null,
            'budget'          => $this->request->getPost('budget') !== '' ? $this->request->getPost('budget') : null,
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

        $successMessage = 'Link TikTok berhasil ditambahkan.';

        try {
            $link = $linkModel->find($linkId);
            (new TikTokTrackingService(new ScrapeTikTokDataSource()))->syncLink($link);
            $linkModel->touchLastSynced($linkId);
            $successMessage .= ' Metrics awal berhasil diambil.';
        } catch (TikTokDataSourceException $e) {
            log_message('error', "[tiktok/links/create] scrape gagal untuk link #{$linkId}: {$e}");
            $successMessage .= ' Namun scrape metrics gagal: ' . $e->getMessage();
        }

        session()->setFlashdata('success', $successMessage);

        return redirect()->to('/tiktok/links');
    }

    public function refreshAll()
    {
        $this->requireCanManageLinks();

        $linkModel = new TiktokLinkModel();
        $links     = $linkModel->findAll();

        if ($links === []) {
            session()->setFlashdata('error', 'Belum ada link TikTok untuk di-refresh.');

            return redirect()->to('/tiktok/links');
        }

        $trackingService = new TikTokTrackingService(new ScrapeTikTokDataSource());
        $processed        = 0;
        $failed           = 0;

        foreach ($links as $link) {
            try {
                $trackingService->syncLink($link);
                $linkModel->touchLastSynced($link['id']);
                $processed++;
            } catch (TikTokDataSourceException $e) {
                $failed++;
                log_message('error', "[tiktok/links/refresh-all] link #{$link['id']}: {$e}");
            }
        }

        (new AuditLogger())->log(session()->get('user_id'), 'refresh_all_tiktok_links', 'tiktok_link', null, ['processed' => $processed, 'failed' => $failed]);

        if ($failed === 0) {
            session()->setFlashdata('success', "Metrics berhasil diperbarui untuk semua {$processed} link.");
        } elseif ($processed > 0) {
            session()->setFlashdata('success', "Metrics diperbarui: {$processed} berhasil, {$failed} gagal (lihat log untuk detail).");
        } else {
            session()->setFlashdata('error', "Semua {$failed} link gagal di-refresh (lihat log untuk detail).");
        }

        return redirect()->to('/tiktok/links');
    }

    public function delete(int $id)
    {
        $this->requireCanManageLinks();

        $linkModel = new TiktokLinkModel();
        $link      = $linkModel->find($id);

        if (! $link) {
            session()->setFlashdata('error', 'Link tidak ditemukan.');

            return redirect()->to('/tiktok/links');
        }

        // tiktok_insight_daily rows for this link cascade-delete via FK.
        $linkModel->delete($id);

        (new AuditLogger())->log(session()->get('user_id'), 'delete_tiktok_link', 'tiktok_link', $id, ['url' => $link['url']]);

        session()->setFlashdata('success', 'Link TikTok berhasil dihapus.');

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
