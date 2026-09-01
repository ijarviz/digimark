<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * retry_count lets manual/automatic retries be capped instead of unlimited;
 * updated_at gives a generic "last state change" timestamp (the existing
 * published_at/processing_started_at only cover specific transitions).
 */
class AddTrackingFieldsToPublishQueueTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('publish_queue', [
            'retry_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'after'      => 'error_message',
            ],
            'updated_at' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'retry_count',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('publish_queue', ['retry_count', 'updated_at']);
    }
}
