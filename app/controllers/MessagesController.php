<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Message;
use App\Models\User;
use PDO;
use App\Core\Database;

class MessagesController extends Controller
{
    private Message $messageModel;

    public function __construct()
    {
        $this->messageModel = new Message();
    }

    /**
     * Inbox — list all conversations for the current user.
     */
    public function inbox(): void
    {
        Auth::requireLogin();

        $userId = (int) $_SESSION['user_id'];
        $conversations = $this->getConversationsForUser($userId);

        $this->view('messages/inbox', [
            'title' => 'Messages | Lynxloop',
            'isLoggedIn' => true,
            'firstName' => $_SESSION['user_first_name'] ?? null,
            'conversations' => $conversations,
        ]);
    }

    /**
     * View a single conversation thread.
     */
    public function show(): void
    {
        Auth::requireLogin();

        $userId = (int) $_SESSION['user_id'];
        $conversationId = (int) ($_GET['id'] ?? 0);

        if ($conversationId <= 0) {
            $this->redirect('/messages');
        }

        // Verify user is a participant
        if (!$this->isParticipant($userId, $conversationId)) {
            $_SESSION['flash_error'] = 'Conversation not found.';
            $this->redirect('/messages');
        }

        $conversation = $this->getConversationDetail($conversationId, $userId);
        $messages = $this->getMessagesForConversation($conversationId);

        // Mark messages as read
        $this->markAsRead($conversationId, $userId);

        $this->view('messages/show', [
            'title' => 'Conversation | Lynxloop',
            'isLoggedIn' => true,
            'firstName' => $_SESSION['user_first_name'] ?? null,
            'conversation' => $conversation,
            'messages' => $messages,
            'currentUserId' => $userId,
        ]);
    }

    /**
     * Reply to a conversation.
     */
    public function reply(): void
    {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/messages');
        }

        $userId = (int) $_SESSION['user_id'];
        $conversationId = (int) ($_POST['conversation_id'] ?? 0);
        $body = trim($_POST['message'] ?? '');

        if ($conversationId <= 0 || $body === '') {
            $_SESSION['flash_error'] = 'Message cannot be empty.';
            $this->redirect('/messages/show?id=' . $conversationId);
        }

        if (!$this->isParticipant($userId, $conversationId)) {
            $_SESSION['flash_error'] = 'Conversation not found.';
            $this->redirect('/messages');
        }

        $db = Database::getInstance()->getConnection();

        // Insert the reply
        $sql = "INSERT INTO messages (conversation_id, sender_id, message_body, created_at)
                VALUES (:conv_id, :sender_id, :body, NOW())";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':conv_id', $conversationId, PDO::PARAM_INT);
        $stmt->bindValue(':sender_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':body', $body, PDO::PARAM_STR);
        $stmt->execute();

        // Create notification for the other participant
        $otherUserId = $this->getOtherParticipant($conversationId, $userId);
        if ($otherUserId !== null) {
            $notifSql = "INSERT INTO notifications (user_id, type, title, body, reference_id, created_at)
                         VALUES (:user_id, 'message', 'New message', :body, :ref_id, NOW())";
            $notifStmt = $db->prepare($notifSql);
            $notifStmt->bindValue(':user_id', $otherUserId, PDO::PARAM_INT);
            $notifStmt->bindValue(':body', mb_substr($body, 0, 200), PDO::PARAM_STR);
            $notifStmt->bindValue(':ref_id', $conversationId, PDO::PARAM_INT);
            $notifStmt->execute();

            // Update seller response time
            (new User())->updateResponseTime($userId);
        }

        $this->redirect('/messages/show?id=' . $conversationId);
    }

    // ── Private helpers ─────────────────────────────────────

