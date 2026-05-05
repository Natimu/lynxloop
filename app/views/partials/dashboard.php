<?php declare(strict_types=1); ?>
<?php
$firstName = $firstName ?? 'there';
$sections = $sections ?? [];
$savedSearches = $savedSearches ?? [];
$unreadCount = $unreadCount ?? 0;
$totalListings = $totalListings ?? 0;
$firstNameSafe = htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8');
?>

<section class="dashboard-shell">
    <div class="dashboard-hero">
        <div class="hero-content">
            <p class="hero-eyebrow">Marketplace pulse</p>
            <h1 class="hero-title">Hey <?= $firstNameSafe ?>, keep trading momentum alive.</h1>
        </div>
        <div class="hero-stats">
            <div class="hero-stat">
                <strong><?= $unreadCount ?></strong>
                <span>Unread messages</span>
            </div>
        </div>
    </div>

    <!-- Dashboard Tabs -->
    <div class="dashboard-tabs" role="tablist" aria-label="Listing sections">
        <?php foreach ($sections as $index => $section):
            $slug = htmlspecialchars($section['slug'] ?? 'section-' . $index, ENT_QUOTES, 'UTF-8');
            $label = htmlspecialchars($section['label'] ?? 'Section', ENT_QUOTES, 'UTF-8');
            $isActive = $index === 0 ? 'is-active' : '';
        ?>
            <a href="#<?= $slug ?>"
               class="dashboard-tab <?= $isActive ?>"
               role="tab"
               aria-controls="<?= $slug ?>"
               data-tab-target="<?= $slug ?>"><?= $label ?></a>
        <?php endforeach; ?>
        <a href="#saved-searches"
           class="dashboard-tab"
           role="tab"
           aria-controls="saved-searches"
           data-tab-target="saved-searches">Search Alerts</a>
        <a href="/messages"
           class="dashboard-tab"
           role="tab">Messages <?php if ($unreadCount > 0): ?><span class="unread-badge"><?= $unreadCount ?></span><?php endif; ?></a>
    </div>

    <!-- Listing Sections -->
    <?php foreach ($sections as $index => $section):
        $slug = htmlspecialchars($section['slug'] ?? 'section-' . $index, ENT_QUOTES, 'UTF-8');
        $label = htmlspecialchars($section['label'] ?? 'Section', ENT_QUOTES, 'UTF-8');
        $subheading = htmlspecialchars($section['subheading'] ?? '', ENT_QUOTES, 'UTF-8');
        $listings = $section['listings'] ?? [];
        $isFirst = $index === 0;
    ?>
        <section class="listing-section tab-panel <?= $isFirst ? '' : 'tab-hidden' ?>"
                 id="<?= $slug ?>"
                 aria-label="<?= $label ?>">
            <div class="section-header">
                <h2><?= $label ?></h2>
                <?php if (!empty($subheading)): ?>
                    <p><?= $subheading ?></p>
                <?php endif; ?>
            </div>

            <?php if (($section['slug'] ?? '') === 'my-listings' && !empty($listings)): ?>
                <div class="manage-listings-stack">
                    <?php foreach ($listings as $listing): ?>
                        <?php
                        $listingId = (int) ($listing['id'] ?? 0);
                        $titleValue = htmlspecialchars((string) ($listing['title'] ?? ''), ENT_QUOTES, 'UTF-8');
                        $priceValue = $listing['price'] !== null ? htmlspecialchars((string) $listing['price'], ENT_QUOTES, 'UTF-8') : '';
                        $priceLabel = $listing['price'] !== null
                            ? '$' . number_format((float) $listing['price'], 2)
                            : 'Trade only';
                        $descriptionValue = htmlspecialchars((string) ($listing['description'] ?? ''), ENT_QUOTES, 'UTF-8');
                        $locationValue = htmlspecialchars((string) ($listing['location'] ?? ''), ENT_QUOTES, 'UTF-8');
                        $statusValue = htmlspecialchars(ucfirst((string) ($listing['status'] ?? 'draft')), ENT_QUOTES, 'UTF-8');
                        $imagePath = htmlspecialchars(
                            $listing['primary_image'] ?? 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=900&q=80',
                            ENT_QUOTES,
                            'UTF-8'
                        );
                        $updatedAt = !empty($listing['updated_at']) ? date('M j, Y', strtotime((string) $listing['updated_at'])) : null;
                        ?>
                        <article class="manage-listing-card" data-manage-card>
                            <button type="button" class="manage-listing-toggle" data-manage-toggle aria-expanded="false" aria-controls="manage-panel-<?= $listingId ?>">
                                <div class="manage-listing-media">
                                    <img src="<?= $imagePath ?>" alt="<?= $titleValue ?> image" loading="lazy" width="120" height="120">
                                </div>
                                <div class="manage-listing-summary">
                                    <div class="manage-listing-summary-main">
                                        <h3><?= $titleValue ?></h3>
                                        <p>Status: <strong><?= $statusValue ?></strong><?php if ($updatedAt): ?> · Updated <?= htmlspecialchars($updatedAt, ENT_QUOTES, 'UTF-8') ?><?php endif; ?></p>
                                    </div>
                                    <div class="manage-listing-summary-side">
                                        <span class="manage-price-pill"><?= htmlspecialchars($priceLabel, ENT_QUOTES, 'UTF-8') ?></span>
                                        <span class="manage-expand-indicator" aria-hidden="true">+</span>
                                    </div>
                                </div>
                            </button>

                            <div class="manage-listing-content manage-listing-content-collapsed" id="manage-panel-<?= $listingId ?>">
                                <div class="manage-listing-head">
                                    <div>
                                        <h3>Edit listing</h3>
                                        <p>Update the name, price, description, or meetup location for this post.</p>
                                    </div>
                                    <a href="/listings/show?id=<?= $listingId ?>" class="ghost manage-view-link">View</a>
                                </div>

                                <form action="/listings/update" method="POST" class="manage-listing-form">
                                    <input type="hidden" name="listing_id" value="<?= $listingId ?>">
                                    <label class="manage-field">
                                        <span>Name</span>
                                        <input type="text" name="title" value="<?= $titleValue ?>" maxlength="200" required>
                                    </label>
                                    <label class="manage-field">
                                        <span>Price</span>
                                        <input type="text" name="price" value="<?= $priceValue ?>" inputmode="decimal" placeholder="Leave blank for trade only">
                                    </label>
                                    <label class="manage-field manage-field-wide">
                                        <span>Description</span>
                                        <textarea name="description" rows="4" maxlength="5000" required><?= $descriptionValue ?></textarea>
                                    </label>
                                    <label class="manage-field manage-field-wide">
                                        <span>Meet location</span>
                                        <input type="text" name="location" value="<?= $locationValue ?>" maxlength="150" placeholder="Library entrance, student center, etc.">
                                    </label>
                                    <div class="manage-actions">
                                        <button type="submit" class="solid">Save changes</button>
                                    </div>
                                </form>

                                <form action="/listings/delete" method="POST" class="manage-delete-form" onsubmit="return confirm('Delete this listing permanently?');">
                                    <input type="hidden" name="listing_id" value="<?= $listingId ?>">
                                    <button type="submit" class="manage-delete-button">Delete listing</button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php elseif (!empty($listings)): ?>
                <div class="card-grid">
                    <?php foreach ($listings as $listing): ?>
                        <?php require __DIR__ . '/components/listing-card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    No listings just yet. Bookmark this space for future trades.
                </div>
            <?php endif; ?>
        </section>
    <?php endforeach; ?>

    <!-- Saved Searches Panel -->
    <section class="listing-section tab-panel tab-hidden" id="saved-searches" aria-label="Search Alerts">
        <div class="section-header">
            <h2>Search Alerts</h2>
            <p>You will be notified when new listings match these searches.</p>
        </div>

        <?php if (!empty($savedSearches)): ?>
            <div class="saved-searches-list">
                <?php foreach ($savedSearches as $search): ?>
                    <div class="saved-search-item">
                        <div class="saved-search-info">
                            <a href="/listings/search?q=<?= urlencode($search['query']) ?><?= !empty($search['category_id']) ? '&category_id=' . (int) $search['category_id'] : '' ?>"
                               class="saved-search-query">
                                "<?= htmlspecialchars($search['query'], ENT_QUOTES, 'UTF-8') ?>"
                            </a>
                            <?php if (!empty($search['category_name'])): ?>
                                <span class="saved-search-category">in <?= htmlspecialchars($search['category_name'], ENT_QUOTES, 'UTF-8') ?></span>
                            <?php endif; ?>
                        </div>
                        <form action="/listings/delete-saved-search" method="POST" class="inline-form">
                            <input type="hidden" name="search_id" value="<?= (int) $search['id'] ?>">
                            <button type="submit" class="saved-search-delete" title="Remove alert">×</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                No saved searches yet. Use the <a href="/listings/search">search page</a> to save an alert.
            </div>
        <?php endif; ?>
    </section>
