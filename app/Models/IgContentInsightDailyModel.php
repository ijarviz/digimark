<?php

namespace App\Models;

use CodeIgniter\Model;

class IgContentInsightDailyModel extends Model
{
    protected $table         = 'ig_content_insight_daily';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'ig_content_id',
        'snapshot_date',
        'reach',
        'impressions',
        'likes',
        'comments',
        'shares',
        'saves',
        'plays',
    ];

    /**
     * Idempotent upsert keyed on UNIQUE(ig_content_id, snapshot_date) —
     * re-running a snapshot for the same day updates the row instead of
     * duplicating it.
     */
    public function upsertDaily(array $row): void
    {
        $sql = 'INSERT INTO ig_content_insight_daily
                (ig_content_id, snapshot_date, reach, impressions, likes, comments, shares, saves, plays)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    reach = VALUES(reach),
                    impressions = VALUES(impressions),
                    likes = VALUES(likes),
                    comments = VALUES(comments),
                    shares = VALUES(shares),
                    saves = VALUES(saves),
                    plays = VALUES(plays)';

        $this->db->query($sql, [
            $row['ig_content_id'],
            $row['snapshot_date'],
            $row['reach'],
            $row['impressions'],
            $row['likes'],
            $row['comments'],
            $row['shares'],
            $row['saves'],
            $row['plays'],
        ]);
    }

    /**
     * Per-content totals within a date range, for the content table.
     */
    public function getContentTotalsInRange(int $igAccountId, string $from, string $to): array
    {
        return $this->db->table('ig_content_insight_daily i')
            ->select('c.id, c.media_type, c.caption, c.permalink, c.posted_at,
                      SUM(i.reach) as reach, SUM(i.impressions) as impressions,
                      SUM(i.likes) as likes, SUM(i.comments) as comments,
                      SUM(i.shares) as shares, SUM(i.saves) as saves, SUM(i.plays) as plays')
            ->join('ig_content c', 'c.id = i.ig_content_id')
            ->where('c.ig_account_id', $igAccountId)
            ->where('i.snapshot_date >=', $from)
            ->where('i.snapshot_date <=', $to)
            ->groupBy('c.id, c.media_type, c.caption, c.permalink, c.posted_at')
            ->orderBy('c.posted_at', 'DESC')
            ->get()
            ->getResultArray();
    }
}
