<?php

namespace App\Libraries\TikTok;

/**
 * Contract for fetching metrics for one tiktok_link. Coding
 * TikTokTrackingService against this interface, rather than a concrete
 * class, means swapping the scraping approach for a real implementation
 * later touches no calling code.
 */
interface TikTokDataSourceInterface
{
    /**
     * @return array{views:int, likes:int, comments:int, shares:int, saves:int, posted_at:?string}
     *
     * posted_at is the video's own upload date on TikTok ('Y-m-d H:i:s'), or
     * null when the data source can't determine it.
     *
     * @throws TikTokDataSourceException on any failure to fetch metrics for the given URL.
     */
    public function fetchMetrics(string $url): array;
}
