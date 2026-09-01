<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Static per-link/creator campaign metadata (not time-series, so they live
 * on tiktok_link rather than tiktok_insight_daily): influencer tier
 * segment, target gender, and affiliate budget.
 */
class AddCampaignFieldsToTiktokLinkTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tiktok_link', [
            'segment' => [
                'type'       => 'ENUM',
                'constraint' => ['nano', 'micro', 'macro', 'celebrity'],
                'null'       => true,
                'after'      => 'affiliate_note',
            ],
            'gender' => [
                'type'       => 'ENUM',
                'constraint' => ['male', 'female', 'unisex', 'all'],
                'null'       => true,
                'after'      => 'segment',
            ],
            'budget' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => true,
                'after'      => 'gender',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tiktok_link', ['segment', 'gender', 'budget']);
    }
}
