<?php

namespace App\Libraries\Instagram;

use Config\Services;
use DateTime;
use RuntimeException;

/**
 * Facebook Login for Business OAuth flow: authorize -> exchange code for
 * short-lived token -> exchange for long-lived token -> resolve the IG
 * Business Account linked to the user's Facebook Page.
 *
 * Requires IG_APP_ID / IG_APP_SECRET / IG_REDIRECT_URI in .env — until a
 * real Meta App exists these are placeholders and this flow cannot
 * complete end-to-end.
 */
class InstagramOAuthService
{
    private const GRAPH_VERSION = 'v19.0';
    private const GRAPH_BASE    = 'https://graph.facebook.com/' . self::GRAPH_VERSION;
    private const OAUTH_DIALOG  = 'https://www.facebook.com/' . self::GRAPH_VERSION . '/dialog/oauth';

    private const SCOPES = [
        'instagram_basic',
        'instagram_manage_insights',
        'instagram_content_publish',
        'pages_show_list',
        'pages_read_engagement',
        'ads_read',
    ];

    public function getAuthorizeUrl(): string
    {
        $params = [
            'client_id'     => env('IG_APP_ID'),
            'redirect_uri'  => env('IG_REDIRECT_URI'),
            'scope'         => implode(',', self::SCOPES),
            'response_type' => 'code',
        ];

        return self::OAUTH_DIALOG . '?' . http_build_query($params);
    }

    /**
     * Exchanges the OAuth callback `code` for a long-lived token and
     * resolves the connected IG Business Account.
     *
     * @return array{ig_business_id: string, ig_username: string, fb_page_id: string, access_token: string, expires_at: DateTime}
     */
    public function handleCallback(string $code): array
    {
        $shortLivedToken = $this->exchangeCodeForToken($code);
        $longLivedToken  = $this->exchangeForLongLivedToken($shortLivedToken['access_token']);

        $pageId = $this->getFirstPageId($longLivedToken['access_token']);
        $igInfo = $this->getIgBusinessAccount($pageId, $longLivedToken['access_token']);

        return [
            'ig_business_id' => $igInfo['id'],
            'ig_username'    => $igInfo['username'] ?? '',
            'fb_page_id'     => $pageId,
            'access_token'   => $longLivedToken['access_token'],
            'expires_at'     => (new DateTime())->modify('+' . ($longLivedToken['expires_in'] ?? 5184000) . ' seconds'),
        ];
    }

    /**
     * Refreshes a long-lived token that is close to expiring. Facebook long-lived
     * tokens are refreshed the same way they were minted (fb_exchange_token grant).
     *
     * @return array{access_token: string, expires_at: DateTime}
     */
    public function refreshLongLivedToken(string $currentToken): array
    {
        $result = $this->exchangeForLongLivedToken($currentToken);

        return [
            'access_token' => $result['access_token'],
            'expires_at'   => (new DateTime())->modify('+' . ($result['expires_in'] ?? 5184000) . ' seconds'),
        ];
    }

    /**
     * Ad accounts accessible to the connected user, for the Fase 2 ads
     * account picker. Requires the `ads_read` scope to have been granted.
     *
     * @return list<array{id: string, name: string, business_id: ?string}>
     */
    public function listAdAccounts(string $accessToken): array
    {
        $result = $this->get('/me/adaccounts', [
            'fields'       => 'id,name,business',
            'access_token' => $accessToken,
        ]);

        return array_map(static fn (array $account) => [
            'id'          => $account['id'],
            'name'        => $account['name'] ?? $account['id'],
            'business_id' => $account['business']['id'] ?? null,
        ], $result['data'] ?? []);
    }

    private function exchangeCodeForToken(string $code): array
    {
        return $this->get('/oauth/access_token', [
            'client_id'     => env('IG_APP_ID'),
            'client_secret' => env('IG_APP_SECRET'),
            'redirect_uri'  => env('IG_REDIRECT_URI'),
            'code'          => $code,
        ]);
    }

    private function exchangeForLongLivedToken(string $shortLivedToken): array
    {
        return $this->get('/oauth/access_token', [
            'grant_type'        => 'fb_exchange_token',
            'client_id'         => env('IG_APP_ID'),
            'client_secret'     => env('IG_APP_SECRET'),
            'fb_exchange_token' => $shortLivedToken,
        ]);
    }

    private function getFirstPageId(string $accessToken): string
    {
        $result = $this->get('/me/accounts', ['access_token' => $accessToken]);

        if (empty($result['data'][0]['id'])) {
            throw new RuntimeException('No Facebook Page found for this user — cannot resolve an IG Business Account.');
        }

        return $result['data'][0]['id'];
    }

    private function getIgBusinessAccount(string $pageId, string $accessToken): array
    {
        $result = $this->get("/{$pageId}", [
            'fields'       => 'instagram_business_account{id,username}',
            'access_token' => $accessToken,
        ]);

        if (empty($result['instagram_business_account']['id'])) {
            throw new RuntimeException('This Facebook Page has no linked Instagram Business Account.');
        }

        return $result['instagram_business_account'];
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
