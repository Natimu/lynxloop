<?php declare(strict_types=1); ?>
<?php
$firstName = $firstName ?? 'there';
$sections = $sections ?? [];
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
                <strong><?= str_pad((string) count($sections), 2, '0', STR_PAD_LEFT) ?></strong>
                <span>Active streams</span>
            </div>
            <div class="hero-stat">
                <strong>03:21</strong>
                <span>Avg. response</span>
            </div>
        </div>
    </div>

    <div class="dashboard-tabs" role="tablist" aria-label="Listing sections">
        <?php foreach ($sections as $index => $section):
            $slug = htmlspecialchars($section['slug'] ?? 'section-' . $index, ENT_QUOTES, 'UTF-8');
            $label = htmlspecialchars($section['label'] ?? 'Section', ENT_QUOTES, 'UTF-8');
            $isActive = $index === 0 ? 'is-active' : '';
        ?>
            <a href="#<?= $slug ?>" class="<?= $isActive ?>" role="tab" aria-controls="<?= $slug ?>"><?= $label ?></a>
        <?php endforeach; ?>
    </div>

    <?php foreach ($sections as $index => $section):
        $slug = htmlspecialchars($section['slug'] ?? 'section-' . $index, ENT_QUOTES, 'UTF-8');
        $label = htmlspecialchars($section['label'] ?? 'Section', ENT_QUOTES, 'UTF-8');
        $subheading = htmlspecialchars($section['subheading'] ?? '', ENT_QUOTES, 'UTF-8');
        $listings = $section['listings'] ?? [];
    ?>
        <section class="listing-section" id="<?= $slug ?>" aria-label="<?= $label ?>">
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
</section>
