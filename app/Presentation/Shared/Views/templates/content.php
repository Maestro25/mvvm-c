<?php
/**
 * @var string $state
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Test Page State</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 2rem; }
        .state { font-size: 1.5rem; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Test Page State UI</h1>
    <p>Current Page State:</p>
    <div class="state"><?= htmlspecialchars($state, ENT_QUOTES, 'UTF-8') ?></div>
    <script type="module" src="/assets/js/main.js"></script>
</body>
</html>
