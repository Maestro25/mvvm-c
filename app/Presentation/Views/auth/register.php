<?php
/**
 * @var array<int, array{type: string, message: string}> $flashMessages
 * @var array<int, array{message: string, mixed}> $notifications
 * @var string $loginUrl
 */
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <title>Register</title>
    <link rel="stylesheet" href="/assets/css/auth.css"/>
</head>
<body>
<div class="register-container">
    <h2>Create Account</h2>

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

    <form method="post" action="/register">
        <label for="username">Username</label>
        <input
            type="text"
            id="username"
            name="username"
            placeholder="Choose a username"
            required
            autofocus
            value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8') : '' ?>"
        />

        <label for="email">Email</label>
        <input
            type="email"
            id="email"
            name="email"
            placeholder="Enter your email address"
            required
            value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8') : '' ?>"
        />

        <label for="password">Password</label>
        <input
            type="password"
            id="password"
            name="password"
            placeholder="Create a password"
            required
        />

        <label for="confirmPassword">Confirm Password</label>
        <input
            type="password"
            id="confirmPassword"
            name="confirmPassword"
            placeholder="Confirm your password"
            required
        />

        <div class="terms-container">
            <input type="checkbox" id="acceptedTerms" name="acceptedTerms" required />
            <label for="acceptedTerms">I accept the <a href="/terms" target="_blank" rel="noopener">Terms and Conditions</a></label>
        </div>

        <button type="submit">Register</button>

        <div class="login-link">
            Already have an account? <a href="<?= htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8') ?>">Login here</a>.
        </div>
    </form>
</div>
</body>
</html>
