<?php

namespace App\Commands;

use App\Libraries\Instagram\InstagramApiService;
use App\Libraries\Instagram\InstagramTokenService;
use App\Models\IgAccountModel;
use App\Models\IgContentInsightDailyModel;
use App\Models\IgContentModel;
use CodeIgniter\CLI\CLI;

class SnapshotIgContent extends BaseSnapshotCommand
{
    protected $name        = 'snapshot:ig-content';
    protected $description = 'Fetches recent IG media and daily per-media insights, upserting into ig_content / ig_content_insight_daily.';

    protected function jobName(): string
    {
        return 'snapshot_ig_content';
    }

    protected function handleJob(array $params): array
    {
        $account = (new IgAccountModel())->getActiveAccount();

        if (! $account) {
            CLI::error('No IG account connected — nothing to snapshot.');

            return ['status' => 'failed', 'error' => 'No IG account connected', 'processed' => 0, 'failed' => 0];
        }

        $accessToken = (new InstagramTokenService())->decrypt($account['access_token_encrypted']);
        $api         = new InstagramApiService();
        $contentModel        = new IgContentModel();
        $insightDailyModel   = new IgContentInsightDailyModel();
        $today                = date('Y-m-d');

        $media     = $api->listRecentMedia($account['ig_business_id'], $accessToken);
        $processed = 0;
        $failed    = 0;

        foreach ($media as $item) {
            try {
                $contentId = $contentModel->upsertByMediaId([
                    'ig_account_id' => $account['id'],
                    'ig_media_id'   => $item['id'],
                    'media_type'    => strtolower($item['media_type']),
                    'caption'       => $item['caption'] ?? null,
                    'permalink'     => $item['permalink'] ?? null,
                    'thumbnail_url' => $item['thumbnail_url'] ?? null,
                    'posted_at'     => date('Y-m-d H:i:s', strtotime($item['timestamp'])),
                    'synced_at'     => date('Y-m-d H:i:s'),
                ]);

                $insights = $api->getMediaInsights($item['id'], $item['media_type'], $accessToken);

                $insightDailyModel->upsertDaily([
                    'ig_content_id' => $contentId,
                    'snapshot_date' => $today,
                    'reach'         => $insights['reach'],
                    'impressions'   => $insights['impressions'],
                    'likes'         => $item['like_count'] ?? 0,
                    'comments'      => $item['comments_count'] ?? 0,
                    'shares'        => $insights['shares'],
                    'saves'         => $insights['saves'],
                    'plays'         => $insights['plays'],
                ]);

                $processed++;
            } catch (\Throwable $e) {
                $failed++;
                CLI::error("Failed media {$item['id']}: {$e->getMessage()}");
                log_message('error', "[snapshot:ig-content] media {$item['id']}: {$e}");
            }
        }

        $status = $failed === 0 ? 'success' : ($processed > 0 ? 'partial' : 'failed');

        return ['status' => $status, 'error' => null, 'processed' => $processed, 'failed' => $failed];
    }
}
