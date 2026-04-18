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
}
