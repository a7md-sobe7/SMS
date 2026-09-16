<?php

declare(strict_types=1);

namespace App\Repositories;

class UserRepository extends BaseRepository
{
    protected string $table = 'users';

    public function findByEmail(string $email): ?array
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function findByUsername(string $username): ?array
    {
        return $this->findOneBy(['username' => $username]);
    }

    public function findByUsernameOrEmail(string $identifier): ?array
    {
        $sql = "SELECT * FROM `users` WHERE `username` = :username OR `email` = :email LIMIT 1";
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute([
            'username' => $identifier,
            'email'    => $identifier
        ]);
        $user = $stmt->fetch();
        return $user !== false ? $user : null;
    }

    public function updatePassword(int $userId, string $passwordHash): bool
    {
        return $this->update($userId, ['password_hash' => $passwordHash]);
    }
}
