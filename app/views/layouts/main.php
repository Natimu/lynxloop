<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Lynxloop 1.1' ?></title>
    <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>
<?php
$isLoggedIn = $isLoggedIn ?? false;
$firstName = $firstName ?? ($currentUser ?? 'User');
$initial = strtoupper(mb_substr(trim((string) $firstName), 0, 1) ?: 'U');
$userRole = $_SESSION['user_role'] ?? 'Member';
$accountMenu = [
    ['label' => 'Profile', 'href' => '/dashboard#profile'],
    ['label' => 'Listings', 'href' => '/dashboard#listings'],
    ['label' => 'Manage Listings', 'href' => '/dashboard#manage'],
    ['label' => 'Messages', 'href' => '/dashboard#messages'],
    ['label' => 'About', 'href' => '/dashboard#about'],
    ['label' => 'Support', 'href' => '/dashboard#support'],
];
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
if (isset($_SESSION['flash_success'])) {
    unset($_SESSION['flash_success']);
}
if (isset($_SESSION['flash_error'])) {
    unset($_SESSION['flash_error']);
}
?>
    <header class="global-header">
        <div class="brandmark">
            <a href="/">LynxLoop</a>
            <span>Campus Exchange</span>
        </div>

        <?php if ($isLoggedIn): ?>
            <div class="header-actions">
                <form action="/logout" method="POST">
                    <button class="logout-button" type="submit">Logout</button>
                </form>
                <button class="account-chip" type="button" data-account-toggle aria-controls="accountPanel" aria-expanded="false">
                    <span><?= $initial ?></span>
                </button>
            </div>
        <?php else: ?>
            <nav class="guest-nav">
                <a href="/">Home</a>
                <a href="/login">Login</a>
                <a href="/register">Register</a>
                <a href="/about">About</a>
            </nav>
        <?php endif; ?>
    </header>

    <?php if ($isLoggedIn): ?>
        <div class="account-panel-wrap" id="accountPanel" data-account-panel aria-hidden="true">
            <div class="account-panel">
                <header class="account-panel-header">
                    <div>
                        <p class="panel-eyebrow">Signed in as</p>
                        <h2><?= htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') ?></h2>
                        <span><?= htmlspecialchars(ucwords((string) $userRole), ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <button type="button" class="panel-close" data-account-close aria-label="Close menu">×</button>
                </header>
                <div class="account-panel-body">
                    <ul>
                        <?php foreach ($accountMenu as $item): ?>
                            <li>
                                <a href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="account-panel-cta">
                        <a href="/listings/create">Publish a listing</a>
                    </div>
                </div>
            </div>
            <div class="panel-overlay" data-account-close></div>
        </div>
    <?php endif; ?>

    <main>
        <?php if ($flashSuccess): ?>
            <div class="flash-message flash-success">
                <?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>
        <?php if ($flashError): ?>
            <div class="flash-message flash-error">
                <?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>
        <?php require $contentView; ?>
    </main>

    <footer>
        <p>© Lynxloop</p>
    </footer>
    <script>
        (() => {
            const openers = document.querySelectorAll('[data-account-toggle]');
            const closers = document.querySelectorAll('[data-account-close]');
            const panel = document.querySelector('[data-account-panel]');
            if (!panel) {
                return;
            }
            const body = document.body;

            const togglePanel = (forceState) => {
                const willOpen = typeof forceState === 'boolean' ? forceState : !body.classList.contains('account-panel-open');
                body.classList.toggle('account-panel-open', willOpen);
                panel.setAttribute('aria-hidden', String(!willOpen));
                openers.forEach((btn) => btn.setAttribute('aria-expanded', String(willOpen)));
            };

            openers.forEach((btn) => btn.addEventListener('click', () => togglePanel()));
            closers.forEach((btn) => btn.addEventListener('click', () => togglePanel(false)));
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && body.classList.contains('account-panel-open')) {
                    togglePanel(false);
                }
            });
        })();
    </script>
</body>
</html>
