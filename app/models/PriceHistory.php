<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

class PriceHistory extends Model
{
    protected string $table = 'price_history';

    public function recordChange(int $listingId, ?string $oldPrice, ?string $newPrice): void
    {
        $sql = "INSERT INTO {$this->table} (listing_id, old_price, new_price, changed_at)
                VALUES (:listing_id, :old_price, :new_price, NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':listing_id', $listingId, PDO::PARAM_INT);
        $stmt->bindValue(':old_price', $oldPrice, $oldPrice === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':new_price', $newPrice, $newPrice === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->execute();
    }

    /**
     * Get the most recent price drop for a listing (if any).
     * Returns null if no drop occurred, or an array with old_price and new_price.
     */
    public function getLatestDrop(int $listingId): ?array
    {
        $sql = "SELECT old_price, new_price, changed_at
                FROM {$this->table}
                WHERE listing_id = :listing_id
                AND old_price IS NOT NULL
                AND new_price IS NOT NULL
                AND new_price < old_price
                ORDER BY changed_at DESC
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':listing_id', $listingId, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * Get latest drops for multiple listings at once (batch query for dashboard).
     */
    public function getLatestDropsForListings(array $listingIds): array
    {
        if (empty($listingIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($listingIds), '?'));

        $sql = "SELECT ph1.listing_id, ph1.old_price, ph1.new_price, ph1.changed_at
                FROM {$this->table} ph1
                INNER JOIN (
                    SELECT listing_id, MAX(changed_at) AS max_changed
                    FROM {$this->table}
                    WHERE listing_id IN ({$placeholders})
                    AND old_price IS NOT NULL
                    AND new_price IS NOT NULL
                    AND new_price < old_price
                    GROUP BY listing_id
                ) ph2 ON ph1.listing_id = ph2.listing_id AND ph1.changed_at = ph2.max_changed";

        $stmt = $this->db->prepare($sql);
        foreach ($listingIds as $i => $id) {
            $stmt->bindValue($i + 1, $id, PDO::PARAM_INT);
        }
        $stmt->execute();

        $results = [];
        foreach ($stmt->fetchAll() as $row) {
            $results[(int) $row['listing_id']] = $row;
        }

        return $results;
    }

    public function getFullHistory(int $listingId): array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE listing_id = :listing_id
                ORDER BY changed_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':listing_id', $listingId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
