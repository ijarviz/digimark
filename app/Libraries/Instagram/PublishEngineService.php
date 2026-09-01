<?php

namespace App\Libraries\Instagram;

use Config\Services;
use RuntimeException;

/**
 * Instagram Content Publishing API: container creation (per media type) ->
 * status polling -> publish. One media container flow per type, since each
 * has a genuinely different Graph API shape (build prompt explicitly warns
 * against assuming they're all the same).
 *
 * NOTE on Stories: caption is intentionally not sent — the Content
 * Publishing API does not support caption text on STORIES containers.
 * Image vs. video is inferred from the URL's file extension, which is a
 * heuristic (documented, not guaranteed) since publish_queue only stores a
 * flat URL list with no explicit per-item media kind.
 */
class PublishEngineService
{
    private const GRAPH_VERSION = 'v19.0';
    private const GRAPH_BASE    = 'https://graph.facebook.com/' . self::GRAPH_VERSION;

    private const VIDEO_EXTENSIONS = ['mp4', 'mov', 'm4v'];

    /**
     * @param array{media_type: string, media_urls: array<int, string>, caption: ?string} $item
     *
     * @return string creation_id
     */
    public function createContainer(array $item, string $igBusinessId, string $accessToken): string
    {
        return match ($item['media_type']) {
            'image'    => $this->createImageContainer($item, $igBusinessId, $accessToken),
            'reels'    => $this->createReelsContainer($item, $igBusinessId, $accessToken),
            'carousel' => $this->createCarouselContainer($item, $igBusinessId, $accessToken),
            'story'    => $this->createStoryContainer($item, $igBusinessId, $accessToken),
            default    => throw new RuntimeException("Unsupported media_type: {$item['media_type']}"),
        };
    }

    /**
     * @return array{status_code: string, raw: array}
     */
    public function pollStatus(string $creationId, string $accessToken): array
    {
        $result = $this->get("/{$creationId}", [
            'fields'       => 'status_code',
            'access_token' => $accessToken,
        ]);

        return ['status_code' => $result['status_code'] ?? 'UNKNOWN', 'raw' => $result];
    }

    /**
     * @return string ig_media_id
     */
    public function publish(string $creationId, string $igBusinessId, string $accessToken): string
    {
        $result = $this->post("/{$igBusinessId}/media_publish", [
            'creation_id'  => $creationId,
            'access_token' => $accessToken,
        ]);

        if (empty($result['id'])) {
            throw new RuntimeException('Publish call did not return a media id.');
        }

        return $result['id'];
    }

    private function createImageContainer(array $item, string $igBusinessId, string $accessToken): string
    {
        $result = $this->post("/{$igBusinessId}/media", [
            'image_url'    => $item['media_urls'][0],
            'caption'      => $item['caption'] ?? '',
            'access_token' => $accessToken,
        ]);

        return $this->extractCreationId($result);
    }

    private function createReelsContainer(array $item, string $igBusinessId, string $accessToken): string
    {
        $result = $this->post("/{$igBusinessId}/media", [
            'media_type'   => 'REELS',
            'video_url'    => $item['media_urls'][0],
            'caption'      => $item['caption'] ?? '',
            'access_token' => $accessToken,
        ]);

        return $this->extractCreationId($result);
    }

    private function createCarouselContainer(array $item, string $igBusinessId, string $accessToken): string
    {
        $childIds = [];

        foreach ($item['media_urls'] as $url) {
            $isVideo = $this->isVideoUrl($url);

            $childResult = $this->post("/{$igBusinessId}/media", array_filter([
                'image_url'         => $isVideo ? null : $url,
                'video_url'         => $isVideo ? $url : null,
                'is_carousel_item'  => 'true',
                'access_token'      => $accessToken,
            ]));

            $childIds[] = $this->extractCreationId($childResult);
        }

        $result = $this->post("/{$igBusinessId}/media", [
            'media_type'   => 'CAROUSEL',
            'children'     => implode(',', $childIds),
            'caption'      => $item['caption'] ?? '',
            'access_token' => $accessToken,
        ]);

        return $this->extractCreationId($result);
    }

    private function createStoryContainer(array $item, string $igBusinessId, string $accessToken): string
    {
        $url     = $item['media_urls'][0];
        $isVideo = $this->isVideoUrl($url);

        $result = $this->post("/{$igBusinessId}/media", array_filter([
            'media_type'   => 'STORIES',
            'image_url'    => $isVideo ? null : $url,
            'video_url'    => $isVideo ? $url : null,
            'access_token' => $accessToken,
        ]));

        return $this->extractCreationId($result);
    }

    private function isVideoUrl(string $url): bool
    {
        $extension = strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));

        return in_array($extension, self::VIDEO_EXTENSIONS, true);
    }

    private function extractCreationId(array $result): string
    {
        if (empty($result['id'])) {
            throw new RuntimeException('Container creation did not return an id.');
        }

        return $result['id'];
    }

    private function post(string $path, array $fields): array
    {
        $client   = Services::curlrequest();
        $response = $client->post(self::GRAPH_BASE . $path, ['query' => $fields, 'http_errors' => false]);
        $body     = json_decode($response->getBody(), true);

        if (isset($body['error'])) {
            throw new RuntimeException('Graph API error: ' . ($body['error']['message'] ?? 'unknown error'));
        }

        return $body ?? [];
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
