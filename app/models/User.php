<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class User extends Model
{
    protected string $user_table = 'users';

    public function findByEmail(string $email): array|false
    {
        $sql = "SELECT * FROM {$this->user_table} WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch();
    }

    public function createUser(array $data): bool
    {
        $sql = "INSERT INTO {$this->user_table} (
                    first_name,
                    last_name,
                    email,
                    password_hash,
                    university_role,
                    verification_status,
                    account_status,
                    created_at,
                    updated_at
                ) VALUES (
                    :first_name,
                    :last_name,
                    :email,
                    :password_hash,
                    :university_role,
                    :verification_status,
                    :account_status,
                    NOW(),
                    NOW()
                )";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':first_name', $data['first_name'], PDO::PARAM_STR);
        $stmt->bindValue(':last_name', $data['last_name'], PDO::PARAM_STR);
        $stmt->bindValue(':email', $data['email'], PDO::PARAM_STR);
        $stmt->bindValue(':password_hash', $data['password_hash'], PDO::PARAM_STR);
        $stmt->bindValue(':university_role', $data['university_role'], PDO::PARAM_STR);
        $stmt->bindValue(':verification_status', $data['verification_status'], PDO::PARAM_STR);
        $stmt->bindValue(':account_status', $data['account_status'], PDO::PARAM_STR);

        return $stmt->execute();
    }

    public function getById(int $id): array|false
    {
        $sql = "SELECT id, first_name, last_name, email, university_role, verification_status, account_status, created_at
                FROM {$this->user_table}
                WHERE id = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch();
    }
}