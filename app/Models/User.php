<?php

require_once dirname(__DIR__) . '/Core/Model.php';

class User extends Model
{
    public function findByEmail(string $email): ?array
    {
        $statement = $this->db->prepare(
            'SELECT id, name, email, phone, password, role FROM users WHERE email = :email LIMIT 1'
        );
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    public function emailExists(string $email): bool
    {
        $statement = $this->db->prepare('SELECT 1 FROM users WHERE email = :email LIMIT 1');
        $statement->execute(['email' => $email]);

        return (bool) $statement->fetchColumn();
    }

    public function createCustomer(string $name, string $email, string $password): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO users (name, email, password, role)
             VALUES (:name, :email, :password, :role)'
        );
        $statement->execute([
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'customer',
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function updateProfile(int $id, string $name, ?string $phone, ?string $newPassword = null): void
    {
        $sql = 'UPDATE users SET name = :name, phone = :phone';
        $parameters = [
            'name' => $name,
            'phone' => $phone,
            'id' => $id,
        ];

        if ($newPassword !== null) {
            $sql .= ', password = :password';
            $parameters['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        $statement = $this->db->prepare($sql . ' WHERE id = :id');
        $statement->execute($parameters);
    }
}
