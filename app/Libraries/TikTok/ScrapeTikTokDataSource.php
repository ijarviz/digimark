<?php

namespace App\Libraries\TikTok;

/**
 * NOT YET IMPLEMENTED — intentionally a stub.
 *
 * TikTok has no official API for pulling metrics from an arbitrary video
 * URL (PRD §5 Fase 4 / TDD §6). Picking a concrete approach (headless
 * browser automation, an undocumented internal endpoint, or a third-party
 * scraping service) is fragile, may violate TikTok's ToS, and is prone to
 * breaking without notice whenever TikTok changes its markup or adds
 * anti-bot measures. That choice needs an explicit decision with the user
 * before real logic is written here — see build prompt's "Yang Perlu
 * Dikonfirmasi" section.
 *
 * `snapshot:tiktok` already tolerates this throwing for every link (each
 * failure is caught and logged per-row, the loop still completes), so
 * wiring in a real implementation later is a drop-in replacement of this
 * class's body only — TikTokTrackingService and callers do not change.
 */
class ScrapeTikTokDataSource implements TikTokDataSourceInterface
{
    public function fetchMetrics(string $url): array
    {
        throw new TikTokDataSourceException(
            'ScrapeTikTokDataSource is not implemented yet — scraping method/library is pending a decision with the user (see class doc comment). URL: ' . $url
        );
    }
}
