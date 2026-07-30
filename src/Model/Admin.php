<?php
/**
 * Model\Admin
 */
declare(strict_types=1);

namespace Model;

class Admin
{
    public function __construct(
        public readonly int    $id,
        public readonly string $username,
        public readonly string $password,   // bcrypt hash
        public readonly string $displayName,
        public readonly string $role,
        public readonly ?string $lastLogin,
        public readonly string $createdAt,
    ) {}

    public function toSessionArray(): array
    {
        return [
            'id'           => $this->id,
            'username'     => $this->username,
            'display_name' => $this->displayName,
            'role'         => $this->role,
        ];
    }
}
