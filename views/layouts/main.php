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

        <details class="settings-menu">
            <summary class="settings-menu-toggle" aria-label="Open settings" title="Settings">
                <i class="fa-solid fa-gear" aria-hidden="true"></i>
            </summary>
            <nav class="settings-menu-popover" aria-label="Settings">
                <a href="/change-password">Change password</a>
            </nav>
        </details>

        <script>
            (() => {
                const settingsMenu = document.querySelector('.settings-menu');

                document.addEventListener('click', (event) => {
                    if (settingsMenu.open && !settingsMenu.contains(event.target)) {
                        settingsMenu.open = false;
                    }
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        settingsMenu.open = false;
                    }
                });
            })();
        </script>

       <?php require __DIR__ . '/partials/scripts.php'; ?>

    </body>
</html>