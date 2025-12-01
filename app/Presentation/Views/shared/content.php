<div class="page-content">
    <h1>Content Loaded</h1>
    <?php if (!empty($data)): ?>
        <pre><?= htmlspecialchars(print_r($data, true), ENT_QUOTES) ?></pre>
    <?php else: ?>
        <p>No content to display.</p>
    <?php endif; ?>
</div>
