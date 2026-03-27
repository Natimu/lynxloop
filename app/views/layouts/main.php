<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Lynxloop 1.1' ?></title>
</head>
<body>
    <?php $isLoggedIn = $isLoggedIn ?? false; ?>
    <header>
        <nav>
            <h2>LynxLoop</h2>
            

            <?php if ($isLoggedIn): ?>
                <a href="/dashboard">Dashboard</a>
                <a href="/dashboard">Text Books</a>
                <a href="/dashboard">Electronics</a>
                <a href="/dashboard">Apparel</a>
                <a href="/dashboard">Saved</a>
                <a href="/dashboard">Contacted</a>
                <a href="/dashboard">Profile</a>
                <form action="/logout" method="POST" style="display:inline;">
                    <button type="submit">Logout</button>
                </form>
            <?php else: ?>
                <a href="/">Home</a>
                <a href="/login">Login</a>
                <a href="/register">Register</a>
                <a href="/about">About</a>
            <?php endif; ?>
        </nav>
    </header>
    <hr>       

    <main>
        <?php require $contentView; ?>
    </main>

    <hr>
    <footer>
        <p>© Lynxloop </p>
    </footer>
</body>
</html>