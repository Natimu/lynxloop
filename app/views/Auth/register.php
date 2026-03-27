<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - LynxLoop</title>
</head>
<body>
    <h1>Sign up</h1>

    <?php if (!empty($errors['general'])): ?>
        <p style="color: red;"><?php echo htmlspecialchars($errors['general']); ?></p>
    <?php endif; ?>

    <form action="register" method="POST">
        <div>
            <label>First Name</label>
            <input type="text" name="first_name" value="<?php echo htmlspecialchars($old['first_name'] ?? ''); ?>">
            <?php if (!empty($errors['first_name'])): ?>
                <p style="color: red;"><?php echo htmlspecialchars($errors['first_name']); ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label>Last Name</label>
            <input type="text" name="last_name" value="<?php echo htmlspecialchars($old['last_name'] ?? ''); ?>">
            <?php if (!empty($errors['last_name'])): ?>
                <p style="color: red;"><?php echo htmlspecialchars($errors['last_name']); ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label>Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($old['email'] ?? ''); ?>">
            <?php if (!empty($errors['email'])): ?>
                <p style="color: red;"><?php echo htmlspecialchars($errors['email']); ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label>Role</label>
            <select name="university_role">
                <option value="student" <?php echo (($old['university_role'] ?? '') === 'student') ? 'selected' : ''; ?>>Student</option>
                <option value="alumni" <?php echo (($old['university_role'] ?? '') === 'alumni') ? 'selected' : ''; ?>>Alumni</option>
                <option value="faculty" <?php echo (($old['university_role'] ?? '') === 'faculty') ? 'selected' : ''; ?>>Faculty</option>
            </select>
            <?php if (!empty($errors['university_role'])): ?>
                <p style="color: red;"><?php echo htmlspecialchars($errors['university_role']); ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label>Password</label>
            <input type="password" name="password">
            <?php if (!empty($errors['password'])): ?>
                <p style="color: red;"><?php echo htmlspecialchars($errors['password']); ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label>Confirm Password</label>
            <input type="password" name="confirm_password">
            <?php if (!empty($errors['confirm_password'])): ?>
                <p style="color: red;"><?php echo htmlspecialchars($errors['confirm_password']); ?></p>
            <?php endif; ?>
        </div>

        <button type="submit">Register</button>
    </form>
    <p><a href="./login">Already have an account? Login</a></p>
</body>
</html>