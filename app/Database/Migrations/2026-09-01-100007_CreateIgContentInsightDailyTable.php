<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateIgContentInsightDailyTable extends Migration
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
            'ig_content_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'snapshot_date' => [
                'type' => 'DATE',
            ],
            'reach' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'impressions' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'likes' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'comments' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'shares' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'saves' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'plays' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['ig_content_id', 'snapshot_date']);
        $this->forge->addForeignKey('ig_content_id', 'ig_content', 'id', '', 'CASCADE');
        $this->forge->createTable('ig_content_insight_daily');
    }

    public function down()
    {
        $this->forge->dropTable('ig_content_insight_daily');
    }
}
