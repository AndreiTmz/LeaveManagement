<div class="form-card text-center error-card">
    <h1><?= $statusCode ?? '500' ?></h1>
    <h3><?= htmlspecialchars($title ?? 'Unknown Error') ?></h3>
    <p><?= htmlspecialchars($message ?? 'An unknown error occurred.') ?></p>
    <a href="/" class="btn btn-primary mt-3">
        Go to Home
    </a>
</div>