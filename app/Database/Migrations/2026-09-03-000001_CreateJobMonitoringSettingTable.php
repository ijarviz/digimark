<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Single-row config table (same pattern as meta_app_config) toggling
 * whether the snapshot/publish/refresh Spark commands run at all —
 * BaseSnapshotCommand checks this before executing a job.
 */
class CreateJobMonitoringSettingTable extends Migration
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
            'is_enabled' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
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
        $this->forge->createTable('job_monitoring_setting');

        // Seed the single row so isEnabled() never has to guess a default.
        $this->db->table('job_monitoring_setting')->insert(['is_enabled' => 1]);
    }

    public function down()
    {
        $this->forge->dropTable('job_monitoring_setting');
    }
}
