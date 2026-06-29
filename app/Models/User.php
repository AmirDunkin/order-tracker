<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

class User extends Model
{
    /**
     * @return array<string, mixed>|false
     */
    public function findById(int $id): array|false
    {
        return $this->fetchOne(
            'SELECT id, name, email, password, role, created_at FROM users WHERE id = :id',
            ['id' => $id]
        );
    }

    /**
     * @return array<string, mixed>|false
     */
    public function findByEmail(string $email): array|false
    {
        return $this->fetchOne(
            'SELECT id, name, email, password, role, created_at FROM users WHERE email = :email',
            ['email' => $email]
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function findAll(?string $role = null): array
    {
        if ($role !== null) {
            return $this->fetchAll(
                'SELECT id, name, email, role, created_at FROM users WHERE role = :role ORDER BY created_at DESC',
                ['role' => $role]
            );
        }

        return $this->fetchAll(
            'SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC'
        );
    }

    /**
     * @param array{name: string, email: string, password: string, role: string} $data
     */
    public function create(array $data): int
    {
        $id = $this->lastInsertId(
            'INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)',
            [
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                'role'     => $data['role'],
            ]
        );

        return (int) $id;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];

        if (isset($data['name'])) {
            $fields[] = 'name = :name';
            $params['name'] = $data['name'];
        }

        if (isset($data['email'])) {
            $fields[] = 'email = :email';
            $params['email'] = $data['email'];
        }

        if (isset($data['password']) && $data['password'] !== '') {
            $fields[] = 'password = :password';
            $params['password'] = password_hash((string) $data['password'], PASSWORD_DEFAULT);
        }

        if (isset($data['role'])) {
            $fields[] = 'role = :role';
            $params['role'] = $data['role'];
        }

        if ($fields === []) {
            return false;
        }

        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id';

        return $this->execute($sql, $params);
    }

    public function delete(int $id): bool
    {
        return $this->execute('DELETE FROM users WHERE id = :id', ['id' => $id]);
    }

    public function emailExists(string $email): bool
    {
        return $this->findByEmail($email) !== false;
    }

    public function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }
}
