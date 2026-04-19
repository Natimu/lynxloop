<?php declare(strict_types=1); ?>
<?php
$listing = $listing ?? [];
$similar = $similar ?? [];
$priceDrop = $priceDrop ?? null;
$isFavorited = $isFavorited ?? false;
$isOwner = $isOwner ?? false;
$canBump = $canBump ?? false;
$conditionOptions = $conditionOptions ?? [];

$title = htmlspecialchars($listing['title'] ?? 'Listing', ENT_QUOTES, 'UTF-8');
$description = htmlspecialchars($listing['description'] ?? '', ENT_QUOTES, 'UTF-8');
$price = $listing['price'] ?? null;
$condition = $listing['item_condition'] ?? '';
$conditionLabel = htmlspecialchars($conditionOptions[$condition] ?? ucfirst($condition), ENT_QUOTES, 'UTF-8');
$categoryName = htmlspecialchars($listing['category_name'] ?? '', ENT_QUOTES, 'UTF-8');
$brand = htmlspecialchars($listing['brand'] ?? '', ENT_QUOTES, 'UTF-8');
$location = htmlspecialchars($listing['location'] ?? '', ENT_QUOTES, 'UTF-8');
$quantity = (int) ($listing['quantity'] ?? 1);
$viewCount = (int) ($listing['view_count'] ?? 0);
$tradeAllowed = !empty($listing['is_trade_allowed']);
$pickupOnly = !empty($listing['pickup_only']);
$images = $listing['images'] ?? [];
$listingId = (int) ($listing['id'] ?? 0);

$sellerName = htmlspecialchars(($listing['seller_first_name'] ?? '') . ' ' . ($listing['seller_last_name'] ?? ''), ENT_QUOTES, 'UTF-8');
$sellerRole = htmlspecialchars(ucwords($listing['seller_role'] ?? ''), ENT_QUOTES, 'UTF-8');
$sellerRating = $listing['seller_rating'] ?? null;
$sellerReviews = (int) ($listing['seller_reviews'] ?? 0);
$responseMinutes = isset($listing['avg_response_minutes']) ? (int) $listing['avg_response_minutes'] : null;
$responseLabel = \App\Models\User::formatResponseTime($responseMinutes);
?>

