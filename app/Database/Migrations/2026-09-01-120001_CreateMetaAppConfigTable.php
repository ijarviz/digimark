<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Single-row config table holding the Meta App credentials used by both
 * Instagram Content Publish (Fase 3) and Ads Read (Fase 2) — they share
 * the same app/token per the confirmed "same app" design decision, so one
 * admin-editable credential set covers both instead of a per-feature key.
 * Previously these were .env-only placeholders; this lets an admin set
 * them from the UI without server file access.
 */
class CreateMetaAppConfigTable extends Migration
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
            'app_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'app_secret_encrypted' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'redirect_uri' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'updated_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('updated_by', 'users', 'id', '', 'SET NULL');
        $this->forge->createTable('meta_app_config');
    }

    public function down()
    {
        $this->forge->dropTable('meta_app_config');
    }
}
