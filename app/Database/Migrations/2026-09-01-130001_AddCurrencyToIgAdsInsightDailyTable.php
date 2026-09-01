<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * `spend` was a bare decimal with no currency context. Ad accounts can be
 * denominated in different currencies, and summing spend across rows
 * without knowing the currency is actively misleading on the dashboard.
 */
class AddCurrencyToIgAdsInsightDailyTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('ig_ads_insight_daily', [
            'currency' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
                'after'      => 'spend',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('ig_ads_insight_daily', 'currency');
    }
}
