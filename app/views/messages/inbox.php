<?php declare(strict_types=1); ?>
<?php
$conversations = $conversations ?? [];
?>

<section class="form-shell">
    <header>
        <p class="hero-eyebrow">Messages</p>
        <h1>Your conversations</h1>
        <p>Messages about your listings and items you're interested in.</p>
    </header>

    <?php if (!empty($conversations)): ?>
        <div class="conversations-list">
            <?php foreach ($conversations as $conv):
                $convId = (int) $conv['conversation_id'];
                $otherName = htmlspecialchars($conv['other_first_name'] . ' ' . $conv['other_last_name'], ENT_QUOTES, 'UTF-8');
                $listingTitle = htmlspecialchars($conv['listing_title'] ?? 'Listing', ENT_QUOTES, 'UTF-8');
                $listingImage = htmlspecialchars($conv['listing_image'] ?? '', ENT_QUOTES, 'UTF-8');
                $lastMessage = htmlspecialchars(mb_substr($conv['last_message'] ?? '', 0, 80), ENT_QUOTES, 'UTF-8');
                $lastAt = date('M j, g:ia', strtotime($conv['last_message_at']));
                $unread = (int) $conv['unread_count'];
                $initial = strtoupper(mb_substr($conv['other_first_name'] ?? 'U', 0, 1));
            ?>
                <a href="/messages/show?id=<?= $convId ?>" class="conversation-row <?= $unread > 0 ? 'has-unread' : '' ?>">
                    <?php if ($listingImage !== ''): ?>
                        <img src="<?= $listingImage ?>" alt="<?= $listingTitle ?>" class="conv-listing-thumb" loading="lazy">
                    <?php else: ?>
                        <div class="seller-avatar-placeholder conv-avatar"><?= $initial ?></div>
                    <?php endif; ?>

                    <div class="conv-content">
                        <div class="conv-header-row">
                            <span class="conv-other-name"><?= $otherName ?></span>
                            <span class="conv-time"><?= $lastAt ?></span>
                        </div>
                        <span class="conv-listing-title"><?= $listingTitle ?></span>
                        <p class="conv-last-message"><?= $lastMessage ?><?= mb_strlen($conv['last_message'] ?? '') > 80 ? '…' : '' ?></p>
                    </div>

                    <?php if ($unread > 0): ?>
                        <span class="unread-badge"><?= $unread ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            No conversations yet. Send a message from any listing to start one.
        </div>
    <?php endif; ?>
</section>
