<?php

namespace App\Libraries\TikTok;

use RuntimeException;

/**
 * Skeleton only, per build prompt's optional/time-permitting instruction —
 * not called anywhere in Fase 1. Represents the "creator connects their
 * TikTok account via TikTok Display API" path (PRD §5 Fase 4 option 1),
 * which is the legal/stable alternative to scraping for creators willing
 * to opt in. Left unimplemented until this path is prioritized.
 */
class TikTokCreatorOAuthService
{
    public function getAuthorizeUrl(string $creatorHandle): string
    {
        throw new RuntimeException('TikTokCreatorOAuthService::getAuthorizeUrl() not implemented — Fase 4 skeleton only.');
    }

    public function handleCallback(string $code): array
    {
        throw new RuntimeException('TikTokCreatorOAuthService::handleCallback() not implemented — Fase 4 skeleton only.');
    }

    public function refreshToken(string $currentToken): array
    {
        throw new RuntimeException('TikTokCreatorOAuthService::refreshToken() not implemented — Fase 4 skeleton only.');
    }
}
