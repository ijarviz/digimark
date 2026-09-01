<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateJobRunLogTable extends Migration
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
            'job_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['success', 'partial', 'failed'],
            ],
            'started_at' => [
                'type' => 'DATETIME',
            ],
            'finished_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'error_message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('job_name');
        $this->forge->createTable('job_run_log');
    }

    public function down()
    {
        $this->forge->dropTable('job_run_log');
    }
}