</section>

<!-- Tab switching JS -->
<script>
(() => {
    const tabs = document.querySelectorAll('.dashboard-tab[data-tab-target]');
    const panels = document.querySelectorAll('.tab-panel');

    tabs.forEach(tab => {
        tab.addEventListener('click', (e) => {
            e.preventDefault();
            const target = tab.dataset.tabTarget;

            // Update active tab
            tabs.forEach(t => t.classList.remove('is-active'));
            tab.classList.add('is-active');

            // Show/hide panels
            panels.forEach(panel => {
                if (panel.id === target) {
                    panel.classList.remove('tab-hidden');
                } else {
                    panel.classList.add('tab-hidden');
                }
            });

            // Update URL hash
            history.replaceState(null, '', '#' + target);
        });
    });

    // Activate tab from URL hash on load
    const hash = window.location.hash.replace('#', '');
    if (hash) {
        const matchingTab = document.querySelector(`.dashboard-tab[data-tab-target="${hash}"]`);
        if (matchingTab) {
            matchingTab.click();
        }
    }
})();

(() => {
    const manageCards = document.querySelectorAll('[data-manage-card]');

    manageCards.forEach((card) => {
        const toggle = card.querySelector('[data-manage-toggle]');
        const panel = card.querySelector('.manage-listing-content');

        if (!toggle || !panel) {
            return;
        }

        toggle.addEventListener('click', () => {
            const willOpen = toggle.getAttribute('aria-expanded') !== 'true';

            toggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            panel.classList.toggle('manage-listing-content-collapsed', !willOpen);
            card.classList.toggle('manage-listing-card-open', willOpen);
        });
    });
})();
</script>
