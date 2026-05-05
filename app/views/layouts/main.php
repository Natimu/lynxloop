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
    ['label' => 'Profile', 'href' => '/profile'],
    ['label' => 'Listings', 'href' => '/dashboard'],
    ['label' => 'Manage Listings', 'href' => '/dashboard#my-listings'],
    ['label' => 'Messages', 'href' => '/messages'],
    ['label' => 'About', 'href' => '/about'],
    ['label' => 'Support', 'href' => '/support'],
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
            <form action="/listings/search" method="GET" class="header-search">
                <input type="text" name="q" placeholder="Search listings…" class="header-search-input"
                       value="<?= htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </form>
            <div class="header-actions">
                <a href="/messages" class="message-nav-link" aria-label="Open messages">
                    <span class="message-nav-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" role="presentation" focusable="false">
                            <path d="M4 6.5h16a1.5 1.5 0 0 1 1.5 1.5v8A1.5 1.5 0 0 1 20 17.5H4A1.5 1.5 0 0 1 2.5 16V8A1.5 1.5 0 0 1 4 6.5Zm0 1a.5.5 0 0 0-.5.5v.2l8.2 5.47a.5.5 0 0 0 .56 0l8.24-5.49V8a.5.5 0 0 0-.5-.5H4Zm16.5 1.9-7.68 5.12a1.5 1.5 0 0 1-1.66 0L3.5 9.42V16a.5.5 0 0 0 .5.5h16a.5.5 0 0 0 .5-.5V9.4Z" fill="currentColor"/>
                        </svg>
                    </span>
                </a>
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
                    <form action="/logout" method="POST" class="account-logout-form">
                        <button class="account-logout-button" type="submit">Logout</button>
                    </form>
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

        // Auto-dismiss flash messages after 4 seconds
        document.querySelectorAll('.flash-message').forEach(flash => {
            setTimeout(() => {
                flash.style.transition = 'opacity 400ms ease, transform 400ms ease';
                flash.style.opacity = '0';
                flash.style.transform = 'translateY(-8px)';
                setTimeout(() => flash.remove(), 400);
            }, 4000);
        });
    </script>
    <script src="/js/image-upload.js"></script>
</body>
</html>
