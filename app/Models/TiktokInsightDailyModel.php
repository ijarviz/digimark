<?php

namespace App\Models;

use CodeIgniter\Model;

class TiktokInsightDailyModel extends Model
{
    protected $table         = 'tiktok_insight_daily';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'tiktok_link_id',
        'snapshot_date',
        'views',
        'likes',
        'comments',
        'shares',
        'source',
    ];

    /**
     * Idempotent upsert keyed on UNIQUE(tiktok_link_id, snapshot_date).
     * `source` is recorded per row since a link can move from 'scrape' to
     * 'oauth' if its creator connects later.
     */
    public function upsertDaily(array $row): void
    {
        $sql = 'INSERT INTO tiktok_insight_daily
                (tiktok_link_id, snapshot_date, views, likes, comments, shares, source)
                VALUES (?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    views = VALUES(views),
                    likes = VALUES(likes),
                    comments = VALUES(comments),
                    shares = VALUES(shares),
                    source = VALUES(source)';

        $this->db->query($sql, [
            $row['tiktok_link_id'],
            $row['snapshot_date'],
            $row['views'],
            $row['likes'],
            $row['comments'],
            $row['shares'],
            $row['source'],
        ]);
    }

    /**
     * Latest metrics row per link, for the TikTok links table.
     */
    public function latestByLink(int $tiktokLinkId): ?array
    {
        return $this->where('tiktok_link_id', $tiktokLinkId)
            ->orderBy('snapshot_date', 'DESC')
            ->first();
    }

    /**
     * Metrics row for a specific snapshot_date, for the date-filtered dashboard.
     */
    public function forLinkAndDate(int $tiktokLinkId, string $date): ?array
    {
        return $this->where('tiktok_link_id', $tiktokLinkId)
            ->where('snapshot_date', $date)
            ->first();
    }
}
