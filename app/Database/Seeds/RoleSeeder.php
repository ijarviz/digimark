<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run()
    {
        foreach (['admin', 'content_manager', 'viewer'] as $name) {
            $exists = $this->db->table('roles')->where('name', $name)->get()->getRow();

            if (! $exists) {
                $this->db->table('roles')->insert(['name' => $name]);
            }
        }
    }
}