<section class="listing-detail">
    <!-- Image Gallery -->
    <div class="detail-gallery">
        <?php if (!empty($images)): ?>
            <div class="gallery-main">
                <img id="gallery-main-img"
                     src="<?= htmlspecialchars($images[0]['image_path'], ENT_QUOTES, 'UTF-8') ?>"
                     alt="<?= $title ?>"
                     loading="eager">
            </div>
            <?php if (count($images) > 1): ?>
                <div class="gallery-thumbs">
                    <?php foreach ($images as $i => $img): ?>
                        <button type="button"
                                class="gallery-thumb <?= $i === 0 ? 'is-active' : '' ?>"
                                data-src="<?= htmlspecialchars($img['image_path'], ENT_QUOTES, 'UTF-8') ?>"
                                aria-label="View image <?= $i + 1 ?>">
                            <img src="<?= htmlspecialchars($img['image_path'], ENT_QUOTES, 'UTF-8') ?>"
                                 alt="Thumbnail <?= $i + 1 ?>" loading="lazy">
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="gallery-main gallery-empty">
                <span>No images available</span>
            </div>
        <?php endif; ?>
    </div>

    <!-- Listing Info -->
    <div class="detail-info">
        <div class="detail-header">
            <div>
                <h1><?= $title ?></h1>
                <div class="detail-meta-row">
                    <span class="detail-category"><?= $categoryName ?></span>
                    <span class="detail-condition"><?= $conditionLabel ?></span>
                    <span class="detail-views"><?= $viewCount ?> views</span>
                </div>
            </div>

            <!-- Favorite button -->
            <?php if (isset($_SESSION['user_id']) && !$isOwner): ?>
                <button type="button" class="favorite-btn <?= $isFavorited ? 'is-favorited' : '' ?>"
                        data-listing-id="<?= $listingId ?>"
                        aria-label="<?= $isFavorited ? 'Remove from saved' : 'Save listing' ?>">
                    <span class="fav-icon"><?= $isFavorited ? '★' : '☆' ?></span>
                </button>
            <?php endif; ?>
        </div>

        <!-- Price + Price Drop Badge -->
        <div class="detail-price-row">
            <?php if ($price !== null): ?>
                <span class="detail-price">$<?= number_format((float) $price, 2) ?></span>
                <?php if ($priceDrop): ?>
                    <span class="price-drop-badge">
                        Price dropped
                        <s>$<?= number_format((float) $priceDrop['old_price'], 2) ?></s>
                    </span>
                <?php endif; ?>
            <?php else: ?>
                <span class="detail-price detail-price-trade">Trade only</span>
            <?php endif; ?>

            <?php if ($tradeAllowed && $price !== null): ?>
                <span class="trade-badge">Open to trades</span>
            <?php endif; ?>
        </div>

        <!-- Details -->
        <div class="detail-description">
            <p><?= nl2br($description) ?></p>
        </div>

        <div class="detail-specs">
            <?php if ($brand !== ''): ?>
                <div class="spec-item"><strong>Brand</strong> <?= $brand ?></div>
            <?php endif; ?>
            <?php if ($quantity > 1): ?>
                <div class="spec-item"><strong>Quantity</strong> <?= $quantity ?></div>
            <?php endif; ?>
            <?php if ($pickupOnly): ?>
                <div class="spec-item"><strong>Pickup only</strong></div>
            <?php endif; ?>
            <?php if ($location !== ''): ?>
                <div class="spec-item"><strong>Location</strong> <?= $location ?></div>
            <?php endif; ?>
        </div>

        <!-- Seller Card -->
        <div class="detail-seller-card">
            <div class="seller-chip">
                <div class="seller-avatar-placeholder">
                    <?= strtoupper(mb_substr($listing['seller_first_name'] ?? 'U', 0, 1)) ?>
                </div>
                <div class="seller-text">
                    <span class="seller-name"><?= $sellerName ?></span>
                    <span class="seller-status"><?= $sellerRole ?></span>
                    <?php if ($responseLabel): ?>
                        <span class="response-time-badge"><?= htmlspecialchars($responseLabel, ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                    <?php if ($sellerReviews > 0): ?>
                        <span class="seller-rating-badge">★ <?= number_format((float) $sellerRating, 1) ?> (<?= $sellerReviews ?>)</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="detail-actions">
            <?php if (isset($_SESSION['user_id']) && !$isOwner): ?>
                <!-- Still Available? quick message -->
                <form action="/listings/quick-message" method="POST" class="inline-form">
                    <input type="hidden" name="listing_id" value="<?= $listingId ?>">
                    <input type="hidden" name="message" value="Hey, is this still available?">
                    <button type="submit" class="solid">Still available?</button>
                </form>

                <!-- Full message button -->
                <form action="/listings/quick-message" method="POST" class="inline-form msg-form">
                    <input type="hidden" name="listing_id" value="<?= $listingId ?>">
                    <div class="quick-msg-input">
                        <input type="text" name="message" placeholder="Send a message…" class="input-control" required>
                        <button type="submit" class="ghost">Send</button>
                    </div>
                </form>
            <?php endif; ?>

            <?php if ($isOwner && $canBump): ?>
                <form action="/listings/bump" method="POST" class="inline-form">
                    <input type="hidden" name="listing_id" value="<?= $listingId ?>">
                    <button type="submit" class="ghost bump-btn">↑ Bump to top</button>
                </form>
            <?php elseif ($isOwner && !$canBump): ?>
                <button class="ghost bump-btn" disabled title="You can bump again in 24 hours">↑ Bumped recently</button>
            <?php endif; ?>
        </div>

        <!-- Campus Meetup Map -->
        <?php if ($location !== '' || $pickupOnly): ?>
            <div class="meetup-map-section">
                <h3>Suggested meetup spots</h3>
                <p class="form-hint">Meet in a public place on campus for safety.</p>
                <div class="meetup-spots">
                    <div class="meetup-spot">
                        <span class="spot-icon">📚</span>
                        <div>
                            <strong>Library main entrance</strong>
                            <span>Well-lit, high foot traffic</span>
                        </div>
                    </div>
                    <div class="meetup-spot">
                        <span class="spot-icon">☕</span>
                        <div>
                            <strong>Student union café</strong>
                            <span>Indoor seating, security nearby</span>
                        </div>
                    </div>
                    <div class="meetup-spot">
                        <span class="spot-icon">🏢</span>
                        <div>
                            <strong>Admin building lobby</strong>
                            <span>Cameras, reception desk</span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Similar Listings -->
<?php if (!empty($similar)): ?>
    <section class="similar-section">
        <div class="section-header">
            <h2>Similar listings</h2>
            <p>More from <?= $categoryName ?></p>
        </div>
        <div class="card-grid">
            <?php foreach ($similar as $listing): ?>
                <?php require __DIR__ . '/../partials/components/listing-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<script>
// Gallery thumbnail switching
document.querySelectorAll('.gallery-thumb').forEach(btn => {
    btn.addEventListener('click', () => {
        const mainImg = document.getElementById('gallery-main-img');
        if (mainImg) mainImg.src = btn.dataset.src;
        document.querySelectorAll('.gallery-thumb').forEach(b => b.classList.remove('is-active'));
        btn.classList.add('is-active');
    });
});

// Favorite toggle via AJAX
document.querySelectorAll('.favorite-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
        const listingId = btn.dataset.listingId;
        const formData = new FormData();
        formData.append('listing_id', listingId);

        try {
            const response = await fetch('/listings/toggle-favorite', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            const icon = btn.querySelector('.fav-icon');

            if (data.favorited) {
                btn.classList.add('is-favorited');
                icon.textContent = '★';
            } else {
                btn.classList.remove('is-favorited');
                icon.textContent = '☆';
            }
        } catch (e) {
            console.error('Favorite toggle failed', e);
        }
    });
});
</script>