    private function getConversationsForUser(int $userId): array
    {
        $db = Database::getInstance()->getConnection();

        $sql = "SELECT c.id AS conversation_id, c.listing_id, c.created_at AS conv_created,
                       l.title AS listing_title, li.image_path AS listing_image,
                       other_user.id AS other_user_id,
                       other_user.first_name AS other_first_name,
                       other_user.last_name AS other_last_name,
                       latest_msg.message_body AS last_message,
                       latest_msg.created_at AS last_message_at,
                       latest_msg.sender_id AS last_sender_id,
                       (SELECT COUNT(*) FROM messages m2
                        WHERE m2.conversation_id = c.id
                        AND m2.sender_id != :user_id3
                        AND m2.is_read = 0) AS unread_count
                FROM conversations c
                INNER JOIN conversation_participants cp ON cp.conversation_id = c.id AND cp.user_id = :user_id
                INNER JOIN conversation_participants cp2 ON cp2.conversation_id = c.id AND cp2.user_id != :user_id2
                INNER JOIN users other_user ON other_user.id = cp2.user_id
                LEFT JOIN listings l ON l.id = c.listing_id
                LEFT JOIN listing_images li ON li.listing_id = l.id AND li.is_primary = 1
                INNER JOIN (
                    SELECT m.conversation_id, m.message_body, m.created_at, m.sender_id
                    FROM messages m
                    INNER JOIN (
                        SELECT conversation_id, MAX(created_at) AS max_created
                        FROM messages
                        GROUP BY conversation_id
                    ) latest ON m.conversation_id = latest.conversation_id AND m.created_at = latest.max_created
                ) latest_msg ON latest_msg.conversation_id = c.id
                ORDER BY latest_msg.created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id2', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id3', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    private function getConversationDetail(int $conversationId, int $userId): ?array
    {
        $db = Database::getInstance()->getConnection();

        $sql = "SELECT c.id AS conversation_id, c.listing_id,
                       l.title AS listing_title, l.price AS listing_price,
                       li.image_path AS listing_image,
                       other_user.id AS other_user_id,
                       other_user.first_name AS other_first_name,
                       other_user.last_name AS other_last_name,
                       other_user.university_role AS other_role
                FROM conversations c
                INNER JOIN conversation_participants cp2 ON cp2.conversation_id = c.id AND cp2.user_id != :user_id
                INNER JOIN users other_user ON other_user.id = cp2.user_id
                LEFT JOIN listings l ON l.id = c.listing_id
                LEFT JOIN listing_images li ON li.listing_id = l.id AND li.is_primary = 1
                WHERE c.id = :conv_id
                LIMIT 1";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':conv_id', $conversationId, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch();
        return $row ?: null;
    }

    private function getMessagesForConversation(int $conversationId): array
    {
        $db = Database::getInstance()->getConnection();

        $sql = "SELECT m.*, u.first_name AS sender_first_name, u.last_name AS sender_last_name
                FROM messages m
                INNER JOIN users u ON u.id = m.sender_id
                WHERE m.conversation_id = :conv_id
                ORDER BY m.created_at ASC";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':conv_id', $conversationId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    private function markAsRead(int $conversationId, int $userId): void
    {
        $db = Database::getInstance()->getConnection();

        $sql = "UPDATE messages SET is_read = 1, read_at = NOW()
                WHERE conversation_id = :conv_id AND sender_id != :user_id AND is_read = 0";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':conv_id', $conversationId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
    }

    private function isParticipant(int $userId, int $conversationId): bool
    {
        $db = Database::getInstance()->getConnection();

        $sql = "SELECT 1 FROM conversation_participants
                WHERE conversation_id = :conv_id AND user_id = :user_id LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':conv_id', $conversationId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch() !== false;
    }

    private function getOtherParticipant(int $conversationId, int $userId): ?int
    {
        $db = Database::getInstance()->getConnection();

        $sql = "SELECT user_id FROM conversation_participants
                WHERE conversation_id = :conv_id AND user_id != :user_id LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':conv_id', $conversationId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch();
        return $row ? (int) $row['user_id'] : null;
    }

    // ── API: Poll for new messages (JSON) ───────────────────

    /**
     * Returns messages newer than a given message ID as JSON.
     * Called by the polling JS every few seconds.
     */
    public function poll(): void
    {
        Auth::requireLogin();

        $userId = (int) $_SESSION['user_id'];
        $conversationId = (int) ($_GET['id'] ?? 0);
        $afterId = (int) ($_GET['after'] ?? 0);

        if ($conversationId <= 0 || !$this->isParticipant($userId, $conversationId)) {
            $this->json(['messages' => []], 403);
        }

        $db = Database::getInstance()->getConnection();

        $sql = "SELECT m.id, m.sender_id, m.message_body, m.created_at,
                       u.first_name AS sender_first_name
                FROM messages m
                INNER JOIN users u ON u.id = m.sender_id
                WHERE m.conversation_id = :conv_id AND m.id > :after_id
                ORDER BY m.created_at ASC";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':conv_id', $conversationId, PDO::PARAM_INT);
        $stmt->bindValue(':after_id', $afterId, PDO::PARAM_INT);
        $stmt->execute();

        $messages = [];
        foreach ($stmt->fetchAll() as $row) {
            $messages[] = [
                'id' => (int) $row['id'],
                'sender_id' => (int) $row['sender_id'],
                'sender_name' => $row['sender_first_name'],
                'body' => $row['message_body'],
                'time' => date('M j, g:ia', strtotime($row['created_at'])),
                'is_mine' => (int) $row['sender_id'] === $userId,
            ];
        }

        // Mark new messages from the other person as read
        if (!empty($messages)) {
            $this->markAsRead($conversationId, $userId);
        }

        $this->json(['messages' => $messages]);
    }

    /**
     * Send a message via AJAX (JSON response instead of redirect).
     */
    public function sendAjax(): void
    {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'POST required'], 405);
        }

