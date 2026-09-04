<?php

namespace App\Libraries\TikTok;

use Config\Services;

/**
 * Fetches video stats via the local Playwright scraper service (see
 * "scrapping sosmed" directory — a small Express server that reads TikTok's
 * `__UNIVERSAL_DATA_FOR_REHYDRATION__` payload for a video page). That
 * service must be running separately (`npm start` inside "scrapping
 * sosmed"); this class is just an HTTP client for its `/api/video-stats`
 * endpoint, matching the drop-in-replacement design noted in
 * TikTokDataSourceInterface's doc comment.
 */
class ScrapeTikTokDataSource implements TikTokDataSourceInterface
{
    public function fetchMetrics(string $url): array
    {
        $baseUrl = rtrim(env('TIKTOK_SCRAPER_BASE_URL') ?: 'http://localhost:3000', '/');

        try {
            $response = Services::curlrequest()->get($baseUrl . '/api/video-stats', [
                'query'          => ['url' => $url],
                'http_errors'    => false,
                // The scraper paces each lookup (2–4s), spins up a fresh browser
                // context, and retries with backoff on a bot-check — a single
                // call can legitimately take a minute. It also serializes calls
                // in an internal queue, so a client that gives up early leaves a
                // job still running and backs the queue up for every request
                // after it. Wait it out instead.
                'connect_timeout' => 10,
                'timeout'         => 120,
            ]);
        } catch (\Throwable $e) {
            throw new TikTokDataSourceException(
                "Tidak bisa menghubungi scraper service di {$baseUrl} — pastikan sudah dijalankan (`npm start` di folder \"scrapping sosmed\"). " . $e->getMessage(),
                previous: $e
            );
        }

        $body = json_decode((string) $response->getBody(), true);

        if (! is_array($body) || isset($body['error'])) {
            throw new TikTokDataSourceException($body['error'] ?? 'Respons scraper service tidak valid untuk URL: ' . $url);
        }

        foreach (['views', 'likes', 'comments', 'shares', 'saves'] as $field) {
            if (! array_key_exists($field, $body)) {
                throw new TikTokDataSourceException("Field '{$field}' tidak ada di respons scraper untuk URL: {$url}");
            }
        }

        return [
            'views'     => (int) $body['views'],
            'likes'     => (int) $body['likes'],
            'comments'  => (int) $body['comments'],
            'shares'    => (int) $body['shares'],
            'saves'     => (int) $body['saves'],
            'posted_at' => ! empty($body['createTime']) ? date('Y-m-d H:i:s', (int) $body['createTime']) : null,
        ];
    }
}
