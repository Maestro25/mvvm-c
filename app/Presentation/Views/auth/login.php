<?php
/**
 * @var array<int, array{type: string, message: string}> $flashMessages
 * @var array<int, array{message: string, mixed}> $notifications
 * @var bool $rememberMe
 * @var string $resetPasswordUrl
 * @var string $registerUrl
 */
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <title>Login</title>
    <link rel="stylesheet" href="/assets/css/auth.css"/>
</head>
<body>
<div class="login-container">
    <h2>Login</h2>

    <?php if (!empty($flashMessages)): ?>
        <div class="flash-container">
            <?php foreach ($flashMessages as $message): ?>
                <div class="flash-message flash-<?= htmlspecialchars($message['type'], ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($message['message'], ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($notifications)): ?>
        <div class="notifications">
            <ul>
                <?php foreach ($notifications as $notification): ?>
                    <li><?= htmlspecialchars($notification['message'], ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="/login">
        <label for="usernameOrEmail">Username or Email</label>
        <input
            type="text"
            id="usernameOrEmail"
            name="usernameOrEmail"
            placeholder="Enter your username or email"
            required
            autofocus
            value="<?= isset($_POST['usernameOrEmail']) ? htmlspecialchars($_POST['usernameOrEmail'], ENT_QUOTES, 'UTF-8') : '' ?>"
        />

        <label for="password">Password</label>
        <input
            type="password"
            id="password"
            name="password"
            placeholder="Enter your password"
            required
        />

        <div class="remember-me-container">
            <input
                type="checkbox"
                id="rememberMe"
                name="rememberMe"
                <?= !empty($rememberMe) ? 'checked' : '' ?>
            />
            <label for="rememberMe">Remember Me</label>
        </div>

        <button type="submit">Login</button>

        <div class="links-container">
            <a href="<?= htmlspecialchars($resetPasswordUrl, ENT_QUOTES, 'UTF-8') ?>">Forgot your password?</a>
            <br/>
            <span>Don't have an account? <a href="<?= htmlspecialchars($registerUrl, ENT_QUOTES, 'UTF-8') ?>">Register here</a>.</span>
        </div>
    </form>
</div>
</body>
</html>
