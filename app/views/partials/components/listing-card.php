<?php declare(strict_types=1); ?>
<?php
$listing = $listing ?? [];
$title = htmlspecialchars($listing['title'] ?? 'Untitled Listing', ENT_QUOTES, 'UTF-8');

// Support both mock data (image key) and DB data (primary_image key)
$image = htmlspecialchars(
    $listing['image'] ?? $listing['primary_image'] ?? 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=900&q=80',
    ENT_QUOTES,
    'UTF-8'
);
$description = htmlspecialchars($listing['description'] ?? 'Description coming soon.', ENT_QUOTES, 'UTF-8');
$listingId = (int) ($listing['id'] ?? 0);
$price = $listing['price'] ?? null;
$tradeAllowed = !empty($listing['is_trade_allowed']);

// Seller info — support both mock nested array and flat DB columns
$seller = $listing['seller'] ?? [];
$sellerName = htmlspecialchars(
    $seller['name'] ?? trim(($listing['seller_first_name'] ?? '') . ' ' . ($listing['seller_last_name'] ?? '')) ?: 'Anonymous Seller',
    ENT_QUOTES,
    'UTF-8'
);
$sellerStatus = htmlspecialchars(
    (string) ($seller['status'] ?? ucwords($listing['seller_role'] ?? '')),
    ENT_QUOTES,
    'UTF-8'
);
$sellerAvatar = htmlspecialchars(
    $seller['avatar'] ?? $listing['seller_avatar'] ?? '',
    ENT_QUOTES,
    'UTF-8'
);

// Response time
$responseMinutes = isset($listing['avg_response_minutes']) ? (int) $listing['avg_response_minutes'] : null;
$responseLabel = \App\Models\User::formatResponseTime($responseMinutes);

// Price drop (set by parent if available)
$priceDrop = $listing['price_drop'] ?? null;

// Bump indicator
$wasBumped = !empty($listing['last_bumped_at']) && (time() - strtotime($listing['last_bumped_at'])) < 86400;

$detailUrl = $listingId > 0 ? '/listings/show?id=' . $listingId : '#';
?>

<article class="listing-card" data-card>
    <?php if ($wasBumped): ?>
        <span class="bumped-badge">↑ Bumped</span>
    <?php endif; ?>

    <div class="seller-chip">
        <?php if ($sellerAvatar !== ''): ?>
            <img class="seller-avatar" src="<?= $sellerAvatar ?>" alt="<?= $sellerName ?> profile" loading="lazy" width="40" height="40">
        <?php else: ?>
            <div class="seller-avatar-placeholder">
                <?= strtoupper(mb_substr($sellerName, 0, 1)) ?>
            </div>
        <?php endif; ?>
        <div class="seller-text">
            <span class="seller-name"><?= $sellerName ?></span>
            <?php if (!empty($sellerStatus)): ?>
                <span class="seller-status"><?= $sellerStatus ?></span>
            <?php endif; ?>
            <?php if ($responseLabel): ?>
                <span class="response-time-badge"><?= htmlspecialchars($responseLabel, ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>
    </div>

    <div class="card-media">
        <a href="<?= $detailUrl ?>">
            <img src="<?= $image ?>" alt="<?= $title ?> image" loading="lazy" width="320" height="240">
        </a>
    </div>

    <div class="card-body">
        <a href="<?= $detailUrl ?>" class="card-title-link">
            <h3><?= $title ?></h3>
        </a>

        <!-- Price row with drop badge -->
        <div class="card-price-row">
            <?php if ($price !== null): ?>
                <span class="card-price">$<?= number_format((float) $price, 2) ?></span>
                <?php if ($priceDrop): ?>
                    <span class="price-drop-badge">
                        Price dropped <s>$<?= number_format((float) $priceDrop['old_price'], 2) ?></s>
                    </span>
                <?php endif; ?>
            <?php else: ?>
                <span class="card-price card-price-trade">Trade only</span>
            <?php endif; ?>
            <?php if ($tradeAllowed && $price !== null): ?>
                <span class="trade-badge-sm">Trades OK</span>
            <?php endif; ?>
        </div>

        <p><?= $description ?></p>
    </div>

    <div class="card-actions">
        <?php if ($listingId > 0 && isset($_SESSION['user_id']) && (int) ($listing['user_id'] ?? 0) !== (int) $_SESSION['user_id']): ?>
            <form action="/listings/quick-message" method="POST" class="inline-form" style="flex:1">
                <input type="hidden" name="listing_id" value="<?= $listingId ?>">
                <input type="hidden" name="message" value="Hey, is this still available?">
                <button type="submit" class="ghost" style="width:100%">Still available?</button>
            </form>
        <?php else: ?>
            <button type="button" class="ghost">Message</button>
        <?php endif; ?>
        <a href="<?= $detailUrl ?>" class="solid card-view-btn">View Listing</a>
    </div>
</article>
