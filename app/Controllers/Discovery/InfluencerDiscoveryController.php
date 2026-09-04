<?php

namespace App\Controllers\Discovery;

use App\Controllers\BaseController;
use App\Libraries\AuditLogger;
use App\Libraries\Discovery\ApifyDiscoveryService;
use App\Libraries\Discovery\DiscoveryException;
use App\Models\TiktokLinkModel;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Influencer Discovery — search Instagram/TikTok by keyword via Apify and
 * optionally save a found TikTok profile into tiktok_link. Viewable by all
 * three roles (like the dashboards); saving is admin/content_manager only,
 * matching TikTok\LinkController's split.
 */
class InfluencerDiscoveryController extends BaseController
{
    public function index()
    {
        $keyword  = trim((string) $this->request->getGet('q'));
        $platform = (string) $this->request->getGet('platform') ?: 'all';
        $errors   = [];
        $results  = [];

        if ($keyword !== '') {
            $service = new ApifyDiscoveryService();

            // One platform failing (Apify down, bad token, rate limit)
            // never blocks the other — same per-source tolerance as
            // snapshot:tiktok's per-link try/catch.
            if ($platform === 'all' || $platform === 'instagram') {
                try {
                    $results = array_merge($results, $service->searchInstagram($keyword));
                } catch (DiscoveryException $e) {
                    $errors[] = 'Instagram: ' . $e->getMessage();
                }
            }

            if ($platform === 'all' || $platform === 'tiktok') {
                try {
                    $results = array_merge($results, $service->searchTikTok($keyword));
                } catch (DiscoveryException $e) {
                    $errors[] = 'TikTok: ' . $e->getMessage();
                }
            }

            usort($results, static fn (array $a, array $b): int => ($b['followers'] ?? 0) <=> ($a['followers'] ?? 0));

            if ($results !== []) {
                $existingUrls = (new TiktokLinkModel())
                    ->whereIn('url', array_column($results, 'profile_url'))
                    ->findColumn('url') ?? [];

                foreach ($results as &$result) {
                    $result['already_tracked'] = in_array($result['profile_url'], $existingUrls, true);
                }
                unset($result);
            }
        }

        return view('layouts/main', [
            'title'       => 'Influencer Discovery',
            'subtitle'    => 'Cari akun influencer baru di Instagram & TikTok berdasarkan keyword/niche.',
            'activeNav'   => 'discovery-influencers',
            'contentView' => 'discovery/influencers_index',
            'contentData' => [
                'keyword'  => $keyword,
                'platform' => $platform,
                'results'  => $results,
                'errors'   => $errors,
                'canSave'  => $this->canManageLinks(),
            ],
            'extraBodyScripts' => [
                base_url('assets/js/influencer-discovery.js'),
            ],
        ]);
    }

    /**
     * AJAX endpoint (mirrors TikTok\LinkController::refreshOne) — saves one
     * discovered account into tiktok_link, tagged discovery_source=apify.
     * Only tiktok.com URLs pass TiktokLinkModel's validation, so only
     * TikTok results can land here; Instagram has no equivalent table yet.
     * Saved inactive: this is a profile URL, not a video URL, so
     * snapshot:tiktok/refresh-one have nothing to scrape until someone
     * edits in a real video link and activates it.
     */
    public function save()
    {
        $this->requireCanManageLinks();

        $platform   = (string) $this->request->getPost('platform');
        $profileUrl = (string) $this->request->getPost('profile_url');
        $handle     = (string) $this->request->getPost('handle');
        $followers  = $this->request->getPost('followers');

        if ($platform !== 'tiktok') {
            return $this->response->setJSON([
                'success'    => false,
                'message'    => 'Hanya profil TikTok yang bisa disimpan langsung ke tracking saat ini.',
                'csrf_token' => csrf_hash(),
            ]);
        }

        $linkModel = new TiktokLinkModel();

        if ($linkModel->where('url', $profileUrl)->first()) {
            return $this->response->setJSON([
                'success'    => false,
                'message'    => 'Link ini sudah ada di TikTok Links.',
                'csrf_token' => csrf_hash(),
            ]);
        }

        $note = 'Ditemukan via Apify Discovery';
        if ($followers !== null && $followers !== '') {
            $note .= ' — ~' . number_format((int) $followers, 0, ',', '.') . ' followers saat ditemukan';
        }

        $data = [
            'url'              => $profileUrl,
            'creator_handle'   => $handle ?: null,
            'affiliate_note'   => $note,
            'data_source'      => 'scrape',
            'discovery_source' => 'apify',
            'is_active'        => 0,
            'added_by'         => session()->get('user_id'),
            'created_at'       => date('Y-m-d H:i:s'),
        ];

        if (! $linkModel->validate($data)) {
            return $this->response->setJSON([
                'success'    => false,
                'message'    => implode(' ', $linkModel->errors()),
                'csrf_token' => csrf_hash(),
            ]);
        }

        $id = $linkModel->insert($data);

        (new AuditLogger())->log(
            session()->get('user_id'),
            'discover_save_tiktok_link',
            'tiktok_link',
            $id,
            ['source' => 'apify', 'handle' => $handle]
        );

        return $this->response->setJSON([
            'success'    => true,
            'message'    => 'Tersimpan ke TikTok Links (nonaktif — tambahkan link video lalu aktifkan untuk mulai tracking).',
            'csrf_token' => csrf_hash(),
        ]);
    }

    private function canManageLinks(): bool
    {
        return in_array(session()->get('role_name'), ['admin', 'content_manager'], true);
    }

    private function requireCanManageLinks(): void
    {
        if (! $this->canManageLinks()) {
            throw new PageNotFoundException();
        }
    }
}
