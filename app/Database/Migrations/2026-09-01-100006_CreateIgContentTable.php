<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateIgContentTable extends Migration
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
            'ig_media_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'media_type' => [
                'type'       => 'ENUM',
                'constraint' => ['image', 'video', 'carousel', 'reels'],
            ],
            'caption' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'permalink' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'thumbnail_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'posted_at' => [
                'type' => 'DATETIME',
            ],
            'synced_at' => [
                'type' => 'DATETIME',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('ig_media_id');
        $this->forge->addForeignKey('ig_account_id', 'ig_account', 'id', '', 'CASCADE');
        $this->forge->createTable('ig_content');
    }

    public function down()
    {
        $this->forge->dropTable('ig_content');
    }
}
