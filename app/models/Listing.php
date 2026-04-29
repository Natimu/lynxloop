<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;
use Throwable;

class Listing extends Model
{
    protected string $table = 'listings';

    public function createListing(array $attributes): int
    {
        $sql = "INSERT INTO {$this->table}
            (user_id, category_id, title, description, item_condition, price, is_trade_allowed,
             quantity, brand, location, status, pickup_only, created_at, updated_at)
            VALUES (:user_id, :category_id, :title, :description, :item_condition, :price, :is_trade_allowed,
                    :quantity, :brand, :location, :status, :pickup_only, NOW(), NOW())";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':user_id', $attributes['user_id'], PDO::PARAM_INT);
        $stmt->bindValue(':category_id', $attributes['category_id'], PDO::PARAM_INT);
        $stmt->bindValue(':title', $attributes['title'], PDO::PARAM_STR);
        $stmt->bindValue(':description', $attributes['description'], PDO::PARAM_STR);
        $stmt->bindValue(':item_condition', $attributes['item_condition'], PDO::PARAM_STR);
        $priceParam = $attributes['price'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR;
        $stmt->bindValue(':price', $attributes['price'], $priceParam);
        $stmt->bindValue(':is_trade_allowed', $attributes['is_trade_allowed'], PDO::PARAM_INT);
        $stmt->bindValue(':quantity', $attributes['quantity'], PDO::PARAM_INT);
        $brandParam = $attributes['brand'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR;
        $stmt->bindValue(':brand', $attributes['brand'], $brandParam);
        $locationParam = $attributes['location'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR;
        $stmt->bindValue(':location', $attributes['location'], $locationParam);
        $stmt->bindValue(':status', $attributes['status'], PDO::PARAM_STR);
        $stmt->bindValue(':pickup_only', $attributes['pickup_only'], PDO::PARAM_INT);

        $stmt->execute();

        return (int) $this->db->lastInsertId();
    }

    public function addListingImage(int $listingId, string $imagePath, bool $isPrimary, int $sortOrder): void
    {
        $sql = "INSERT INTO listing_images (listing_id, image_path, is_primary, sort_order, created_at)
            VALUES (:listing_id, :image_path, :is_primary, :sort_order, NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':listing_id', $listingId, PDO::PARAM_INT);
        $stmt->bindValue(':image_path', $imagePath, PDO::PARAM_STR);
        $stmt->bindValue(':is_primary', $isPrimary ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':sort_order', $sortOrder, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function createListingWithImages(array $attributes, array $images): int
    {
        $this->db->beginTransaction();

        try {
            $listingId = $this->createListing($attributes);

            foreach ($images as $index => $imagePath) {
                $this->addListingImage($listingId, $imagePath, $index === 0, $index);
            }

            $this->db->commit();

            return $listingId;
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $exception;
        }
    }

    // ── Bump ────────────────────────────────────────────────

    /**
     * Bump a listing to the top. Returns false if already bumped within 24 hours.
     */
    public function bump(int $listingId, int $userId): bool
    {
        $listing = $this->findById($listingId);

        if (!$listing || (int) $listing['user_id'] !== $userId) {
            return false;
        }

        if ($listing['last_bumped_at'] !== null) {
            $lastBump = strtotime($listing['last_bumped_at']);
            if ((time() - $lastBump) < 86400) {
                return false; // 24-hour cooldown
            }
        }

        $sql = "UPDATE {$this->table} SET last_bumped_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $listingId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Check if a listing can be bumped (24h cooldown passed).
     */
    public function canBump(int $listingId): bool
    {
        $listing = $this->findById($listingId);

        if (!$listing) {
            return false;
        }

        if ($listing['last_bumped_at'] === null) {
            return true;
        }

        return (time() - strtotime($listing['last_bumped_at'])) >= 86400;
    }

    // ── Search ──────────────────────────────────────────────

    /**
     * Search active listings by keyword, with optional category filter.
     * Results are sorted by bump time (bumped first), then newest.
     */
    public function search(string $query, ?int $categoryId = null, int $limit = 24, int $offset = 0): array
    {
        $sql = "SELECT l.*, li.image_path AS primary_image,
                       u.first_name AS seller_first_name, u.last_name AS seller_last_name,
                       u.university_role AS seller_role, u.profile_image AS seller_avatar,
                       u.avg_response_minutes
                FROM {$this->table} l
                LEFT JOIN listing_images li ON li.listing_id = l.id AND li.is_primary = 1
                INNER JOIN users u ON u.id = l.user_id
                WHERE l.status = 'active'
                AND (l.title LIKE :query OR l.description LIKE :query2)";

        $params = [':query' => "%{$query}%", ':query2' => "%{$query}%"];

        if ($categoryId !== null) {
            $sql .= " AND l.category_id = :category_id";
            $params[':category_id'] = $categoryId;
        }

        $sql .= " ORDER BY l.last_bumped_at DESC, l.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // ── Similar Listings ────────────────────────────────────

    /**
     * Get similar listings in the same category, excluding the given listing.
     */
    public function getSimilar(int $listingId, int $categoryId, int $limit = 4): array
    {
        $sql = "SELECT l.*, li.image_path AS primary_image,
                       u.first_name AS seller_first_name, u.last_name AS seller_last_name,
                       u.university_role AS seller_role, u.profile_image AS seller_avatar,
                       u.avg_response_minutes
                FROM {$this->table} l
                LEFT JOIN listing_images li ON li.listing_id = l.id AND li.is_primary = 1
                INNER JOIN users u ON u.id = l.user_id
                WHERE l.status = 'active'
                AND l.category_id = :category_id
                AND l.id != :listing_id
                ORDER BY l.last_bumped_at DESC, l.created_at DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->bindValue(':listing_id', $listingId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // ── Listing Detail with Images ──────────────────────────

    /**
     * Get a single listing with all its images and seller info.
     */
    public function getDetailWithImages(int $listingId): ?array
    {
        $sql = "SELECT l.*, c.name AS category_name,
                       u.first_name AS seller_first_name, u.last_name AS seller_last_name,
                       u.university_role AS seller_role, u.profile_image AS seller_avatar,
                       u.average_rating AS seller_rating, u.total_reviews AS seller_reviews,
                       u.avg_response_minutes
                FROM {$this->table} l
                INNER JOIN categories c ON c.id = l.category_id
                INNER JOIN users u ON u.id = l.user_id
                WHERE l.id = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $listingId, PDO::PARAM_INT);
        $stmt->execute();

        $listing = $stmt->fetch();

        if (!$listing) {
            return null;
        }

        // Get all images
        $imgSql = "SELECT * FROM listing_images WHERE listing_id = :id ORDER BY sort_order ASC";
        $imgStmt = $this->db->prepare($imgSql);
        $imgStmt->bindValue(':id', $listingId, PDO::PARAM_INT);
        $imgStmt->execute();

        $listing['images'] = $imgStmt->fetchAll();

        // Increment view count
        $this->db->prepare("UPDATE {$this->table} SET view_count = view_count + 1 WHERE id = :id")
                 ->execute([':id' => $listingId]);

        return $listing;
    }

    // ── Active Listings by Category (for dashboard) ─────────

    /**
     * Get active listings grouped by category, sorted by bump then newest.
     */
    public function getActiveByCategory(?int $categoryId = null, int $limit = 12): array
    {
        $sql = "SELECT l.*, li.image_path AS primary_image,
                       u.first_name AS seller_first_name, u.last_name AS seller_last_name,
                       u.university_role AS seller_role, u.profile_image AS seller_avatar,
                       u.avg_response_minutes
                FROM {$this->table} l
                LEFT JOIN listing_images li ON li.listing_id = l.id AND li.is_primary = 1
                INNER JOIN users u ON u.id = l.user_id
                WHERE l.status = 'active'";

        if ($categoryId !== null) {
            $sql .= " AND l.category_id = :category_id";
        }

        $sql .= " ORDER BY l.last_bumped_at DESC, l.created_at DESC LIMIT :limit";

        $stmt = $this->db->prepare($sql);

        if ($categoryId !== null) {
            $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // ── User's own listings (for manage panel) ──────────────

    public function getByUser(int $userId): array
    {
        $sql = "SELECT l.*, li.image_path AS primary_image
                FROM {$this->table} l
                LEFT JOIN listing_images li ON li.listing_id = l.id AND li.is_primary = 1
                WHERE l.user_id = :user_id
                ORDER BY l.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // ── Update price (with history tracking) ────────────────

    public function updatePrice(int $listingId, int $userId, ?string $newPrice): bool
    {
        $listing = $this->findById($listingId);

        if (!$listing || (int) $listing['user_id'] !== $userId) {
            return false;
        }

        $sql = "UPDATE {$this->table} SET price = :price, updated_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':price', $newPrice, $newPrice === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':id', $listingId, PDO::PARAM_INT);

        return $stmt->execute();
    }
}
