<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * "Improve Me" (Jarvis Power menu) — an admin-only prompt box that bridges
 * into a claude.ai cloud routine via a GitHub issue: submitting a prompt
 * here creates an issue labeled `improve-me` on the project repo, which
 * fires the routine to work on this codebase on a new branch and open a
 * Pull Request (never a direct push to main). This table records what we
 * know at submit time (the issue we created); the routine's outcome (PR
 * link, or why it declined) lands as a comment on that GitHub issue and
 * is polled back in by ImproveMeService::recentRequests() on every admin
 * page load — `result_comment` / `result_pr_url` cache it once settled.
 *
 * Ported from automedia (app/Database/Migrations/*AiImproveRequests*),
 * merged into one migration and with the RBAC permission seed dropped:
 * digimark gates this in the route + controller on role_name === 'admin'.
 */
class CreateAiImproveRequestsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                  => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'prompt'              => ['type' => 'TEXT'],
            'status'              => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'submitted'],
            'github_issue_number' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'github_issue_url'    => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'error_message'       => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'result_comment'      => ['type' => 'TEXT', 'null' => true],
            'result_pr_url'       => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'requested_by'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at'          => ['type' => 'DATETIME', 'null' => true],
            'updated_at'          => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('requested_by', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('ai_improve_requests');
    }

    public function down()
    {
        $this->forge->dropTable('ai_improve_requests');
    }
}
