<?php

namespace App\Libraries\Instagram;

use Config\Services;
use RuntimeException;

/**
 * Instagram Graph API client for content listing, per-media insights, and
 * profile insights. Uses CI4's built-in CURLRequest rather than adding
 * Guzzle as a dependency.
 *
 * NOTE: Graph API's available insight metrics differ by media_type and
 * change between API versions (e.g. `shares` for organic reels was added
 * relatively recently, and `plays` only applies to video/reels). The metric
 * lists below are a best-effort mapping per the Technical Design Doc's
 * schema and have not been validated against a live IG Business account —
 * revisit once real credentials/content are available.
 */
class InstagramApiService
{
    private const GRAPH_VERSION = 'v19.0';
    private const GRAPH_BASE    = 'https://graph.facebook.com/' . self::GRAPH_VERSION;

    /**
     * @return list<array<string, mixed>>
     */
    public function listRecentMedia(string $igAccountId, string $accessToken, int $limit = 25): array
    {
        $result = $this->get("/{$igAccountId}/media", [
            'fields'       => 'id,media_type,caption,permalink,thumbnail_url,timestamp,like_count,comments_count',
            'limit'        => $limit,
            'access_token' => $accessToken,
        ]);

        return $result['data'] ?? [];
    }

    /**
     * @return array{reach:int, impressions:int, saves:int, plays:?int, shares:int}
     */
    public function getMediaInsights(string $mediaId, string $mediaType, string $accessToken): array
    {
        $isVideo = in_array(strtoupper($mediaType), ['VIDEO', 'REELS'], true);

        $metrics = $isVideo
            ? ['reach', 'impressions', 'plays', 'saved', 'shares']
            : ['reach', 'impressions', 'saved', 'shares'];

        $result = $this->get("/{$mediaId}/insights", [
            'metric'       => implode(',', $metrics),
            'access_token' => $accessToken,
        ]);

        $values = $this->flattenMetricValues($result['data'] ?? []);

        return [
            'reach'       => $values['reach'] ?? 0,
            'impressions' => $values['impressions'] ?? 0,
            'saves'       => $values['saved'] ?? 0,
            'shares'      => $values['shares'] ?? 0,
            'plays'       => $isVideo ? ($values['plays'] ?? 0) : null,
        ];
    }

    /**
     * @return array{follower_count:int, profile_visits:int, reach:int, impressions:int}
     */
    public function getProfileInsights(string $igAccountId, string $accessToken): array
    {
        $profile = $this->get("/{$igAccountId}", [
            'fields'       => 'followers_count',
            'access_token' => $accessToken,
        ]);

        $insights = $this->get("/{$igAccountId}/insights", [
            'metric'       => 'profile_views,reach,impressions',
            'period'       => 'day',
            'access_token' => $accessToken,
        ]);

        $values = $this->flattenMetricValues($insights['data'] ?? []);

        return [
            'follower_count' => $profile['followers_count'] ?? 0,
            'profile_visits' => $values['profile_views'] ?? 0,
            'reach'          => $values['reach'] ?? 0,
            'impressions'    => $values['impressions'] ?? 0,
        ];
    }

    /**
     * Graph API insight responses look like:
     *   [{"name": "reach", "period": "day", "values": [{"value": 123}]}, ...]
     * This flattens that into ['reach' => 123, ...].
     */
    private function flattenMetricValues(array $metricData): array
    {
        $flat = [];

        foreach ($metricData as $metric) {
            $flat[$metric['name']] = $metric['values'][0]['value'] ?? 0;
        }

        return $flat;
    }

    private function get(string $path, array $query): array
    {
        $client   = Services::curlrequest();
        $response = $client->get(self::GRAPH_BASE . $path, ['query' => $query, 'http_errors' => false]);
        $body     = json_decode($response->getBody(), true);

        if (isset($body['error'])) {
            throw new RuntimeException('Graph API error: ' . ($body['error']['message'] ?? 'unknown error'));
        }

        return $body ?? [];
    }
}
