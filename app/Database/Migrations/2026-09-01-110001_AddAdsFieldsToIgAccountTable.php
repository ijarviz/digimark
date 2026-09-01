<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Fase 2: Ads config lives on the existing ig_account row rather than a new
 * table, since ads_read was added to the same Meta App / same long-lived
 * token as Fase 1's IG connection (confirmed decision, not a separate
 * ad_account + token pair).
 */
class AddAdsFieldsToIgAccountTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('ig_account', [
            'ad_account_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 60,
                'null'       => true,
                'after'      => 'connected_at',
            ],
            'business_manager_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 60,
                'null'       => true,
                'after'      => 'ad_account_id',
            ],
            'ads_connected_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'business_manager_id',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('ig_account', ['ad_account_id', 'business_manager_id', 'ads_connected_at']);
    }
}
