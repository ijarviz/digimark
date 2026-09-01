<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * TDD §3.5's publish_queue schema has no way to (a) resume polling a
 * container across separate 5-minute cron runs without creating a
 * duplicate, (b) detect a container stuck in IN_PROGRESS to time it out,
 * or (c) count "published in the last 24h" for the ~25/day rate limit.
 * All three are explicit functional requirements in the Fase 3 build
 * prompt, so these columns are additive/justified, not scope creep.
 */
class AddOrchestrationFieldsToPublishQueueTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('publish_queue', [
            'container_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'status',
            ],
            'processing_started_at' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'container_id',
            ],
            'published_at' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'processing_started_at',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('publish_queue', ['container_id', 'processing_started_at', 'published_at']);
    }
}
