<?php

namespace App\Commands;

use App\Libraries\Instagram\InstagramOAuthService;
use App\Libraries\Instagram\InstagramTokenService;
use App\Models\IgAccountModel;
use CodeIgniter\CLI\CLI;
use DateTime;

class RefreshIgToken extends BaseSnapshotCommand
{
    protected $name        = 'refresh:ig-token';
    protected $description = 'Refreshes the IG long-lived token when fewer than 7 days remain before expiry.';

    protected function jobName(): string
    {
        return 'refresh_ig_token';
    }

    protected function handleJob(array $params): array
    {
        $accountModel = new IgAccountModel();
        $account      = $accountModel->getActiveAccount();

        if (! $account) {
            CLI::write('No IG account connected — nothing to refresh.', 'yellow');

            return ['status' => 'success', 'error' => null, 'processed' => 0, 'failed' => 0];
        }

        $tokenService = new InstagramTokenService();
        $expiresAt    = new DateTime($account['token_expires_at']);

        if (! $tokenService->isExpiringSoon($expiresAt, 7)) {
            CLI::write("Token not expiring soon (expires {$account['token_expires_at']}) — skipping.", 'green');

            return ['status' => 'success', 'error' => null, 'processed' => 0, 'failed' => 0];
        }

        try {
            $currentToken = $tokenService->decrypt($account['access_token_encrypted']);
            $refreshed    = (new InstagramOAuthService())->refreshLongLivedToken($currentToken);

            $accountModel->update($account['id'], [
                'access_token_encrypted' => $tokenService->encrypt($refreshed['access_token']),
                'token_expires_at'       => $refreshed['expires_at']->format('Y-m-d H:i:s'),
            ]);

            CLI::write('Token refreshed, new expiry: ' . $refreshed['expires_at']->format('Y-m-d H:i:s'), 'green');

            return ['status' => 'success', 'error' => null, 'processed' => 1, 'failed' => 0];
        } catch (\Throwable $e) {
            CLI::error('Token refresh failed: ' . $e->getMessage());

            return ['status' => 'failed', 'error' => $e->getMessage(), 'processed' => 0, 'failed' => 1];
        }
    }
}
