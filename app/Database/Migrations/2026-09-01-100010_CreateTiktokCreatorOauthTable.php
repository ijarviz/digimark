<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Inert in Fase 1 — only diisi kalau kreator setuju connect resmi (skeleton
 * for a future TikTok Display API OAuth path). No controller/service wires
 * real functionality against this table yet.
 */
class CreateTiktokCreatorOauthTable extends Migration
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
            'creator_handle' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'access_token_encrypted' => [
                'type' => 'TEXT',
            ],
            'token_expires_at' => [
                'type' => 'DATETIME',
            ],
            'connected_at' => [
                'type' => 'DATETIME',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('tiktok_creator_oauth');
    }

    public function down()
    {
        $this->forge->dropTable('tiktok_creator_oauth');
    }
}
