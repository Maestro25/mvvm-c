<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Dashboard</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css" />
</head>
<body>


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

<section id="dashboard-content">
    <p>This is your dashboard content area.</p>
    <!-- Additional dashboard components and widgets go here -->
</section>


</body>
</html>
