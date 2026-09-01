<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTiktokInsightDailyTable extends Migration
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
            'tiktok_link_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'snapshot_date' => [
                'type' => 'DATE',
            ],
            'views' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'likes' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'comments' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'shares' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'source' => [
                'type'       => 'ENUM',
                'constraint' => ['oauth', 'scrape'],
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['tiktok_link_id', 'snapshot_date']);
        $this->forge->addForeignKey('tiktok_link_id', 'tiktok_link', 'id', '', 'CASCADE');
        $this->forge->createTable('tiktok_insight_daily');
    }

    public function down()
    {
        $this->forge->dropTable('tiktok_insight_daily');
    }
}
