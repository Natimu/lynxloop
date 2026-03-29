<?php declare(strict_types=1); ?>
<?php
$errors = $errors ?? [];
$old = $old ?? [];
$categoryOptions = $categoryOptions ?? [];
$conditionOptions = $conditionOptions ?? [];

$value = static function (string $key, mixed $default = '') use ($old): string {
    $raw = $old[$key] ?? $default;
    return htmlspecialchars((string) $raw, ENT_QUOTES, 'UTF-8');
};

$checked = static function (string $key) use ($old): string {
    return !empty($old[$key]) ? 'checked' : '';
};
?>

<section class="form-shell">
    <header>
        <p class="hero-eyebrow">Create Listing</p>
        <h1>Share something new with LynxLoop.</h1>
        <p>Complete the essentials below—listings enter a short review queue before appearing in public feeds.</p>
    </header>

    <?php if (!empty($errors['general'])): ?>
        <div class="flash-message flash-error" role="alert">
            <?= htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form action="/listings" method="POST" novalidate>
        <div class="form-grid">
            <div class="form-field">
                <label for="title">Listing title</label>
                <input class="input-control" type="text" id="title" name="title" maxlength="200" value="<?= $value('title') ?>" required>
                <?php if (!empty($errors['title'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['title'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-field">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id" class="input-control" required>
                    <option value="">Select category</option>
                    <?php foreach ($categoryOptions as $option):
                        $selected = (int) ($old['category_id'] ?? 0) === (int) $option['id'] ? 'selected' : '';
                    ?>
                        <option value="<?= htmlspecialchars((string) $option['id'], ENT_QUOTES, 'UTF-8') ?>" <?= $selected ?>>
                            <?= htmlspecialchars($option['label'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($errors['category_id'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['category_id'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-field">
                <label for="item_condition">Condition</label>
                <select id="item_condition" name="item_condition" class="input-control" required>
                    <?php foreach ($conditionOptions as $valueKey => $label):
                        $selected = ($old['item_condition'] ?? '') === $valueKey ? 'selected' : '';
                    ?>
                        <option value="<?= htmlspecialchars((string) $valueKey, ENT_QUOTES, 'UTF-8') ?>" <?= $selected ?>>
                            <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($errors['item_condition'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['item_condition'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-field">
                <label for="price">Price (optional)</label>
                <input class="input-control" type="number" step="0.01" min="0" id="price" name="price" value="<?= $value('price') ?>">
                <span class="form-hint">Leave blank if this is trade-only.</span>
                <?php if (!empty($errors['price'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['price'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-field">
                <label for="quantity">Quantity</label>
                <input class="input-control" type="number" min="1" id="quantity" name="quantity" value="<?= $value('quantity', '1') ?>" required>
                <?php if (!empty($errors['quantity'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['quantity'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-field">
                <label for="brand">Brand (optional)</label>
                <input class="input-control" type="text" id="brand" name="brand" value="<?= $value('brand') ?>">
                <?php if (!empty($errors['brand'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['brand'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-field">
                <label for="location">Meet-up location</label>
                <input class="input-control" type="text" id="location" name="location" value="<?= $value('location') ?>">
                <?php if (!empty($errors['location'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['location'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>
        </div>

        <section>
            <div class="form-field">
                <label for="description">Description</label>
                <textarea id="description" name="description" required><?= $value('description') ?></textarea>
                <?php if (!empty($errors['description'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['description'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>
        </section>

        <section class="form-grid">
            <fieldset class="form-field">
                <legend>Trade options</legend>
                <label class="toggle-row">
                    <input type="checkbox" name="is_trade_allowed" <?= $checked('is_trade_allowed') ?>>
                    Open to trade offers
                </label>
            </fieldset>

            <fieldset class="form-field">
                <legend>Pickup preferences</legend>
                <label class="toggle-row">
                    <input type="checkbox" name="pickup_only" <?= $checked('pickup_only') ?>>
                    Pickup only (default)
                </label>
            </fieldset>
        </section>

        <div class="form-actions">
            <button type="submit">Publish Listing</button>
        </div>
    </form>
</section>
