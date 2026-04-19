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
            <p class="hero-grid-meta">
                Discover your needs in your school. Textbooks, Electronics, Apparel, and many more.
            </p>
        </div>
        <div class="hero-stats">
            <div class="hero-stat">
                <strong><?= $totalListings ?></strong>
                <span>Active listings</span>
            </div>
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

            <?php if (!empty($listings)): ?>
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
</script>
