<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

class Tables extends Model
{

    public function categoryOptions(): array
    {
        $query = "SELECT id, name
                FROM categories
                WHERE is_active = 1
                ORDER BY id ASC";
        $statement2 = $this->db->prepare($query);
        $statement2->execute();
        $categories = $statement2->fetchAll();
        $statement2->closeCursor();
        return $categories;
    }
}
