<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table            = 'roles';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $allowedFields    = ['name'];
    protected $useTimestamps    = false;

    public function getByName(string $name): ?array
    {
        return $this->where('name', $name)->first();
    }
}
