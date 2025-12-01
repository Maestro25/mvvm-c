<?php
declare(strict_types=1);

/**
 * Data passed from the controller:
 * @var string $error
 * @var array<int, array{type: string, message: string}> $flashMessages
 * @var array<int, array{message: string, mixed}> $notifications
 */
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <title>Error</title>
    <link rel="stylesheet" href="/assets/css/error.css"/>
</head>
<body>
<header>
    <h1>An error occurred</h1>
</header>

<section id="error-message">
    <p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
</section>

<?php if (!empty($flashMessages)): ?>
    <section id="flash-messages">
        <?php foreach ($flashMessages as $message): ?>
            <div class="flash-message flash-<?= htmlspecialchars($message['type'], ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($message['message'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<?php if (!empty($notifications)): ?>
    <section id="notifications">
        <ul>
            <?php foreach ($notifications as $notification): ?>
                <li><?= htmlspecialchars($notification['message'], ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
        </ul>
    </section>
<?php endif; ?>

<footer>
    <p>© <?= date('Y') ?> Your Company</p>
</footer>
</body>
</html>
