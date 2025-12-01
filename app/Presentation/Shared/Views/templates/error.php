<?php
/**
 * @var string $message
 * @var int $code
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Error <?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        body { font-family: Arial, sans-serif; padding: 2rem; color: #b00020; }
        .error { font-weight: bold; }
    </style>
</head>
<body>
    <h1>Error Occurred</h1>
    <p class="error"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
</body>
</html>
