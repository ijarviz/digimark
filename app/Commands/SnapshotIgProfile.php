<?php

namespace App\Commands;

use App\Libraries\Instagram\InstagramApiService;
use App\Libraries\Instagram\InstagramTokenService;
use App\Models\IgAccountModel;
use App\Models\IgProfileInsightDailyModel;
use CodeIgniter\CLI\CLI;

class SnapshotIgProfile extends BaseSnapshotCommand
{
    protected $name        = 'snapshot:ig-profile';
    protected $description = 'Fetches daily follower_count / profile_visits / reach / impressions, upserting into ig_profile_insight_daily.';

    protected function jobName(): string
    {
        return 'snapshot_ig_profile';
    }

    protected function handleJob(array $params): array
    {
        $account = (new IgAccountModel())->getActiveAccount();

        if (! $account) {
            CLI::error('No IG account connected — nothing to snapshot.');

            return ['status' => 'failed', 'error' => 'No IG account connected', 'processed' => 0, 'failed' => 0];
        }

        $accessToken = (new InstagramTokenService())->decrypt($account['access_token_encrypted']);

        try {
            $insights = (new InstagramApiService())->getProfileInsights($account['ig_business_id'], $accessToken);

            (new IgProfileInsightDailyModel())->upsertDaily([
                'ig_account_id'  => $account['id'],
                'snapshot_date'  => date('Y-m-d'),
                'follower_count' => $insights['follower_count'],
                'profile_visits' => $insights['profile_visits'],
                'reach'          => $insights['reach'],
                'impressions'    => $insights['impressions'],
            ]);

            return ['status' => 'success', 'error' => null, 'processed' => 1, 'failed' => 0];
        } catch (\Throwable $e) {
            CLI::error('Failed to fetch profile insights: ' . $e->getMessage());

            return ['status' => 'failed', 'error' => $e->getMessage(), 'processed' => 0, 'failed' => 1];
        }
    }
}
