<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Lets a content manager stop tracking a dead link without deleting its
 * history, and gives a per-link freshness signal without joining job logs.
 */
class AddActiveFieldsToTiktokLinkTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tiktok_link', [
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'after'      => 'data_source',
            ],
            'last_synced_at' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'is_active',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tiktok_link', ['is_active', 'last_synced_at']);
    }
}
