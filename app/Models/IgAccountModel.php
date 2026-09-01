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
        'ad_account_id',
        'business_manager_id',
        'ads_connected_at',
        'is_active',
        'disconnected_at',
    ];

    /**
     * Fase 1 supports a single connected IG Business account, explicitly
     * marked via is_active rather than inferred from "highest id" (which
     * broke silently once reconnects could leave more than one row behind).
     */
    public function getActiveAccount(): ?array
    {
        return $this->where('is_active', 1)->orderBy('id', 'DESC')->first();
    }

    /**
     * Upserts by ig_business_id: reconnecting the same IG Business Account
     * updates its existing row (refreshing the token, preserving any
     * already-selected ad_account_id) instead of accumulating duplicate
     * rows. Any other row is marked inactive so is_active=1 stays unique.
     *
     * @return int The row id (existing or newly inserted).
     */
    public function upsertConnection(array $data): int
    {
        $existing = $this->where('ig_business_id', $data['ig_business_id'])->first();

        $this->where('id !=', $existing['id'] ?? 0)
            ->where('is_active', 1)
            ->update(null, ['is_active' => 0, 'disconnected_at' => date('Y-m-d H:i:s')]);

        $data['is_active'] = 1;

        if ($existing) {
            $this->update($existing['id'], $data);

            return $existing['id'];
        }

        return $this->insert($data);
    }
}
