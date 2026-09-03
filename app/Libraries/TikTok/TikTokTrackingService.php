<?php

namespace App\Libraries\TikTok;

use App\Models\TiktokInsightDailyModel;
use App\Models\TiktokLinkModel;

/**
 * Orchestrates fetching + persisting metrics for one tiktok_link. Depends
 * only on TikTokDataSourceInterface so the concrete data source (scrape
 * stub today, a real scraper or TikTokCreatorOAuthService later) can be
 * swapped without touching this class or its callers.
 */
class TikTokTrackingService
{
    public function __construct(private TikTokDataSourceInterface $dataSource)
    {
    }

    /**
     * @throws TikTokDataSourceException propagated from the data source — callers (snapshot:tiktok) must catch this per-link.
     */
    public function syncLink(array $tiktokLink): void
    {
        $metrics = $this->dataSource->fetchMetrics($tiktokLink['url']);

        (new TiktokInsightDailyModel())->upsertDaily([
            'tiktok_link_id' => $tiktokLink['id'],
            'snapshot_date'  => date('Y-m-d'),
            'views'          => $metrics['views'],
            'likes'          => $metrics['likes'],
            'comments'       => $metrics['comments'],
            'shares'         => $metrics['shares'],
            'saves'          => $metrics['saves'],
            'source'         => $tiktokLink['data_source'],
        ]);

        // The video's upload date never changes once known, so only write it
        // the first time it's available — no need to touch it on every sync.
        if (! empty($metrics['posted_at']) && empty($tiktokLink['video_posted_at'])) {
            (new TiktokLinkModel())->update($tiktokLink['id'], ['video_posted_at' => $metrics['posted_at']]);
        }
    }
}
