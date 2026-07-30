<?php
declare(strict_types=1);

namespace Model;

class Member
{
    public function __construct(
        public readonly int     $id,
        public readonly string  $email,
        public readonly string  $username,
        public readonly string  $password,
        public readonly string  $displayName,
        public readonly ?string $profilePicture,
        public readonly string  $status,
        public readonly ?string $lastLogin,
        public readonly string  $createdAt,
    ) {}

    public function toSessionArray(): array
    {
        return [
            'id'              => $this->id,
            'email'           => $this->email,
            'username'        => $this->username,
            'display_name'    => $this->displayName,
            'profile_picture' => $this->profilePicture,
            'status'          => $this->status,
        ];
    }
}
