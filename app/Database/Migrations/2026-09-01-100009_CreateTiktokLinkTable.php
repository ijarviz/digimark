<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTiktokLinkTable extends Migration
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
            'url' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
            ],
            'tiktok_video_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'creator_handle' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'affiliate_note' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'data_source' => [
                'type'       => 'ENUM',
                'constraint' => ['oauth', 'scrape'],
                'default'    => 'scrape',
            ],
            'added_by' => [
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
        $this->forge->addForeignKey('added_by', 'users', 'id', '', 'SET NULL');
        $this->forge->createTable('tiktok_link');
    }

    public function down()
    {
        $this->forge->dropTable('tiktok_link');
    }
}