        // Read JSON body
        $input = json_decode(file_get_contents('php://input'), true);
        $userId = (int) $_SESSION['user_id'];
        $conversationId = (int) ($input['conversation_id'] ?? 0);
        $body = trim($input['message'] ?? '');

        if ($conversationId <= 0 || $body === '') {
            $this->json(['error' => 'Invalid message'], 400);
        }

        if (!$this->isParticipant($userId, $conversationId)) {
            $this->json(['error' => 'Not authorized'], 403);
        }

        $db = Database::getInstance()->getConnection();

        $sql = "INSERT INTO messages (conversation_id, sender_id, message_body, created_at)
                VALUES (:conv_id, :sender_id, :body, NOW())";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':conv_id', $conversationId, PDO::PARAM_INT);
        $stmt->bindValue(':sender_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':body', $body, PDO::PARAM_STR);
        $stmt->execute();

        $messageId = (int) $db->lastInsertId();

        // Notification for the other person
        $otherUserId = $this->getOtherParticipant($conversationId, $userId);
        if ($otherUserId !== null) {
            $notifSql = "INSERT INTO notifications (user_id, type, title, body, reference_id, created_at)
                         VALUES (:user_id, 'message', 'New message', :body, :ref_id, NOW())";
            $notifStmt = $db->prepare($notifSql);
            $notifStmt->bindValue(':user_id', $otherUserId, PDO::PARAM_INT);
            $notifStmt->bindValue(':body', mb_substr($body, 0, 200), PDO::PARAM_STR);
            $notifStmt->bindValue(':ref_id', $conversationId, PDO::PARAM_INT);
            $notifStmt->execute();

            (new User())->updateResponseTime($userId);
        }

        $this->json([
            'success' => true,
            'message' => [
                'id' => $messageId,
                'sender_id' => $userId,
                'sender_name' => $_SESSION['user_first_name'] ?? '',
                'body' => $body,
                'time' => date('M j, g:ia'),
                'is_mine' => true,
            ],
        ]);
    }
}
