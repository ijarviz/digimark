<?php

namespace App\Libraries\Instagram;

use Config\Services;
use RuntimeException;

/**
 * Marketing API client for campaign-level ads insights. Same Meta App /
 * same long-lived token as InstagramApiService — only the endpoint family
 * differs (act_{ad_account_id} instead of the IG business account id).
 */
class InstagramAdsApiService
{
    private const GRAPH_VERSION = 'v19.0';
    private const GRAPH_BASE    = 'https://graph.facebook.com/' . self::GRAPH_VERSION;

    /**
     * @return list<array{campaign_id:string, campaign_name:string, ad_reach:int, ad_impressions:int, spend:float}>
     */
    public function listCampaignInsights(string $adAccountId, string $accessToken, string $date): array
    {
        $result = $this->get("/{$adAccountId}/insights", [
            'level'        => 'campaign',
            'fields'       => 'campaign_id,campaign_name,reach,impressions,spend',
            'time_range'   => json_encode(['since' => $date, 'until' => $date]),
            'access_token' => $accessToken,
        ]);

        return array_map(static fn (array $row) => [
            'campaign_id'    => $row['campaign_id'],
            'campaign_name'  => $row['campaign_name'] ?? '',
            'ad_reach'       => (int) ($row['reach'] ?? 0),
            'ad_impressions' => (int) ($row['impressions'] ?? 0),
            'spend'          => (float) ($row['spend'] ?? 0),
        ], $result['data'] ?? []);
    }

    /**
     * The ad account's billing currency (e.g. "IDR", "USD") — a property of
     * the account, not of individual insight rows, so it's fetched once
     * per sync run and denormalized onto each campaign row rather than
     * re-fetched per campaign.
     */
    public function getAdAccountCurrency(string $adAccountId, string $accessToken): ?string
    {
        $result = $this->get("/{$adAccountId}", [
            'fields'       => 'currency',
            'access_token' => $accessToken,
        ]);

        return $result['currency'] ?? null;
    }

    /**
     * Best-effort: finds an ig_content_id if this campaign is boosting an
     * organic post. NOTE: unverified against a live ad account — Graph
     * API's exact field for linking an ad's creative back to an IG media
     * object (`effective_object_story_id` here) may need adjustment once
     * tested against real campaigns. Any failure here must never block the
     * insight row itself — callers should catch and treat as "no link".
     */
    public function resolveIgMediaId(string $campaignId, string $accessToken): ?string
    {
        $result = $this->get("/{$campaignId}/ads", [
            'fields'       => 'creative{effective_object_story_id}',
            'limit'        => 5,
            'access_token' => $accessToken,
        ]);

        foreach ($result['data'] ?? [] as $ad) {
            $storyId = $ad['creative']['effective_object_story_id'] ?? null;

            // effective_object_story_id is "{page_id}_{media_id}" — extract the media id segment.
            if ($storyId && str_contains($storyId, '_')) {
                return substr($storyId, strpos($storyId, '_') + 1);
            }
        }

        return null;
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
