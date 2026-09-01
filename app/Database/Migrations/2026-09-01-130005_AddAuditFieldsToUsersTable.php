<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Role/status changes were only visible in audit_log, not on the user row
 * itself. updated_at/password_changed_at make "when did this account last
 * change" a direct query instead of an audit_log join.
 */
class AddAuditFieldsToUsersTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'updated_at' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'created_at',
            ],
            'password_changed_at' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'updated_at',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['updated_at', 'password_changed_at']);
    }
}
