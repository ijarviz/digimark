<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * A content_manager can now cancel a still-pending queue item. `cancelled`
 * is a terminal status kept in the table (not a row delete) so the audit
 * trail and history stay intact. The queue worker already filters on
 * `status = 'pending'`, so a cancelled row is simply never picked up.
 */
class AddCancelledStatusToPublishQueue extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('publish_queue', [
            'status' => [
                'name'       => 'status',
                'type'       => 'ENUM',
                'constraint' => ['pending', 'processing', 'published', 'failed', 'cancelled'],
                'default'    => 'pending',
            ],
        ]);
    }

    public function down()
    {
        $this->db->table('publish_queue')->where('status', 'cancelled')->update(['status' => 'failed']);

        $this->forge->modifyColumn('publish_queue', [
            'status' => [
                'name'       => 'status',
                'type'       => 'ENUM',
                'constraint' => ['pending', 'processing', 'published', 'failed'],
                'default'    => 'pending',
            ],
        ]);
    }
}
