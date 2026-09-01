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
        'added_by',
        'created_at',
    ];

    protected $validationRules = [
        'url' => 'required|max_length[500]|valid_url_strict[http,https]',
    ];
}
