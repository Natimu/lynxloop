<?php declare(strict_types=1); ?>
<?php
$conversation = $conversation ?? [];
$messages = $messages ?? [];
$currentUserId = $currentUserId ?? 0;

$otherName = htmlspecialchars(($conversation['other_first_name'] ?? '') . ' ' . ($conversation['other_last_name'] ?? ''), ENT_QUOTES, 'UTF-8');
$otherRole = htmlspecialchars(ucwords($conversation['other_role'] ?? ''), ENT_QUOTES, 'UTF-8');
$listingTitle = htmlspecialchars($conversation['listing_title'] ?? 'Listing', ENT_QUOTES, 'UTF-8');
$listingImage = htmlspecialchars($conversation['listing_image'] ?? '', ENT_QUOTES, 'UTF-8');
$listingPrice = $conversation['listing_price'] ?? null;
$listingId = (int) ($conversation['listing_id'] ?? 0);
$convId = (int) ($conversation['conversation_id'] ?? 0);
?>

<section class="form-shell conversation-shell">
    <!-- Conversation header with listing context -->
    <div class="conv-detail-header">
        <a href="/messages" class="conv-back-link">← Back to messages</a>

        <div class="conv-listing-context">
            <?php if ($listingImage !== ''): ?>
                <a href="/listings/show?id=<?= $listingId ?>">
                    <img src="<?= $listingImage ?>" alt="<?= $listingTitle ?>" class="conv-listing-img" loading="lazy">
                </a>
            <?php endif; ?>
            <div>
                <a href="/listings/show?id=<?= $listingId ?>" class="conv-listing-link"><?= $listingTitle ?></a>
                <?php if ($listingPrice !== null): ?>
                    <span class="conv-listing-price">$<?= number_format((float) $listingPrice, 2) ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="conv-with">
            <div class="seller-avatar-placeholder" style="width:36px;height:36px;min-width:36px;font-size:0.9rem;">
                <?= strtoupper(mb_substr($conversation['other_first_name'] ?? 'U', 0, 1)) ?>
            </div>
            <div>
                <strong><?= $otherName ?></strong>
                <span class="seller-status"><?= $otherRole ?></span>
            </div>
        </div>
    </div>

    <!-- Message thread -->
    <div class="message-thread" id="messageThread">
        <?php foreach ($messages as $msg):
            $isMine = (int) $msg['sender_id'] === $currentUserId;
            $senderName = htmlspecialchars($msg['sender_first_name'] ?? '', ENT_QUOTES, 'UTF-8');
            $body = htmlspecialchars($msg['message_body'] ?? '', ENT_QUOTES, 'UTF-8');
            $time = date('M j, g:ia', strtotime($msg['created_at']));
        ?>
            <div class="message-bubble <?= $isMine ? 'msg-mine' : 'msg-theirs' ?>">
                <?php if (!$isMine): ?>
                    <span class="msg-sender"><?= $senderName ?></span>
                <?php endif; ?>
                <p><?= nl2br($body) ?></p>
                <span class="msg-time"><?= $time ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Reply form -->
    <form action="/messages/reply" method="POST" class="reply-form">
        <input type="hidden" name="conversation_id" value="<?= $convId ?>">
        <div class="reply-input-row">
            <input type="text" name="message" class="input-control reply-input" placeholder="Type a message…" required autofocus>
            <button type="submit" class="reply-send-btn">Send</button>
        </div>
    </form>
</section>

<script>
// Auto-scroll to bottom of thread
const thread = document.getElementById('messageThread');
if (thread) {
    thread.scrollTop = thread.scrollHeight;
}
</script>
