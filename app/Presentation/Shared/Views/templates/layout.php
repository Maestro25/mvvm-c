<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title><?= htmlspecialchars($title ?? 'My App') ?></title>
    <link rel="stylesheet" href="/assets/styles.css" />
</head>
<body>
<header>
    <div id="auth-panel">
        <?= $authPanel ?? '' // Partial for auth UI ?>
    </div>
</header>

<main>
    <?= $content ?? '' // Main dynamic content ?>
</main>

<footer>
    <p>&copy; <?= date('Y') ?> My Company</p>
</footer>
</body>
</html>
