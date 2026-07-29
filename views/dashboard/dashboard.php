<div class="dashboard">
    <div class="dashboard-summary">
        <div class="summary-card">
            <h6>Remaining leave</h6>
            <h3><?= $remainingLeave ?? '--' ?></h3>
        </div>

        <div class="summary-card">
            <h6>Pending requests</h6>
            <h3><?= $pendingRequests ?? '--' ?></h3>
        </div>

    </div>
    <div id="calendar"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.21/index.global.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const calendar = new FullCalendar.Calendar(
            document.getElementById('calendar'),
            {
                initialView: 'dayGridMonth',
                events: '/calendar/leaves',
                height: 'auto',
                firstDay: 1,
                headerToolbar: {
                    left: 'requestLeave',
                    center: 'title',
                    right: 'today prev,next',
                },
                customButtons: {
                    requestLeave: {
                        text: 'Request Leave',
                        click: function () {
                            window.location.href = '/leaves/request';
                        }
                    }
                }
            }
        );

        calendar.render();
    });
</script>