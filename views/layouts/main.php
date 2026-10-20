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

        <details class="context-menu">
            <summary class="context-menu-toggle" aria-label="Open menu" title="Menu">
                <i class="fa-solid fa-bars" aria-hidden="true"></i>
            </summary>
            <nav class="context-menu-popover" aria-label="Menu">
                <?php if (($_SESSION['is_manager'] ?? false) === true && empty($hideAddEmployeeButton)): ?>
                    <a href="/employees/add">Add employee</a>
                <?php endif; ?>
                <?php if (($title ?? '') !== 'Change password'): ?>
                    <a href="/change-password">Change password</a>
                <?php endif; ?>
            </nav>
        </details>

        <script>
            (() => {
                const contextMenu = document.querySelector('.context-menu');

                document.addEventListener('click', (event) => {
                    if (contextMenu.open && !contextMenu.contains(event.target)) {
                        contextMenu.open = false;
                    }
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        contextMenu.open = false;
                    }
                });
            })();
        </script>

       <?php require __DIR__ . '/partials/scripts.php'; ?>

    </body>
</html>