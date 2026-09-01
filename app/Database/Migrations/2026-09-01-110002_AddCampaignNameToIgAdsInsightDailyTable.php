<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * TDD §3.4 only defines campaign_id — the build prompt explicitly permits
 * adding a missing campaign field ("sesuaikan kalau ada field campaign
 * yang kurang"). A dashboard showing only raw campaign IDs is not usable.
 */
class AddCampaignNameToIgAdsInsightDailyTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('ig_ads_insight_daily', [
            'campaign_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'campaign_id',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('ig_ads_insight_daily', 'campaign_name');
    }
}
