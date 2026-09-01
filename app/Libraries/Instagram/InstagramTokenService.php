<?php

namespace App\Libraries\Instagram;

use Config\Services;
use DateTime;

/**
 * Encrypts/decrypts Instagram access tokens for storage. CI4's Encryption
 * service returns raw binary output, so it is base64-wrapped before being
 * persisted in a `text` column and base64-decoded before decryption.
 */
class InstagramTokenService
{
    public function encrypt(string $plainToken): string
    {
        $encrypter = Services::encrypter();

        return base64_encode($encrypter->encrypt($plainToken));
    }

    public function decrypt(string $encryptedToken): string
    {
        $encrypter = Services::encrypter();

        return $encrypter->decrypt(base64_decode($encryptedToken));
    }

    public function isExpiringSoon(DateTime $expiresAt, int $daysThreshold = 7): bool
    {
        $threshold = (new DateTime())->modify("+{$daysThreshold} days");

        return $expiresAt <= $threshold;
    }
}
