<?php

namespace App\Models;

use CodeIgniter\Model;

class IgProfileInsightDailyModel extends Model
{
    protected $table         = 'ig_profile_insight_daily';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'ig_account_id',
        'snapshot_date',
        'follower_count',
        'profile_visits',
        'reach',
        'impressions',
    ];

    /**
     * Idempotent upsert keyed on UNIQUE(ig_account_id, snapshot_date).
     */
    public function upsertDaily(array $row): void
    {
        $sql = 'INSERT INTO ig_profile_insight_daily
                (ig_account_id, snapshot_date, follower_count, profile_visits, reach, impressions)
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    follower_count = VALUES(follower_count),
                    profile_visits = VALUES(profile_visits),
                    reach = VALUES(reach),
                    impressions = VALUES(impressions)';

        $this->db->query($sql, [
            $row['ig_account_id'],
            $row['snapshot_date'],
            $row['follower_count'],
            $row['profile_visits'],
            $row['reach'],
            $row['impressions'],
        ]);
    }

    /**
     * Daily series for the profile chart, ordered oldest-first.
     */
    public function getSeriesInRange(int $igAccountId, string $from, string $to): array
    {
        return $this->where('ig_account_id', $igAccountId)
            ->where('snapshot_date >=', $from)
            ->where('snapshot_date <=', $to)
            ->orderBy('snapshot_date', 'ASC')
            ->findAll();
    }
}
