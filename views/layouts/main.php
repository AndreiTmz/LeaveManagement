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

        <?php if (parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) === '/calendar'): ?>
            <button
                class="calendar-legend-toggle"
                type="button"
                aria-label="Calendar legend"
                aria-haspopup="dialog"
                aria-controls="calendar-legend"
                title="Calendar legend"
                data-open-calendar-legend
            >
                <i class="fa-solid fa-info" aria-hidden="true"></i>
            </button>

            <dialog class="calendar-legend-dialog" id="calendar-legend" aria-labelledby="calendar-legend-title">
                <div class="calendar-legend-header">
                    <div>
                        <h2 id="calendar-legend-title">Leave request statuses</h2>
                    </div>
                    <button class="calendar-legend-close" type="button" aria-label="Close calendar legend" data-close-calendar-legend>
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>
                </div>
                <p class="calendar-legend-intro">Each event’s color identifies its department. Its appearance shows the request status.</p>
                <ul class="calendar-legend-list">
                    <li class="calendar-legend-item">
                        <span class="calendar-legend-example approved" aria-hidden="true">Employee name</span>
                        <span class="calendar-legend-description">
                            <strong>Approved</strong>
                            <span>Confirmed leave; shown at full opacity.</span>
                        </span>
                        <i class="fa-solid fa-circle-check calendar-legend-status approved" aria-hidden="true"></i>
                    </li>
                    <li class="calendar-legend-item">
                        <span class="calendar-legend-example pending" aria-hidden="true">Employee name</span>
                        <span class="calendar-legend-description">
                            <strong>Pending</strong>
                            <span>Waiting for a manager's review; shown faded.</span>
                        </span>
                        <i class="fa-solid fa-clock calendar-legend-status pending" aria-hidden="true"></i>
                    </li>
                    <li class="calendar-legend-item">
                        <span class="calendar-legend-example rejected" aria-hidden="true">Employee name</span>
                        <span class="calendar-legend-description">
                            <strong>Rejected</strong>
                            <span>Declined request; the event name is crossed out.</span>
                        </span>
                        <i class="fa-solid fa-circle-xmark calendar-legend-status rejected" aria-hidden="true"></i>
                    </li>
                </ul>
            </dialog>
        <?php endif; ?>

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
                const legend = document.getElementById('calendar-legend');
                const openLegendButton = document.querySelector('[data-open-calendar-legend]');

                document.addEventListener('click', (event) => {
                    if (contextMenu.open && !contextMenu.contains(event.target)) {
                        contextMenu.open = false;
                    }
                });

                if (legend && openLegendButton) {
                    openLegendButton.addEventListener('click', () => legend.showModal());
                    legend.querySelector('[data-close-calendar-legend]').addEventListener('click', () => legend.close());
                    legend.addEventListener('click', (event) => {
                        if (event.target === legend) legend.close();
                    });
                }

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