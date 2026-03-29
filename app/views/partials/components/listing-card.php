<?php declare(strict_types=1); ?>
<?php
$listing = $listing ?? [];
$title = htmlspecialchars($listing['title'] ?? 'Untitled Listing', ENT_QUOTES, 'UTF-8');
$image = htmlspecialchars($listing['image'] ?? 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=900&q=80', ENT_QUOTES, 'UTF-8');
$description = htmlspecialchars($listing['description'] ?? 'Description coming soon.', ENT_QUOTES, 'UTF-8');
$seller = $listing['seller'] ?? [];
$sellerName = htmlspecialchars($seller['name'] ?? 'Anonymous Seller', ENT_QUOTES, 'UTF-8');
$sellerStatus = isset($seller['status']) ? htmlspecialchars((string) $seller['status'], ENT_QUOTES, 'UTF-8') : null;
$sellerAvatar = htmlspecialchars($seller['avatar'] ?? 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=facearea&w=120&h=120&q=80', ENT_QUOTES, 'UTF-8');
?>

<article class="listing-card" data-card>
    <div class="seller-chip">
        <img class="seller-avatar" src="<?= $sellerAvatar ?>" alt="<?= $sellerName ?> profile" loading="lazy" width="40" height="40">
        <div class="seller-text">
            <span class="seller-name"><?= $sellerName ?></span>
            <?php if (!empty($sellerStatus)): ?>
                <span class="seller-status"><?= $sellerStatus ?></span>
            <?php endif; ?>
        </div>
    </div>

    <div class="card-media">
        <img src="<?= $image ?>" alt="<?= $title ?> image" loading="lazy" width="320" height="240">
    </div>

    <div class="card-body">
        <h3><?= $title ?></h3>
        <p><?= $description ?></p>
    </div>

    <div class="card-actions">
        <button type="button" class="ghost">Message</button>
        <button type="button" class="solid">View Listing</button>
    </div>
</article>
