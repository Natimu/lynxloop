<?php declare(strict_types=1); ?>
<?php
$user = $user ?? [];
$userListings = $userListings ?? [];
$favoriteCount = $favoriteCount ?? 0;
$messageCount = $messageCount ?? 0;

$fullName = htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''), ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8');
$role = htmlspecialchars(ucwords($user['university_role'] ?? ''), ENT_QUOTES, 'UTF-8');
$bio = htmlspecialchars($user['bio'] ?? '', ENT_QUOTES, 'UTF-8');
$joined = isset($user['created_at']) ? date('F Y', strtotime($user['created_at'])) : '';
$rating = $user['average_rating'] ?? null;
$reviews = (int) ($user['total_reviews'] ?? 0);
$responseLabel = \App\Models\User::formatResponseTime(isset($user['avg_response_minutes']) ? (int) $user['avg_response_minutes'] : null);
$initial = strtoupper(mb_substr($user['first_name'] ?? 'U', 0, 1));
?>

<section class="form-shell profile-shell">
    <header>
        <p class="hero-eyebrow">Your Profile</p>
    </header>

    <div class="profile-card">
        <div class="profile-avatar">
            <div class="seller-avatar-placeholder" style="width:80px;height:80px;min-width:80px;font-size:2rem;">
                <?= $initial ?>
            </div>
        </div>
        <div class="profile-info">
            <h1><?= $fullName ?></h1>
            <span class="profile-role"><?= $role ?></span>
            <?php if ($joined): ?>
                <span class="profile-joined">Joined <?= $joined ?></span>
            <?php endif; ?>
            <?php if ($bio !== ''): ?>
                <p class="profile-bio"><?= $bio ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="profile-stats">
        <div class="profile-stat">
            <strong><?= count($userListings) ?></strong>
            <span>Listings</span>
        </div>
        <div class="profile-stat">
            <strong><?= $favoriteCount ?></strong>
            <span>Saved</span>
        </div>
        <div class="profile-stat">
            <strong><?= $messageCount ?></strong>
            <span>Messages</span>
        </div>
        <?php if ($reviews > 0): ?>
            <div class="profile-stat">
                <strong>★ <?= number_format((float) $rating, 1) ?></strong>
                <span><?= $reviews ?> review<?= $reviews !== 1 ? 's' : '' ?></span>
            </div>
        <?php endif; ?>
        <?php if ($responseLabel): ?>
            <div class="profile-stat">
                <strong><?= htmlspecialchars($responseLabel, ENT_QUOTES, 'UTF-8') ?></strong>
                <span>Avg reply</span>
            </div>
        <?php endif; ?>
    </div>

    <div class="profile-details">
        <div class="profile-detail-row">
            <strong>Email</strong>
            <span><?= $email ?></span>
        </div>
        <div class="profile-detail-row">
            <strong>Role</strong>
            <span><?= $role ?></span>
        </div>
    </div>

    <div class="profile-actions">
        <a href="/dashboard" class="ghost profile-action-btn">View Dashboard</a>
        <a href="/listings/create" class="solid profile-action-btn">Publish a Listing</a>
    </div>
</section>

<?php if (!empty($userListings)): ?>
    <section class="listing-section" style="margin-top:1.5rem;">
        <div class="section-header">
            <h2>Your Listings</h2>
        </div>
        <div class="card-grid">
            <?php foreach ($userListings as $listing): ?>
                <?php require __DIR__ . '/../partials/components/listing-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>
