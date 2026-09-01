<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateIgAccountTable extends Migration
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
            'ig_business_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'ig_username' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'fb_page_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'access_token_encrypted' => [
                'type' => 'TEXT',
            ],
            'token_expires_at' => [
                'type' => 'DATETIME',
            ],
            'connected_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'connected_at' => [
                'type' => 'DATETIME',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('connected_by', 'users', 'id', '', 'SET NULL');
        $this->forge->createTable('ig_account');
    }

    public function down()
    {
        $this->forge->dropTable('ig_account');
    }
}
