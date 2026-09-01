<?php

namespace App\Commands;

use App\Libraries\Instagram\InstagramTokenService;
use App\Models\IgAccountModel;
use App\Models\IgContentInsightDailyModel;
use App\Models\IgContentModel;
use App\Models\IgProfileInsightDailyModel;
use App\Models\TiktokInsightDailyModel;
use App\Models\TiktokLinkModel;
use App\Models\UserModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Populates fake IG account/content/insight rows and a couple of TikTok
 * links so the dashboards can be visually verified without live Meta/TikTok
 * API credentials. Local dev only — refuses to run outside `development`.
 */
class DevSeedFakeData extends BaseCommand
{
    protected $group       = 'dev';
    protected $name        = 'dev:seed-fake-data';
    protected $description = 'Seeds fake IG/TikTok data for local dashboard testing (development environment only).';

    private const MEDIA_TYPES = ['image', 'video', 'carousel', 'reels'];

    public function run(array $params)
    {
        if (ENVIRONMENT !== 'development') {
            CLI::error('dev:seed-fake-data can only run when CI_ENVIRONMENT=development.');

            return;
        }

        $accountId = $this->seedIgAccount();
        $this->seedProfileInsights($accountId);
        $this->seedContentAndInsights($accountId);
        $this->seedTiktokLinks();

        CLI::write('Fake data seeded successfully.', 'green');
    }

    private function seedIgAccount(): int
    {
        $accountModel = new IgAccountModel();
        $existing     = $accountModel->getActiveAccount();

        if ($existing) {
            CLI::write('ig_account already has a row — reusing id ' . $existing['id'], 'yellow');

            return $existing['id'];
        }

        $adminId = (new UserModel())->where('role_id !=', null)->first()['id'] ?? null;

        $id = $accountModel->insert([
            'ig_business_id'         => 'fake_ig_business_id_123',
            'ig_username'            => 'fakebrand',
            'fb_page_id'             => 'fake_fb_page_id_456',
            'access_token_encrypted' => (new InstagramTokenService())->encrypt('fake-dev-token'),
            'token_expires_at'       => date('Y-m-d H:i:s', strtotime('+60 days')),
            'connected_by'           => $adminId,
            'connected_at'           => date('Y-m-d H:i:s'),
        ]);

        CLI::write("Seeded fake ig_account #{$id}", 'green');

        return $id;
    }

    private function seedProfileInsights(int $accountId): void
    {
        $model    = new IgProfileInsightDailyModel();
        $follower = 5000;

        for ($i = 30; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $follower += random_int(-5, 40);

            $model->upsertDaily([
                'ig_account_id'  => $accountId,
                'snapshot_date'  => $date,
                'follower_count' => $follower,
                'profile_visits' => random_int(20, 300),
                'reach'          => random_int(500, 5000),
                'impressions'    => random_int(800, 8000),
            ]);
        }

        CLI::write('Seeded 31 days of ig_profile_insight_daily.', 'green');
    }

    private function seedContentAndInsights(int $accountId): void
    {
        $contentModel = new IgContentModel();
        $insightModel = new IgContentInsightDailyModel();

        for ($c = 1; $c <= 10; $c++) {
            $postedAt  = date('Y-m-d H:i:s', strtotime('-' . random_int(1, 29) . ' days'));
            $mediaType = self::MEDIA_TYPES[array_rand(self::MEDIA_TYPES)];
            $isVideo   = in_array($mediaType, ['video', 'reels'], true);

            $contentId = $contentModel->upsertByMediaId([
                'ig_account_id' => $accountId,
                'ig_media_id'   => "fake_media_{$c}",
                'media_type'    => $mediaType,
                'caption'       => "Fake caption for post #{$c} — lorem ipsum dolor sit amet.",
                'permalink'     => "https://instagram.com/p/fake{$c}",
                'thumbnail_url' => null,
                'posted_at'     => $postedAt,
                'synced_at'     => date('Y-m-d H:i:s'),
            ]);

            // A couple of daily snapshots per content, simulating repeated cron runs.
            foreach ([0, 1, 2] as $daysAfterPost) {
                $snapshotDate = date('Y-m-d', strtotime($postedAt . " +{$daysAfterPost} days"));

                if (strtotime($snapshotDate) > time()) {
                    continue;
                }

                $insightModel->upsertDaily([
                    'ig_content_id' => $contentId,
                    'snapshot_date' => $snapshotDate,
                    'reach'         => random_int(200, 4000),
                    'impressions'   => random_int(300, 6000),
                    'likes'         => random_int(10, 500),
                    'comments'      => random_int(0, 60),
                    'shares'        => random_int(0, 40),
                    'saves'         => random_int(0, 80),
                    'plays'         => $isVideo ? random_int(100, 3000) : null,
                ]);
            }
        }

        CLI::write('Seeded 10 fake ig_content rows with daily insights.', 'green');
    }

    private function seedTiktokLinks(): void
    {
        $linkModel    = new TiktokLinkModel();
        $insightModel = new TiktokInsightDailyModel();

        $fakeLinks = [
            ['url' => 'https://www.tiktok.com/@fakecreator1/video/1111111111', 'creator_handle' => '@fakecreator1', 'note' => 'Campaign A', 'segment' => 'micro', 'gender' => 'female', 'budget' => 1500000],
            ['url' => 'https://www.tiktok.com/@fakecreator2/video/2222222222', 'creator_handle' => '@fakecreator2', 'note' => 'Campaign B', 'segment' => 'macro', 'gender' => 'all', 'budget' => 5000000],
        ];

        foreach ($fakeLinks as $fake) {
            $existing = $linkModel->where('url', $fake['url'])->first();

            $linkId = $existing['id'] ?? $linkModel->insert([
                'url'            => $fake['url'],
                'creator_handle' => $fake['creator_handle'],
                'affiliate_note' => $fake['note'],
                'segment'        => $fake['segment'],
                'gender'         => $fake['gender'],
                'budget'         => $fake['budget'],
                'data_source'    => 'scrape',
                'added_by'       => null,
                'created_at'     => date('Y-m-d H:i:s'),
            ]);

            for ($i = 3; $i >= 0; $i--) {
                $insightModel->upsertDaily([
                    'tiktok_link_id' => $linkId,
                    'snapshot_date'  => date('Y-m-d', strtotime("-{$i} days")),
                    'views'          => random_int(1000, 50000),
                    'likes'          => random_int(50, 3000),
                    'comments'       => random_int(0, 400),
                    'shares'         => random_int(0, 200),
                    'saves'          => random_int(0, 300),
                    'source'         => 'scrape',
                ]);
            }
        }

        CLI::write('Seeded 2 fake tiktok_link rows with 4 days of insights.', 'green');
    }
}
