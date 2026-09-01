<?php

namespace App\Commands;

use App\Libraries\Instagram\InstagramAdsApiService;
use App\Libraries\Instagram\InstagramTokenService;
use App\Models\IgAccountModel;
use App\Models\IgAdsInsightDailyModel;
use App\Models\IgContentModel;
use CodeIgniter\CLI\CLI;

class SnapshotIgAds extends BaseSnapshotCommand
{
    protected $name        = 'snapshot:ig-ads';
    protected $description = 'Fetches daily per-campaign ads insights (reach/impressions/spend), upserting into ig_ads_insight_daily.';

    protected function jobName(): string
    {
        return 'snapshot_ig_ads';
    }

    protected function handleJob(array $params): array
    {
        $account = (new IgAccountModel())->getActiveAccount();

        if (! $account) {
            CLI::error('No IG account connected — nothing to snapshot.');

            return ['status' => 'failed', 'error' => 'No IG account connected', 'processed' => 0, 'failed' => 0];
        }

        if (empty($account['ad_account_id'])) {
            CLI::error('No ad_account_id configured — connect an Ads Account first (Admin > IG Account).');

            return ['status' => 'failed', 'error' => 'No ad_account_id configured', 'processed' => 0, 'failed' => 0];
        }

        $accessToken   = (new InstagramTokenService())->decrypt($account['access_token_encrypted']);
        $adsApi        = new InstagramAdsApiService();
        $insightModel  = new IgAdsInsightDailyModel();
        $contentModel  = new IgContentModel();
        $today         = date('Y-m-d');

        try {
            $campaigns = $adsApi->listCampaignInsights($account['ad_account_id'], $accessToken, $today);
        } catch (\Throwable $e) {
            CLI::error('Failed to list campaign insights: ' . $e->getMessage());

            return ['status' => 'failed', 'error' => $e->getMessage(), 'processed' => 0, 'failed' => 0];
        }

        $processed = 0;
        $failed    = 0;

        foreach ($campaigns as $campaign) {
            try {
                $igContentId = null;

                try {
                    $mediaId = $adsApi->resolveIgMediaId($campaign['campaign_id'], $accessToken);
                    $igContentId = $mediaId ? ($contentModel->findByMediaId($mediaId)['id'] ?? null) : null;
                } catch (\Throwable $e) {
                    // Best-effort mapping only — never blocks the insight row itself.
                    log_message('warning', "[snapshot:ig-ads] could not resolve ig_content for campaign {$campaign['campaign_id']}: {$e->getMessage()}");
                }

                $insightModel->upsertDaily([
                    'ig_content_id'  => $igContentId,
                    'campaign_id'    => $campaign['campaign_id'],
                    'campaign_name'  => $campaign['campaign_name'],
                    'snapshot_date'  => $today,
                    'ad_reach'       => $campaign['ad_reach'],
                    'ad_impressions' => $campaign['ad_impressions'],
                    'spend'          => $campaign['spend'],
                ]);

                $processed++;
            } catch (\Throwable $e) {
                $failed++;
                CLI::error("Failed campaign {$campaign['campaign_id']}: {$e->getMessage()}");
                log_message('error', "[snapshot:ig-ads] campaign {$campaign['campaign_id']}: {$e}");
            }
        }

        $status = $failed === 0 ? 'success' : ($processed > 0 ? 'partial' : 'failed');

        return ['status' => $status, 'error' => null, 'processed' => $processed, 'failed' => $failed];
    }
}
