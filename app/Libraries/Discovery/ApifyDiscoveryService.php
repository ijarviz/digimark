<?php

namespace App\Libraries\Discovery;

use Config\Apify as ApifyConfig;
use Config\Services;

/**
 * Influencer discovery via Apify actors — searches Instagram/TikTok by
 * keyword and returns matching accounts (handle, followers, bio, ...).
 * Distinct from TikTok\ScrapeTikTokDataSource, which reads metrics for
 * ONE already-known video/profile URL; this finds accounts nobody has
 * added to this app yet.
 *
 * Both actors are run synchronously via Apify's run-sync-get-dataset-items
 * endpoint, which blocks until the run finishes and returns its dataset —
 * simplest option for an admin-triggered, occasional search. Field
 * mappings below were confirmed against live actor output, not just the
 * actors' documented input schema.
 *
 * - Instagram: apify/instagram-search-scraper with searchType=user returns
 *   user profiles directly (username, followersCount, biography, ...).
 * - TikTok: clockworks/tiktok-scraper has no dedicated "profile search" —
 *   with searchSection=/user it returns one VIDEO per matched profile
 *   (resultsPerPage=1 keeps it to exactly one), and the full profile is
 *   embedded in that video's authorMeta (name, fans, signature, ...).
 */
class ApifyDiscoveryService
{
    private const IG_ACTOR     = 'apify~instagram-search-scraper';
    private const TIKTOK_ACTOR = 'clockworks~tiktok-scraper';

    // Apify's own run budget per call. HAProxy's digi-mark backend has a
    // matching 180s timeout server override (see haproxy.cfg) sized for
    // two sequential calls (Instagram + TikTok) plus overhead.
    private const RUN_TIMEOUT_SECONDS = 60;

    public function __construct(private readonly ApifyConfig $config = new ApifyConfig())
    {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function searchInstagram(string $keyword, int $limit = 15): array
    {
        $items = $this->runActor(self::IG_ACTOR, [
            'search'      => $keyword,
            'searchType'  => 'user',
            'searchLimit' => $limit,
        ]);

        $results = [];

        foreach ($items as $item) {
            if (empty($item['username'])) {
                continue;
            }

            $results[] = [
                'platform'    => 'instagram',
                'external_id' => (string) ($item['id'] ?? ''),
                'handle'      => $item['username'],
                'name'        => $item['fullName'] ?: $item['username'],
                'profile_url' => $item['url'] ?? ('https://www.instagram.com/' . $item['username']),
                'avatar_url'  => $item['profilePicUrlHD'] ?? ($item['profilePicUrl'] ?? null),
                'bio'         => $item['biography'] ?? null,
                'followers'   => isset($item['followersCount']) ? (int) $item['followersCount'] : null,
                'following'   => isset($item['followsCount']) ? (int) $item['followsCount'] : null,
                'verified'    => (bool) ($item['verified'] ?? false),
                'category'    => $item['businessCategoryName'] ?? null,
            ];
        }

        return $results;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function searchTikTok(string $keyword, int $limit = 15): array
    {
        $items = $this->runActor(self::TIKTOK_ACTOR, [
            'searchQueries'         => [$keyword],
            'searchSection'         => '/user',
            'maxProfilesPerQuery'   => $limit,
            'resultsPerPage'        => 1,
            'shouldDownloadAvatars' => false,
            'shouldDownloadCovers'  => false,
            'shouldDownloadVideos'  => false,
        ]);

        // One video per matched profile is expected (resultsPerPage=1), but
        // dedupe by author id defensively in case the actor ever returns
        // more than one per profile.
        $seen    = [];
        $results = [];

        foreach ($items as $item) {
            $author = $item['authorMeta'] ?? null;

            if (! $author || empty($author['name'])) {
                continue;
            }

            $key = $author['id'] ?? $author['name'];

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;

            $results[] = [
                'platform'    => 'tiktok',
                'external_id' => (string) ($author['id'] ?? ''),
                'handle'      => $author['name'],
                'name'        => $author['nickName'] ?: $author['name'],
                'profile_url' => $author['profileUrl'] ?? ('https://www.tiktok.com/@' . $author['name']),
                'avatar_url'  => $author['avatar'] ?? null,
                'bio'         => $author['signature'] ?? null,
                'followers'   => isset($author['fans']) ? (int) $author['fans'] : null,
                'following'   => isset($author['following']) ? (int) $author['following'] : null,
                'verified'    => (bool) ($author['verified'] ?? false),
                'category'    => null,
            ];
        }

        return $results;
    }

    /**
     * @param array<string, mixed> $input
     * @return list<array<string, mixed>>
     */
    private function runActor(string $actorId, array $input): array
    {
        if ($this->config->token === '') {
            throw new DiscoveryException('APIFY_TOKEN belum dikonfigurasi di .env.');
        }

        try {
            $response = Services::curlrequest([], null, null, false)->request(
                'POST',
                "https://api.apify.com/v2/acts/{$actorId}/run-sync-get-dataset-items",
                [
                    'query'          => ['token' => $this->config->token, 'timeout' => self::RUN_TIMEOUT_SECONDS],
                    'json'           => $input,
                    'http_errors'    => false,
                    'connect_timeout'=> 10,
                    // A bit above Apify's own run budget so Apify's timeout
                    // (which returns a clean partial/empty result) wins over
                    // an abrupt client-side cutoff.
                    'timeout'        => self::RUN_TIMEOUT_SECONDS + 15,
                ]
            );
        } catch (\Throwable $e) {
            throw new DiscoveryException('Tidak bisa menghubungi Apify: ' . $e->getMessage(), previous: $e);
        }

        $status = $response->getStatusCode();
        $body   = json_decode((string) $response->getBody(), true);

        if ($status < 200 || $status >= 300) {
            $message = is_array($body) ? ($body['error']['message'] ?? json_encode($body)) : (string) $body;

            throw new DiscoveryException("Apify actor {$actorId} gagal (HTTP {$status}): {$message}");
        }

        return is_array($body) ? $body : [];
    }
}
