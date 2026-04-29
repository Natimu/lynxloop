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

// Find the highest message ID for polling
$lastMsgId = 0;
foreach ($messages as $msg) {
    $id = (int) ($msg['id'] ?? 0);
    if ($id > $lastMsgId) {
        $lastMsgId = $id;
    }
}
?>

<section class="form-shell conversation-shell">
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
            <span class="live-indicator" id="liveIndicator" title="Live — checking for new messages">●</span>
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
            <div class="message-bubble <?= $isMine ? 'msg-mine' : 'msg-theirs' ?>" data-msg-id="<?= (int) $msg['id'] ?>">
                <?php if (!$isMine): ?>
                    <span class="msg-sender"><?= $senderName ?></span>
                <?php endif; ?>
                <p><?= nl2br($body) ?></p>
                <span class="msg-time"><?= $time ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Reply form (AJAX) -->
    <div class="reply-form">
        <div class="reply-input-row">
            <input type="text" id="replyInput" class="input-control reply-input" placeholder="Type a message…" autofocus>
            <button type="button" id="replySendBtn" class="reply-send-btn">Send</button>
        </div>
    </div>
</section>

<script>
(() => {
    const convId = <?= $convId ?>;
    const thread = document.getElementById('messageThread');
    const input = document.getElementById('replyInput');
    const sendBtn = document.getElementById('replySendBtn');
    const indicator = document.getElementById('liveIndicator');
    let lastMsgId = <?= $lastMsgId ?>;
    let sending = false;

    // Scroll to bottom
    function scrollToBottom() {
        thread.scrollTop = thread.scrollHeight;
    }
    scrollToBottom();

    // Create a message bubble element
    function createBubble(msg) {
        const div = document.createElement('div');
        div.className = 'message-bubble ' + (msg.is_mine ? 'msg-mine' : 'msg-theirs');
        div.dataset.msgId = msg.id;

        let html = '';
        if (!msg.is_mine) {
            html += '<span class="msg-sender">' + escapeHtml(msg.sender_name) + '</span>';
        }
        html += '<p>' + escapeHtml(msg.body).replace(/\n/g, '<br>') + '</p>';
        html += '<span class="msg-time">' + escapeHtml(msg.time) + '</span>';

        div.innerHTML = html;
        return div;
    }

    function escapeHtml(text) {
        const d = document.createElement('div');
        d.textContent = text;
        return d.innerHTML;
    }

    // ── Poll for new messages ───────────────────────────
    async function poll() {
        try {
            const res = await fetch('/messages/poll?id=' + convId + '&after=' + lastMsgId);
            const data = await res.json();

            if (data.messages && data.messages.length > 0) {
                const wasAtBottom = (thread.scrollHeight - thread.scrollTop - thread.clientHeight) < 60;

                data.messages.forEach(msg => {
                    // Don't add duplicates
                    if (!thread.querySelector('[data-msg-id="' + msg.id + '"]')) {
                        thread.appendChild(createBubble(msg));
                        lastMsgId = msg.id;
                    }
                });

                if (wasAtBottom) {
                    scrollToBottom();
                }

                // Flash indicator green briefly
                indicator.classList.add('live-flash');
                setTimeout(() => indicator.classList.remove('live-flash'), 600);
            }
        } catch (e) {
            console.error('Poll error:', e);
        }
    }

    // Poll every 3 seconds
    setInterval(poll, 3000);

    // ── Send message via AJAX ───────────────────────────
    async function sendMessage() {
        const body = input.value.trim();
        if (body === '' || sending) return;

        sending = true;
        sendBtn.disabled = true;
        sendBtn.textContent = '…';

        try {
            const res = await fetch('/messages/send', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    conversation_id: convId,
                    message: body,
                }),
            });

            const data = await res.json();

            if (data.success && data.message) {
                // Add the bubble immediately
                if (!thread.querySelector('[data-msg-id="' + data.message.id + '"]')) {
                    thread.appendChild(createBubble(data.message));
                    lastMsgId = data.message.id;
                }
                scrollToBottom();
                input.value = '';
            }
        } catch (e) {
            console.error('Send error:', e);
        } finally {
            sending = false;
            sendBtn.disabled = false;
            sendBtn.textContent = 'Send';
            input.focus();
        }
    }

    // Send on button click
    sendBtn.addEventListener('click', sendMessage);

    // Send on Enter key
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });
})();
</script>
