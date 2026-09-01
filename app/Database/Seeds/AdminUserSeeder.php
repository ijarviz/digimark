<?php

namespace App\Database\Seeds;

use App\Models\UserModel;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        $username = env('SEED_ADMIN_USERNAME') ?: 'admin';
        $email    = env('SEED_ADMIN_EMAIL') ?: 'admin@local.dev';
        $password = env('SEED_ADMIN_PASSWORD');

        $generated = false;

        if (! $password) {
            $password  = bin2hex(random_bytes(6));
            $generated = true;
        }

        $existing = $this->db->table('users')->where('username', $username)->get()->getRow();

        if ($existing) {
            CLI::write("Admin user '{$username}' already exists — skipping.", 'yellow');

            return;
        }

        $adminRole = $this->db->table('roles')->where('name', 'admin')->get()->getRow();

        if (! $adminRole) {
            CLI::error('Role "admin" not found — run RoleSeeder first.');

            return;
        }

        (new UserModel())->insert([
            'name'          => 'Administrator',
            'email'         => $email,
            'username'      => $username,
            'password_hash' => UserModel::hashPassword($password),
            'role_id'       => $adminRole->id,
            'is_active'     => 1,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        CLI::write('Admin user created:', 'green');
        CLI::write("  username: {$username}");

        if ($generated) {
            CLI::write("  password: {$password}", 'yellow');
            CLI::write('  CHANGE THIS PASSWORD IMMEDIATELY — it was auto-generated because SEED_ADMIN_PASSWORD was not set in .env.', 'red');
        } else {
            CLI::write('  password: (from SEED_ADMIN_PASSWORD in .env)');
        }
    }
}
