<?php declare(strict_types=1); ?>
<?php
$query = htmlspecialchars($query ?? '', ENT_QUOTES, 'UTF-8');
$categoryId = $categoryId ?? null;
$results = $results ?? [];
$categoryOptions = $categoryOptions ?? [];
$isLoggedIn = $isLoggedIn ?? false;
?>

<section class="form-shell">
    <header>
        <p class="hero-eyebrow">Search</p>
        <h1>Find what you need on campus.</h1>
    </header>

    <form action="/listings/search" method="GET" class="search-form">
        <div class="search-bar">
            <input type="text" name="q" class="input-control search-input"
                   placeholder="Search listings…"
                   value="<?= $query ?>"
                   autofocus>
            <select name="category_id" class="input-control search-category">
                <option value="">All categories</option>
                <?php foreach ($categoryOptions as $cat): ?>
                    <option value="<?= (int) $cat['id'] ?>"
                            <?= $categoryId === (int) $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="search-submit">Search</button>
        </div>
    </form>

    <!-- Save Search Alert -->
    <?php if ($query !== '' && $isLoggedIn): ?>
        <form action="/listings/save-search" method="POST" class="save-search-form">
            <input type="hidden" name="query" value="<?= $query ?>">
            <?php if ($categoryId): ?>
                <input type="hidden" name="category_id" value="<?= $categoryId ?>">
            <?php endif; ?>
            <button type="submit" class="save-search-btn">
                🔔 Save this search — get notified when new matches appear
            </button>
        </form>
    <?php endif; ?>
</section>

<!-- Results -->
<?php if ($query !== ''): ?>
    <section class="listing-section" style="margin-top: 1.5rem;">
        <div class="section-header">
            <h2>
                <?= count($results) ?> result<?= count($results) !== 1 ? 's' : '' ?>
                for "<?= $query ?>"
            </h2>
        </div>

        <?php if (!empty($results)): ?>
            <div class="card-grid">
                <?php foreach ($results as $listing): ?>
                    <?php require __DIR__ . '/../partials/components/listing-card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                No listings match your search. Try different keywords or save this search to get notified.
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>
