<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Influencer Discovery — Apify account token used to run the Instagram/
 * TikTok search actors (see App\Libraries\Discovery\ApifyDiscoveryService).
 * Same .env-based secret handling as every other credential in this
 * project — gitignored, never in a tracked file.
 */
class Apify extends BaseConfig
{
    public string $token;

    public function __construct()
    {
        parent::__construct();

        $this->token = (string) env('APIFY_TOKEN', '');
    }
}
