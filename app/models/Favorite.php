<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

class Favorite extends Model
{
    protected string $table = 'favorites';

    public function toggle(int $userId, int $listingId): bool
    {
        $existing = $this->find($userId, $listingId);

        if ($existing) {
            $sql = "DELETE FROM {$this->table} WHERE user_id = :user_id AND listing_id = :listing_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':listing_id', $listingId, PDO::PARAM_INT);
            $stmt->execute();
            return false; // un-favorited
        }

        $sql = "INSERT INTO {$this->table} (user_id, listing_id, created_at)
                VALUES (:user_id, :listing_id, NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':listing_id', $listingId, PDO::PARAM_INT);
        $stmt->execute();
        return true; // favorited
    }

    public function find(int $userId, int $listingId): ?array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE user_id = :user_id AND listing_id = :listing_id
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':listing_id', $listingId, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getUserFavoriteIds(int $userId): array
    {
        $sql = "SELECT listing_id FROM {$this->table} WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return array_column($stmt->fetchAll(), 'listing_id');
    }

    public function getUserFavoritesWithListings(int $userId): array
    {
        $sql = "SELECT l.*, li.image_path AS primary_image,
                       u.first_name AS seller_first_name, u.last_name AS seller_last_name,
                       u.university_role AS seller_role, u.avg_response_minutes
                FROM {$this->table} f
                INNER JOIN listings l ON l.id = f.listing_id
                LEFT JOIN listing_images li ON li.listing_id = l.id AND li.is_primary = 1
                INNER JOIN users u ON u.id = l.user_id
                WHERE f.user_id = :user_id
                ORDER BY f.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
