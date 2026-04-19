<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

class SavedSearch extends Model
{
    protected string $table = 'saved_searches';

    public function createSearch(int $userId, string $query, ?int $categoryId = null): int
    {
        $sql = "INSERT INTO {$this->table} (user_id, query, category_id, created_at)
                VALUES (:user_id, :query, :category_id, NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':query', $query, PDO::PARAM_STR);
        $stmt->bindValue(':category_id', $categoryId, $categoryId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->execute();

        return (int) $this->db->lastInsertId();
    }

    public function getByUser(int $userId): array
    {
        $sql = "SELECT ss.*, c.name AS category_name
                FROM {$this->table} ss
                LEFT JOIN categories c ON c.id = ss.category_id
                WHERE ss.user_id = :user_id AND ss.is_active = 1
                ORDER BY ss.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function deleteSearch(int $id, int $userId): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id AND user_id = :user_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function countMatchingListings(string $query, ?int $categoryId = null): int
    {
        $sql = "SELECT COUNT(*) FROM listings
                WHERE status = 'active'
                AND (title LIKE :query OR description LIKE :query2)";
        $params = [':query' => "%{$query}%", ':query2' => "%{$query}%"];

        if ($categoryId !== null) {
            $sql .= " AND category_id = :category_id";
            $params[':category_id'] = $categoryId;
        }

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }
}
