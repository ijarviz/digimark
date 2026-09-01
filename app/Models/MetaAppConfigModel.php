<?php

namespace App\Models;

use App\Libraries\Instagram\InstagramTokenService;
use CodeIgniter\Model;

class MetaAppConfigModel extends Model
{
    protected $table         = 'meta_app_config';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'app_id',
        'app_secret_encrypted',
        'redirect_uri',
        'updated_by',
        'updated_at',
    ];

    /**
     * Single-row config table — whichever row exists is "the" config, a
     * new one is created only if the table is still empty.
     */
    public function getConfig(): ?array
    {
        return $this->orderBy('id', 'ASC')->first();
    }

    /**
     * @param string|null $appSecretPlain Pass null to keep the existing secret unchanged.
     */
    public function saveConfig(string $appId, ?string $appSecretPlain, string $redirectUri, ?int $updatedBy): void
    {
        $existing = $this->getConfig();

        $data = [
            'app_id'       => $appId,
            'redirect_uri' => $redirectUri,
            'updated_by'   => $updatedBy,
            'updated_at'   => date('Y-m-d H:i:s'),
        ];

        if ($appSecretPlain !== null && $appSecretPlain !== '') {
            $data['app_secret_encrypted'] = (new InstagramTokenService())->encrypt($appSecretPlain);
        }

        if ($existing) {
            $this->update($existing['id'], $data);
        } else {
            $this->insert($data);
        }
    }

    public function getDecryptedAppSecret(): ?string
    {
        $config = $this->getConfig();

        if (! $config || empty($config['app_secret_encrypted'])) {
            return null;
        }

        return (new InstagramTokenService())->decrypt($config['app_secret_encrypted']);
    }
}
