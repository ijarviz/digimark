<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateIgProfileInsightDailyTable extends Migration
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
            'snapshot_date' => [
                'type' => 'DATE',
            ],
            'follower_count' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'profile_visits' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'reach' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'impressions' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['ig_account_id', 'snapshot_date']);
        $this->forge->addForeignKey('ig_account_id', 'ig_account', 'id', '', 'CASCADE');
        $this->forge->createTable('ig_profile_insight_daily');
    }

    public function down()
    {
        $this->forge->dropTable('ig_profile_insight_daily');
    }
}
