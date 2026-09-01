<?php

namespace App\Commands;

use App\Libraries\AuditLogger;
use App\Libraries\Instagram\InstagramTokenService;
use App\Libraries\Instagram\PublishEngineService;
use App\Models\IgAccountModel;
use App\Models\IgContentModel;
use App\Models\PublishQueueModel;
use CodeIgniter\CLI\CLI;

class ProcessPublishQueue extends BaseSnapshotCommand
{
    protected $name        = 'process:publish-queue';
    protected $description = 'Processes publish_queue sequentially: resumes in-flight containers, creates new ones for due items, enforces the daily rate limit.';

    /** Instagram's documented organic publish limit is ~25 posts / 24h per account. */
    private const MAX_PUBLISHES_PER_DAY = 25;

    /** Stuck-container safety valve — tune once real video processing times are known. */
    private const CONTAINER_TIMEOUT_MINUTES = 30;

    protected function jobName(): string
    {
        return 'process_publish_queue';
    }

    protected function handleJob(array $params): array
    {
        $account = (new IgAccountModel())->getActiveAccount();

        if (! $account) {
            CLI::error('No IG account connected — nothing to process.');

            return ['status' => 'failed', 'error' => 'No IG account connected', 'processed' => 0, 'failed' => 0];
        }

        $accessToken = (new InstagramTokenService())->decrypt($account['access_token_encrypted']);
        $engine      = new PublishEngineService();
        $queueModel  = new PublishQueueModel();

        $processed = 0;
        $failed    = 0;
        $deferred  = 0;

        // 1. Resume in-flight containers first — never create a duplicate
        // container for an item that's already mid-processing.
        foreach ($queueModel->processingItems() as $item) {
            [$processed, $failed] = $this->resumeProcessing($item, $engine, $queueModel, $account['ig_business_id'], $accessToken, $processed, $failed);
        }

        // 2. Rate-limited creation of new containers for due pending items.
        $todayCount = $queueModel->countPublishedToday();

        foreach ($queueModel->duePendingItems() as $item) {
            if ($todayCount >= self::MAX_PUBLISHES_PER_DAY) {
                $queueModel->deferToNextDay($item['id']);
                (new AuditLogger())->log(null, 'publish_deferred_rate_limit', 'publish_queue', $item['id'], [
                    'reason' => 'Daily publish limit (' . self::MAX_PUBLISHES_PER_DAY . ') reached',
                ]);
                $deferred++;

                continue;
            }

            try {
                $creationId = $engine->createContainer($item, $account['ig_business_id'], $accessToken);
                $queueModel->markProcessing($item['id'], $creationId);
                $todayCount++;
                $processed++;
            } catch (\Throwable $e) {
                $queueModel->markFailed($item['id'], $e->getMessage());
                $failed++;
                CLI::error("Item #{$item['id']} container creation failed: {$e->getMessage()}");
                log_message('error', "[process:publish-queue] item #{$item['id']}: {$e}");
            }
        }

        if ($deferred > 0) {
            CLI::write("{$deferred} item(s) deferred to next day due to daily publish limit.", 'yellow');
        }

        $status = ($failed === 0 && $deferred === 0) ? 'success' : (($processed > 0) ? 'partial' : 'failed');

        return [
            'status'    => $status,
            'error'     => $deferred > 0 ? "{$deferred} item(s) deferred due to daily publish limit" : null,
            'processed' => $processed,
            'failed'    => $failed,
        ];
    }

    /**
     * @return array{0:int,1:int} [processed, failed] tallies, updated
     */
    private function resumeProcessing(array $item, PublishEngineService $engine, PublishQueueModel $queueModel, string $igBusinessId, string $accessToken, int $processed, int $failed): array
    {
        try {
            $poll = $engine->pollStatus($item['container_id'], $accessToken);

            if ($poll['status_code'] === 'FINISHED') {
                $igMediaId = $engine->publish($item['container_id'], $igBusinessId, $accessToken);
                $queueModel->markPublished($item['id'], $igMediaId);
                $this->linkToContentTracking($item, $igMediaId);
                $processed++;

                return [$processed, $failed];
            }

            if (in_array($poll['status_code'], ['ERROR', 'EXPIRED'], true)) {
                $queueModel->markFailed($item['id'], 'Container status: ' . $poll['status_code']);
                $failed++;

                return [$processed, $failed];
            }

            // Still IN_PROGRESS — check for a stuck timeout rather than polling forever.
            $startedAt = strtotime($item['processing_started_at']);
            $ageMinutes = ($startedAt !== false) ? (time() - $startedAt) / 60 : 0;

            if ($ageMinutes > self::CONTAINER_TIMEOUT_MINUTES) {
                $queueModel->markFailed($item['id'], 'Container processing timeout after ' . self::CONTAINER_TIMEOUT_MINUTES . ' minutes (status_code=' . $poll['status_code'] . ')');
                $failed++;
            }
            // Otherwise: leave as-is, picked up again on the next cron run.
        } catch (\Throwable $e) {
            $queueModel->markFailed($item['id'], $e->getMessage());
            $failed++;
            CLI::error("Item #{$item['id']} polling/publish failed: {$e->getMessage()}");
            log_message('error', "[process:publish-queue] item #{$item['id']}: {$e}");
        }

        return [$processed, $failed];
    }

    /**
     * Stories are intentionally NOT linked — Instagram's regular media
     * listing endpoint (what snapshot:ig-content uses) does not return
     * Stories, and they expire in 24h, so a linked ig_content row here
     * would never get insight snapshots. See PublishEngineService's class
     * doc and the Fase 3 plan for the full rationale.
     */
    private function linkToContentTracking(array $item, string $igMediaId): void
    {
        if ($item['media_type'] === 'story') {
            return;
        }

        (new IgContentModel())->upsertByMediaId([
            'ig_account_id' => (new IgAccountModel())->getActiveAccount()['id'],
            'ig_media_id'   => $igMediaId,
            'media_type'    => $item['media_type'],
            'caption'       => $item['caption'],
            'permalink'     => null,
            'thumbnail_url' => null,
            'posted_at'     => date('Y-m-d H:i:s'),
            'synced_at'     => date('Y-m-d H:i:s'),
        ]);
    }
}
