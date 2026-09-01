<?php

namespace App\Models;

use CodeIgniter\Model;

class IgAdsInsightDailyModel extends Model
{
    protected $table         = 'ig_ads_insight_daily';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'ig_content_id',
        'campaign_id',
        'campaign_name',
        'snapshot_date',
        'ad_reach',
        'ad_impressions',
        'spend',
    ];

    /**
     * Idempotent upsert keyed on UNIQUE(campaign_id, snapshot_date).
     */
    public function upsertDaily(array $row): void
    {
        $sql = 'INSERT INTO ig_ads_insight_daily
                (ig_content_id, campaign_id, campaign_name, snapshot_date, ad_reach, ad_impressions, spend)
                VALUES (?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    ig_content_id = VALUES(ig_content_id),
                    campaign_name = VALUES(campaign_name),
                    ad_reach = VALUES(ad_reach),
                    ad_impressions = VALUES(ad_impressions),
                    spend = VALUES(spend)';

        $this->db->query($sql, [
            $row['ig_content_id'],
            $row['campaign_id'],
            $row['campaign_name'],
            $row['snapshot_date'],
            $row['ad_reach'],
            $row['ad_impressions'],
            $row['spend'],
        ]);
    }

    /**
     * Campaign rows for the date range, with linked organic content
     * (permalink/caption) when ig_content_id is set. No ig_account scoping
     * needed — Fase 1/2 assume a single connected account.
     */
    public function getCampaignsInRange(string $from, string $to): array
    {
        return $this->db->table('ig_ads_insight_daily a')
            ->select('a.campaign_id, a.campaign_name,
                      SUM(a.ad_reach) as ad_reach, SUM(a.ad_impressions) as ad_impressions, SUM(a.spend) as spend,
                      c.permalink, c.caption')
            ->join('ig_content c', 'c.id = a.ig_content_id', 'left')
            ->where('a.snapshot_date >=', $from)
            ->where('a.snapshot_date <=', $to)
            ->groupBy('a.campaign_id, a.campaign_name, c.permalink, c.caption')
            ->orderBy('spend', 'DESC')
            ->get()
            ->getResultArray();
    }
}
