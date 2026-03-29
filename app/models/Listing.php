<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

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
}
