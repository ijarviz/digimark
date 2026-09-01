<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table         = 'users';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'name',
        'email',
        'username',
        'password_hash',
        'role_id',
        'is_active',
        'last_login_at',
        'created_at',
    ];

    /**
     * Finds an active user by username, joined with the role name.
     */
    public function findActiveByUsername(string $username): ?array
    {
        return $this->select('users.*, roles.name as role_name')
            ->join('roles', 'roles.id = users.role_id')
            ->where('users.username', $username)
            ->where('users.is_active', 1)
            ->first();
    }

    public function verifyPassword(array $user, string $plainPassword): bool
    {
        return password_verify($plainPassword, $user['password_hash']);
    }

    public function touchLastLogin(int $userId): void
    {
        $this->update($userId, ['last_login_at' => date('Y-m-d H:i:s')]);
    }

    public static function hashPassword(string $plainPassword): string
    {
        return password_hash($plainPassword, PASSWORD_BCRYPT);
    }
}
