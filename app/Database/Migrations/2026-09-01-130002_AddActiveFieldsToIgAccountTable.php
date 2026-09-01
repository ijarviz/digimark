<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * "The active account" was determined by "row with the highest id", which
 * is fragile and silently breaks the moment more than one row exists (e.g.
 * repeated reconnects). is_active makes the active row explicit.
 */
class AddActiveFieldsToIgAccountTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('ig_account', [
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'after'      => 'ads_connected_at',
            ],
            'disconnected_at' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'is_active',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('ig_account', ['is_active', 'disconnected_at']);
    }
}
