<?php

namespace App\Models;

use CodeIgniter\Model;

class TiktokLinkModel extends Model
{
    protected $table         = 'tiktok_link';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'url',
        'tiktok_video_id',
        'creator_handle',
        'affiliate_note',
        'data_source',
        'is_active',
        'last_synced_at',
        'added_by',
        'created_at',
    ];

    protected $validationRules = [
        // regex_match confirms the host is actually (a subdomain of)
        // tiktok.com — valid_url_strict alone accepts any http(s) URL,
        // which previously let non-TikTok links be tracked silently.
        'url' => 'required|max_length[500]|valid_url_strict[http,https]|regex_match[/^https?:\/\/([\w-]+\.)?tiktok\.com\//i]',
    ];

    protected $validationMessages = [
        'url' => [
            'regex_match' => 'URL harus berupa link tiktok.com yang valid.',
        ],
    ];

    public function activeLinks(): array
    {
        return $this->where('is_active', 1)->findAll();
    }

    public function touchLastSynced(int $id): void
    {
        $this->update($id, ['last_synced_at' => date('Y-m-d H:i:s')]);
    }
}
