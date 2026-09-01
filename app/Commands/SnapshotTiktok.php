<?php

namespace App\Commands;

use App\Libraries\TikTok\ScrapeTikTokDataSource;
use App\Libraries\TikTok\TikTokTrackingService;
use App\Models\TiktokLinkModel;
use CodeIgniter\CLI\CLI;

class SnapshotTiktok extends BaseSnapshotCommand
{
    protected $name        = 'snapshot:tiktok';
    protected $description = 'Loops tiktok_link and fetches daily metrics. One link failing never stops the loop — errors are logged per-row.';

    protected function jobName(): string
    {
        return 'snapshot_tiktok';
    }

    protected function handleJob(array $params): array
    {
        $linkModel = new TiktokLinkModel();
        $links     = $linkModel->activeLinks();

        if ($links === []) {
            CLI::write('No active tiktok_link rows to snapshot.', 'yellow');

            return ['status' => 'success', 'error' => null, 'processed' => 0, 'failed' => 0];
        }

        // Volume is assumed low (sequential loop, not a queue) per the
        // build prompt's stated assumption — revisit if tiktok_link grows large.
        $trackingService = new TikTokTrackingService(new ScrapeTikTokDataSource());

        $processed = 0;
        $failed    = 0;

        foreach ($links as $link) {
            try {
                $trackingService->syncLink($link);
                $linkModel->touchLastSynced($link['id']);
                $processed++;
            } catch (\Throwable $e) {
                $failed++;
                CLI::error("Link #{$link['id']} ({$link['url']}) failed: {$e->getMessage()}");
                log_message('error', "[snapshot:tiktok] link #{$link['id']}: {$e}");
            }
        }

        $status = $failed === 0 ? 'success' : ($processed > 0 ? 'partial' : 'failed');

        return ['status' => $status, 'error' => null, 'processed' => $processed, 'failed' => $failed];
    }
}
