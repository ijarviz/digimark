<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Marks a tiktok_link row that originated from Influencer Discovery
 * (Apify search) rather than being added manually, so the TikTok Links
 * list/audit trail can show where a link came from.
 */
class AddDiscoverySourceToTiktokLinkTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tiktok_link', [
            'discovery_source' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'added_by',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tiktok_link', ['discovery_source']);
    }
}
