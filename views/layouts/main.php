<!DOCTYPE html>
<html lang="en">
    <?php require __DIR__ . '/partials/head.php'; ?>
    
    <body>
        <header class="app-header">
            <div class="container app-header-content">
                <div>
                    <h3>Leave Management</h3>
                    <small>Welcome, <?= htmlspecialchars($_SESSION['full_name'] ?? '') ?></small>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <?php if (($_SESSION['is_manager'] ?? false) === true && empty($hideAddEmployeeButton)): ?>
                        <a class="btn calendar-action-button" href="/employees/add">Add employee</a>
                    <?php endif; ?>
                    <form method="POST" action="/logout">
                        <?= csrf_field() ?>
                        <button class="btn btn-outline-dark">
                            Logout
                        </button>
                    </form>
                </div>
            </div>

        </header>

        <main class="container">
            <?php if(!empty($content)): ?>
                <?= $content ?>
            <?php endif; ?>
        </main>

       <?php require __DIR__ . '/partials/scripts.php'; ?>

    </body>
</html>