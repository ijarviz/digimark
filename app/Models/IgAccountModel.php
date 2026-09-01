<?php

namespace App\Models;

use CodeIgniter\Model;

class IgAccountModel extends Model
{
    protected $table         = 'ig_account';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'ig_business_id',
        'ig_username',
        'fb_page_id',
        'access_token_encrypted',
        'token_expires_at',
        'connected_by',
        'connected_at',
    ];

    /**
     * Fase 1 supports a single connected IG Business account — the most
     * recently connected row is treated as "the" active account.
     */
    public function getActiveAccount(): ?array
    {
        return $this->orderBy('id', 'DESC')->first();
    }
}
