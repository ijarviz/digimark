<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Inert in Fase 1 — no controller/route references this model yet. See
 * App\Libraries\TikTok\TikTokCreatorOAuthService.
 */
class TiktokCreatorOauthModel extends Model
{
    protected $table         = 'tiktok_creator_oauth';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'creator_handle',
        'access_token_encrypted',
        'token_expires_at',
        'connected_at',
    ];
}
