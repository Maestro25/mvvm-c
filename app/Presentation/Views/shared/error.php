<div class="error-message">
    <h2>Error Occurred</h2>
    <p><?= htmlspecialchars($error['message'] ?? 'An unknown error occurred.', ENT_QUOTES) ?></p>
    <?php if (!empty($error['code'])): ?>
        <p>Error Code: <?= htmlspecialchars($error['code'], ENT_QUOTES) ?></p>
    <?php endif; ?>
</div>
