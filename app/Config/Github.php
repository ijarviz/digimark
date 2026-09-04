<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * "Improve Me" feature (Jarvis Power menu) — a fine-grained GitHub PAT
 * (Issues: Read and write, scoped to the project repo) used solely to
 * create the GitHub issue that bridges an admin-submitted prompt to a
 * claude.ai cloud routine, and to poll that issue for the routine's
 * result. Same .env-based secret handling as every other credential in
 * this project — gitignored, never in a tracked file.
 *
 *   github.repo  = 'owner/name'   (e.g. ijarviz/digimark)
 *   github.token = 'github_pat_...'
 */
class Github extends BaseConfig
{
    public string $token;
    public string $repo;

    public function __construct()
    {
        parent::__construct();

        $this->token = (string) env('github.token', '');
        $this->repo  = (string) env('github.repo', '');
    }
}
