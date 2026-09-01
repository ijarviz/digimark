<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Fase 3 (Publish & Schedule) — schema prepared ahead of time so no future
 * migration headache, but this table is not referenced by any Fase 1
 * controller/service/route.
 */
class CreatePublishQueueTable extends Migration
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
            'ig_account_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'caption' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'media_urls' => [
                'type' => 'JSON',
            ],
            'media_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'scheduled_at' => [
                'type' => 'DATETIME',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'processing', 'published', 'failed'],
                'default'    => 'pending',
            ],
            'ig_media_id_result' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'error_message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('ig_account_id', 'ig_account', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('created_by', 'users', 'id', '', 'SET NULL');
        $this->forge->createTable('publish_queue');
    }

    public function down()
    {
        $this->forge->dropTable('publish_queue');
    }
}
