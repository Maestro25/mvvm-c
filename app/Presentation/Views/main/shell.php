<!DOCTYPE html>
<html lang="en">
<head>
    <title><?= htmlspecialchars($data['pageTitle'] ?? 'App') ?></title>
    <?php include __DIR__ . '/../shared/head.php'; ?>
</head>
<body>
    <?php include __DIR__ . '/../shared/header.php'; ?>

    <main>
        <?php if (isset($data['contentView'])): ?>
            <?php include __DIR__ . '/../' . $data['contentView'] . '.php'; ?>
        <?php else: ?>
            <p>No content available.</p>
        <?php endif; ?>
    </main>

    <?php include __DIR__ . '/../shared/footer.php'; ?>
</body>
</html>
