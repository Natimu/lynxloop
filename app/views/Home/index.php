<?php declare(strict_types=1); ?>
<?php
// If logged in, they shouldn't see this page
if (!empty($userId)) {
    header('Location: /dashboard');
    exit;
}

$errors = $errors ?? [];
$old = $old ?? [];
$activeTab = $activeTab ?? 'login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LynxLoop — Campus Exchange</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --onyx: #0e0e0e;
            --jet: #111111;
            --graphite: #1a1a1a;
            --stroke: rgba(255, 255, 255, 0.06);
            --porcelain: #f8f8f3;
            --porcelain-muted: rgba(248, 248, 243, 0.5);
            --gold: #f3c846;
            --gold-deep: #d4a017;
            --gold-soft: rgba(243, 200, 70, 0.08);
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--onyx);
            color: var(--porcelain);
            min-height: 100vh;
            display: flex;
        }

        /* ── LEFT PANEL (30%) — Brand ────────────────── */

        .landing-brand {
            width: 30%;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 3rem 2rem;
            background: var(--jet);
            border-right: 1px solid var(--stroke);
            position: relative;
            overflow: hidden;
        }

        .landing-brand::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 30% 40%, rgba(243, 200, 70, 0.06) 0%, transparent 60%);
            pointer-events: none;
        }

        .brand-logo {
            position: relative;
            z-index: 1;
            text-align: center;
        }

        .brand-logo-icon {
            width: 80px;
            height: 80px;
            border-radius: 24px;
            background: linear-gradient(135deg, var(--gold), var(--gold-deep));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--onyx);
            box-shadow: 0 20px 40px rgba(243, 200, 70, 0.25);
        }

        .brand-logo h1 {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            margin-bottom: 0.35rem;
        }

        .brand-logo h1 span {
            color: var(--gold);
        }

        .brand-tagline {
            color: var(--porcelain-muted);
            font-size: 0.95rem;
            margin-bottom: 2.5rem;
        }

        .brand-features {
            position: relative;
            z-index: 1;
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            width: 100%;
            max-width: 260px;
        }

        .brand-feature {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            font-size: 0.85rem;
            color: var(--porcelain-muted);
            line-height: 1.5;
        }

        .feature-icon {
            width: 36px;
            height: 36px;
            min-width: 36px;
            border-radius: 10px;
            background: var(--gold-soft);
            border: 1px solid rgba(243, 200, 70, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .brand-footer {
            position: absolute;
            bottom: 2rem;
            color: rgba(248, 248, 243, 0.2);
            font-size: 0.75rem;
            z-index: 1;
        }

        /* ── RIGHT PANEL (70%) — Auth + Background ───── */

        .landing-auth {
            width: 70%;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .landing-bg {
            position: absolute;
            inset: 0;
            background-image: url('https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            filter: brightness(0.25) saturate(0.6);
            z-index: 0;
        }

        .landing-bg-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(14, 14, 14, 0.85) 0%, rgba(14, 14, 14, 0.6) 100%);
            z-index: 1;
        }

        /* ── Auth Card ───────────────────────────────── */

        .auth-card {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 440px;
            background: rgba(17, 17, 17, 0.92);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--stroke);
            border-radius: 28px;
            padding: 2.5rem;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.5);
        }

        /* ── Tabs ────────────────────────────────────── */

        .auth-tabs {
            display: flex;
            gap: 0;
            margin-bottom: 2rem;
            background: rgba(255, 255, 255, 0.04);
            border-radius: 999px;
            padding: 4px;
        }

        .auth-tab {
            flex: 1;
            text-align: center;
            padding: 0.65rem 1rem;
            border-radius: 999px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            border: none;
            background: transparent;
            color: var(--porcelain-muted);
            font-family: inherit;
            transition: all 200ms ease;
        }

        .auth-tab.is-active {
            background: linear-gradient(120deg, var(--gold), var(--gold-deep));
            color: var(--onyx);
            box-shadow: 0 8px 20px rgba(243, 200, 70, 0.3);
        }

        .auth-tab:not(.is-active):hover {
            color: var(--porcelain);
        }

        /* ── Forms ───────────────────────────────────── */

        .auth-form {
            display: none;
        }

        .auth-form.is-active {
            display: block;
        }

        .auth-form h2 {
            font-size: 1.4rem;
            margin-bottom: 0.35rem;
        }

        .auth-form .form-subtitle {
            color: var(--porcelain-muted);
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--porcelain-muted);
            margin-bottom: 0.35rem;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 14px;
            border: 1px solid var(--stroke);
            background: rgba(255, 255, 255, 0.04);
            color: var(--porcelain);
            font: inherit;
            font-size: 0.95rem;
            transition: border-color 180ms ease, box-shadow 180ms ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(243, 200, 70, 0.1);
        }

        .form-group select option {
            background: var(--jet);
            color: var(--porcelain);
        }

        .form-row {
            display: flex;
            gap: 0.75rem;
        }

        .form-row .form-group {
            flex: 1;
        }

        .field-error {
            color: #fca5a5;
            font-size: 0.8rem;
            margin-top: 0.3rem;
        }

        .general-error {
            background: rgba(248, 113, 113, 0.1);
            border: 1px solid rgba(248, 113, 113, 0.25);
            color: #fca5a5;
            padding: 0.65rem 1rem;
            border-radius: 14px;
            font-size: 0.85rem;
            margin-bottom: 1rem;
        }

        .auth-submit {
            width: 100%;
            padding: 0.85rem;
            border: none;
            border-radius: 999px;
            background: linear-gradient(120deg, var(--gold), var(--gold-deep));
            color: var(--onyx);
            font: inherit;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            margin-top: 0.5rem;
            box-shadow: 0 15px 30px rgba(243, 200, 70, 0.35);
            transition: transform 160ms ease, box-shadow 160ms ease;
        }

        .auth-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 40px rgba(243, 200, 70, 0.4);
        }

        /* ── Responsive ──────────────────────────────── */

        @media (max-width: 900px) {
            body {
                flex-direction: column;
            }

            .landing-brand {
                width: 100%;
                min-height: auto;
                padding: 2rem 1.5rem;
                border-right: none;
                border-bottom: 1px solid var(--stroke);
            }

            .brand-features {
                flex-direction: row;
                flex-wrap: wrap;
                max-width: 100%;
                justify-content: center;
            }

            .brand-feature {
                flex: 0 0 auto;
                width: 45%;
            }

            .brand-footer {
                display: none;
            }

            .landing-auth {
                width: 100%;
                min-height: auto;
                padding: 2rem 1rem;
            }
        }

        @media (max-width: 500px) {
            .auth-card {
                padding: 1.5rem;
                border-radius: 20px;
            }

            .form-row {
                flex-direction: column;
                gap: 0;
            }

            .brand-features {
                flex-direction: column;
            }

            .brand-feature {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<!-- LEFT: Brand Panel -->
<div class="landing-brand">
    <div class="brand-logo">
        <div class="brand-logo-icon">LL</div>
        <h1>Lynx<span>Loop</span></h1>
        <p class="brand-tagline">Campus Exchange</p>
    </div>

    <ul class="brand-features">
        <li class="brand-feature">
            <span class="feature-icon">📚</span>
            <span>Buy and sell textbooks, electronics, furniture, and more with classmates.</span>
        </li>
        <li class="brand-feature">
            <span class="feature-icon">💬</span>
            <span>Message sellers directly and arrange safe campus meetups.</span>
        </li>
        <li class="brand-feature">
            <span class="feature-icon">🔔</span>
            <span>Set search alerts so you never miss a deal on what you need.</span>
        </li>
        <li class="brand-feature">
            <span class="feature-icon">⚡</span>
            <span>Bump listings, track price drops, and trade with trust.</span>
        </li>
    </ul>

    <span class="brand-footer">© LynxLoop <?= date('Y') ?></span>
</div>

<!-- RIGHT: Auth Panel -->
<div class="landing-auth">
    <div class="landing-bg"></div>
    <div class="landing-bg-overlay"></div>

    <div class="auth-card">
        <!-- Tab toggle -->
        <div class="auth-tabs">
            <button type="button" class="auth-tab <?= $activeTab === 'login' ? 'is-active' : '' ?>" data-auth-tab="login">Sign In</button>
            <button type="button" class="auth-tab <?= $activeTab === 'register' ? 'is-active' : '' ?>" data-auth-tab="register">Create Account</button>
        </div>

        <!-- LOGIN FORM -->
        <div class="auth-form <?= $activeTab === 'login' ? 'is-active' : '' ?>" id="form-login">
            <h2>Welcome back</h2>
            <p class="form-subtitle">Sign in to continue trading on campus.</p>

            <?php if (!empty($errors['general']) && $activeTab === 'login'): ?>
                <div class="general-error"><?= htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form action="/login" method="POST">
                <div class="form-group">
                    <label for="login-email">Email</label>
                    <input type="email" id="login-email" name="email" placeholder="you@campus.edu"
                           value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <?php if (!empty($errors['email']) && $activeTab === 'login'): ?>
                        <p class="field-error"><?= htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="login-password">Password</label>
                    <input type="password" id="login-password" name="password" placeholder="Enter your password">
                    <?php if (!empty($errors['password']) && $activeTab === 'login'): ?>
                        <p class="field-error"><?= htmlspecialchars($errors['password'], ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                </div>

                <button type="submit" class="auth-submit">Sign In</button>
            </form>
        </div>

        <!-- REGISTER FORM -->
        <div class="auth-form <?= $activeTab === 'register' ? 'is-active' : '' ?>" id="form-register">
            <h2>Join LynxLoop</h2>
            <p class="form-subtitle">Create an account and start trading today.</p>

            <?php if (!empty($errors['general']) && $activeTab === 'register'): ?>
                <div class="general-error"><?= htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form action="/register" method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="reg-first">First Name</label>
                        <input type="text" id="reg-first" name="first_name" placeholder="Jordan"
                               value="<?= htmlspecialchars($old['first_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <?php if (!empty($errors['first_name'])): ?>
                            <p class="field-error"><?= htmlspecialchars($errors['first_name'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="reg-last">Last Name</label>
                        <input type="text" id="reg-last" name="last_name" placeholder="Mitchell"
                               value="<?= htmlspecialchars($old['last_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <?php if (!empty($errors['last_name'])): ?>
                            <p class="field-error"><?= htmlspecialchars($errors['last_name'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="reg-email">Email</label>
                    <input type="email" id="reg-email" name="email" placeholder="you@campus.edu"
                           value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <?php if (!empty($errors['email'])): ?>
                        <p class="field-error"><?= htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="reg-role">Role</label>
                    <select id="reg-role" name="university_role">
                        <option value="student" <?= (($old['university_role'] ?? '') === 'student') ? 'selected' : '' ?>>Student</option>
                        <option value="alumni" <?= (($old['university_role'] ?? '') === 'alumni') ? 'selected' : '' ?>>Alumni</option>
                        <option value="faculty" <?= (($old['university_role'] ?? '') === 'faculty') ? 'selected' : '' ?>>Faculty</option>
                    </select>
                    <?php if (!empty($errors['university_role'])): ?>
                        <p class="field-error"><?= htmlspecialchars($errors['university_role'], ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="reg-pass">Password</label>
                        <input type="password" id="reg-pass" name="password" placeholder="Min 6 characters">
                        <?php if (!empty($errors['password'])): ?>
                            <p class="field-error"><?= htmlspecialchars($errors['password'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="reg-confirm">Confirm</label>
                        <input type="password" id="reg-confirm" name="confirm_password" placeholder="Re-enter password">
                        <?php if (!empty($errors['confirm_password'])): ?>
                            <p class="field-error"><?= htmlspecialchars($errors['confirm_password'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <button type="submit" class="auth-submit">Create Account</button>
            </form>
        </div>
    </div>
</div>

<script>
    const tabs = document.querySelectorAll('[data-auth-tab]');
    const forms = document.querySelectorAll('.auth-form');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.authTab;

            tabs.forEach(t => t.classList.remove('is-active'));
            tab.classList.add('is-active');

            forms.forEach(f => {
                f.classList.toggle('is-active', f.id === 'form-' + target);
            });
        });
    });
</script>
</body>
</html>
