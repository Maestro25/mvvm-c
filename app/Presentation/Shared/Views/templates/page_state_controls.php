<?php
/**
 * @var string $state The current page state string
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Page State Tester</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 2rem; }
        .state { font-size: 1.5rem; font-weight: bold; margin-bottom: 1rem; }
        button { margin-right: 10px; padding: 0.5rem 1rem; font-size: 1rem; }
    </style>
</head>
<body>
    <h1>Test Page State</h1>
    <p>Current State: <span class="state"><?= htmlspecialchars($state) ?></span></p>

    <form method="get" action="">
        <button type="submit" name="state" value="initial">Initial</button>
        <button type="submit" name="state" value="loading">Loading</button>
        <button type="submit" name="state" value="loaded">Loaded</button>
        <button type="submit" name="state" value="error">Error</button>
    </form>

    <hr />

    <?php if ($state === 'loading'): ?>
        <p>⏳ Loading... please wait.</p>
    <?php elseif ($state === 'loaded'): ?>
        <p>✅ Content successfully loaded.</p>
    <?php elseif ($state === 'error'): ?>
        <p style="color:red;">⚠️ An error has occurred.</p>
    <?php else: ?>
        <p>😀 Welcome to the test page. Use the buttons above to change states.</p>
    <?php endif; ?>
</body>
</html>
