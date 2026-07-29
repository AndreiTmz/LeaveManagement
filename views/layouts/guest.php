<!DOCTYPE html>
<html lang="en">
    <?php require __DIR__ . '/partials/head.php'; ?>

    <body class="d-flex justify-content-center align-items-center min-vh-100">
        <?php if(!empty($content)): ?>
            <?= $content ?>
        <?php endif; ?>

        <?php require __DIR__ . '/partials/scripts.php'; ?>
    </body>
</html>