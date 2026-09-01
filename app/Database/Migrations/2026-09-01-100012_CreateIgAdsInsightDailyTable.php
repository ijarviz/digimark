<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Fase 2 (Instagram Ads) — schema prepared ahead of time so no future
 * migration headache, but this table is not referenced by any Fase 1
 * controller/service/route.
 */
class CreateIgAdsInsightDailyTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'ig_content_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'campaign_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'snapshot_date' => [
                'type' => 'DATE',
            ],
            'ad_reach' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'ad_impressions' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'spend' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['campaign_id', 'snapshot_date']);
        $this->forge->addForeignKey('ig_content_id', 'ig_content', 'id', '', 'SET NULL');
        $this->forge->createTable('ig_ads_insight_daily');
    }

    public function down()
    {
        $this->forge->dropTable('ig_ads_insight_daily');
    }
}
