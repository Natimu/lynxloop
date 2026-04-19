<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;
use Throwable;

class Message extends Model
{
    protected string $table = 'messages';

    /**
     * Send a quick message about a listing. Creates conversation if needed.
     * Returns the conversation ID.
     */
    public function sendQuickMessage(int $senderId, int $listingOwnerId, int $listingId, string $body): int
    {
        $this->db->beginTransaction();

        try {
            // Check for existing conversation between these users about this listing
            $convId = $this->findConversation($senderId, $listingOwnerId, $listingId);

            if ($convId === null) {
                $convId = $this->createConversation($senderId, $listingOwnerId, $listingId);
            }

            // Insert the message
            $sql = "INSERT INTO {$this->table} (conversation_id, sender_id, message_body, created_at)
                    VALUES (:conv_id, :sender_id, :body, NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':conv_id', $convId, PDO::PARAM_INT);
            $stmt->bindValue(':sender_id', $senderId, PDO::PARAM_INT);
            $stmt->bindValue(':body', $body, PDO::PARAM_STR);
            $stmt->execute();

            // Create notification for the listing owner
            $this->createNotification($listingOwnerId, $senderId, $listingId, $body);

            $this->db->commit();

            return $convId;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    private function findConversation(int $userId1, int $userId2, int $listingId): ?int
    {
        $sql = "SELECT c.id FROM conversations c
                INNER JOIN conversation_participants cp1 ON cp1.conversation_id = c.id AND cp1.user_id = :u1
                INNER JOIN conversation_participants cp2 ON cp2.conversation_id = c.id AND cp2.user_id = :u2
                WHERE c.listing_id = :listing_id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':u1', $userId1, PDO::PARAM_INT);
        $stmt->bindValue(':u2', $userId2, PDO::PARAM_INT);
        $stmt->bindValue(':listing_id', $listingId, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch();

        return $row ? (int) $row['id'] : null;
    }

    private function createConversation(int $userId1, int $userId2, int $listingId): int
    {
        $sql = "INSERT INTO conversations (listing_id, created_by, created_at)
                VALUES (:listing_id, :created_by, NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':listing_id', $listingId, PDO::PARAM_INT);
        $stmt->bindValue(':created_by', $userId1, PDO::PARAM_INT);
        $stmt->execute();

        $convId = (int) $this->db->lastInsertId();

        // Add both participants
        $partSql = "INSERT INTO conversation_participants (conversation_id, user_id, joined_at)
                    VALUES (:conv_id, :user_id, NOW())";

        $partStmt = $this->db->prepare($partSql);

        $partStmt->bindValue(':conv_id', $convId, PDO::PARAM_INT);
        $partStmt->bindValue(':user_id', $userId1, PDO::PARAM_INT);
        $partStmt->execute();

        $partStmt->bindValue(':conv_id', $convId, PDO::PARAM_INT);
        $partStmt->bindValue(':user_id', $userId2, PDO::PARAM_INT);
        $partStmt->execute();

        return $convId;
    }

    private function createNotification(int $recipientId, int $senderId, int $listingId, string $body): void
    {
        $sql = "INSERT INTO notifications (user_id, type, title, body, reference_id, created_at)
                VALUES (:user_id, 'message', :title, :body, :ref_id, NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $recipientId, PDO::PARAM_INT);
        $stmt->bindValue(':title', 'New message about your listing', PDO::PARAM_STR);
        $stmt->bindValue(':body', mb_substr($body, 0, 200), PDO::PARAM_STR);
        $stmt->bindValue(':ref_id', $listingId, PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * Get unread message count for a user.
     */
    public function getUnreadCount(int $userId): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} m
                INNER JOIN conversation_participants cp ON cp.conversation_id = m.conversation_id AND cp.user_id = :user_id
                WHERE m.sender_id != :user_id2 AND m.is_read = 0";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id2', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }
}
