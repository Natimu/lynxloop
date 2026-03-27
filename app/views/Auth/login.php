<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LynxLoop</title>
</head>
<body>
    <h1>Login</h1>

    <?php if (!empty($errors['general'])): ?>
        <p style="color: red;"><?php echo htmlspecialchars($errors['general']); ?></p>
    <?php endif; ?>
    <form action="/login" method="POST">
        <div>
            <label>Email</label><br>
            <input type="email" name="email" value="<?php echo htmlspecialchars($old['email'] ?? ''); ?>">
            <?php if (!empty($errors['email'])): ?>
                <p style="color: red;"><?php echo htmlspecialchars($errors['email']); ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label>Password</label><br>
            <input type="password" name="password">
            <?php if (!empty($errors['password'])): ?>
                <p style="color: red;"><?php echo htmlspecialchars($errors['password']); ?></p>
            <?php endif; ?>
        </div>

        <button type="submit">Login</button>
    </form>
    <p><a href="register">Need an account? Register</a></p>
</body>
</html>