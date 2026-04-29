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
        $sql = "SELECT id, first_name, last_name, email, university_role, verification_status,
                       account_status, avg_response_minutes, average_rating, total_reviews, bio, created_at
                FROM {$this->user_table}
                WHERE id = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Calculate and cache a seller's average response time in minutes.
     * Based on conversations where they are the listing owner and responded.
     */
    public function updateResponseTime(int $userId): void
    {
        // Find the average time between the first message in a conversation
        // (from buyer) and the seller's first reply
        $sql = "SELECT AVG(TIMESTAMPDIFF(MINUTE, buyer_msg.created_at, seller_reply.created_at)) AS avg_mins
                FROM conversations conv
                INNER JOIN listings l ON l.id = conv.listing_id AND l.user_id = :user_id
                INNER JOIN (
                    SELECT conversation_id, MIN(created_at) AS created_at
                    FROM messages
                    WHERE sender_id != :user_id2
                    GROUP BY conversation_id
                ) buyer_msg ON buyer_msg.conversation_id = conv.id
                INNER JOIN (
                    SELECT conversation_id, MIN(created_at) AS created_at
                    FROM messages
                    WHERE sender_id = :user_id3
                    GROUP BY conversation_id
                ) seller_reply ON seller_reply.conversation_id = conv.id
                WHERE seller_reply.created_at > buyer_msg.created_at";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id2', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id3', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch();
        $avgMinutes = $result['avg_mins'] !== null ? (int) round((float) $result['avg_mins']) : null;

        $updateSql = "UPDATE {$this->user_table} SET avg_response_minutes = :avg WHERE id = :id";
        $updateStmt = $this->db->prepare($updateSql);
        $updateStmt->bindValue(':avg', $avgMinutes, $avgMinutes === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $updateStmt->bindValue(':id', $userId, PDO::PARAM_INT);
        $updateStmt->execute();
    }

    /**
     * Format response time for display.
     */
    public static function formatResponseTime(?int $minutes): ?string
    {
        if ($minutes === null) {
            return null;
        }

        if ($minutes < 60) {
            return "Responds in ~{$minutes}min";
        }

        $hours = (int) round($minutes / 60);

        if ($hours < 24) {
            return "Responds in ~{$hours}hr";
        }

        $days = (int) round($hours / 24);
        return "Responds in ~{$days}d";
    }
}