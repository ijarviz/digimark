<?php

namespace App\Models;

use CodeIgniter\Model;

class IgContentModel extends Model
{
    protected $table         = 'ig_content';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'ig_account_id',
        'ig_media_id',
        'media_type',
        'caption',
        'permalink',
        'thumbnail_url',
        'posted_at',
        'synced_at',
    ];

    /**
     * Idempotent upsert keyed on the UNIQUE(ig_media_id) constraint. Uses a
     * raw parameterized ON DUPLICATE KEY UPDATE for an atomic single
     * round-trip instead of a find-then-insert-or-update race.
     */
    public function upsertByMediaId(array $row): int
    {
        $sql = 'INSERT INTO ig_content
                (ig_account_id, ig_media_id, media_type, caption, permalink, thumbnail_url, posted_at, synced_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    caption = VALUES(caption),
                    permalink = VALUES(permalink),
                    thumbnail_url = VALUES(thumbnail_url),
                    synced_at = VALUES(synced_at)';

        $this->db->query($sql, [
            $row['ig_account_id'],
            $row['ig_media_id'],
            $row['media_type'],
            $row['caption'],
            $row['permalink'],
            $row['thumbnail_url'],
            $row['posted_at'],
            $row['synced_at'],
        ]);

        return (int) $this->where('ig_media_id', $row['ig_media_id'])->first()['id'];
    }

    public function findByMediaId(string $igMediaId): ?array
    {
        return $this->where('ig_media_id', $igMediaId)->first();
    }
}
