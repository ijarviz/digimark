<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * TikTok's "saves"/bookmark count is an engagement metric that changes
 * daily (like Instagram's `saves`), so it belongs on the daily snapshot
 * table, not as a static field on tiktok_link.
 */
class AddSavesToTiktokInsightDailyTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tiktok_insight_daily', [
            'saves' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'after'      => 'shares',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tiktok_insight_daily', 'saves');
    }
}
