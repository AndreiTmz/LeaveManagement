<div class="dashboard">
    <div class="dashboard-summary">
        <div class="summary-card">
            <?php $leaveBalances = $remainingLeave ?? []; ?>
            <?php if ($leaveBalances): ?>
                <div class="leave-carousel<?= count($leaveBalances) === 1 ? ' single-slide' : '' ?>" aria-label="Remaining leave by type">
                    <?php if (count($leaveBalances) > 1): ?>
                        <button class="leave-carousel-control" type="button" data-direction="-1" aria-label="Previous leave type" title="Previous" disabled>
                            &larr;
                        </button>
                    <?php endif; ?>
                    <div class="leave-carousel-viewport">
                        <div class="leave-carousel-track" aria-live="polite">
                            <?php foreach ($leaveBalances as $index => $balance): ?>
                                <div class="leave-carousel-slide" role="group" aria-roledescription="slide" aria-label="<?= $index + 1 ?> of <?= count($leaveBalances) ?>">
                                    <h3><?= htmlspecialchars($balance->leaveType, ENT_QUOTES, 'UTF-8') ?></h3>
                                    <p class="leave-balance-number"><?= (int) $balance->remaining_days ?></p>
                                    <p class="leave-balance-caption">days remaining</p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php if (count($leaveBalances) > 1): ?>
                        <button class="leave-carousel-control" type="button" data-direction="1" aria-label="Next leave type" title="Next">
                            &rarr;
                        </button>
                        <span class="leave-carousel-position" aria-hidden="true">1 / <?= count($leaveBalances) ?></span>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p class="leave-balance-empty">No leave balances available.</p>
            <?php endif; ?>
        </div>

        <div class="summary-card">
            <?php $requests = $leaveRequests ?? []; ?>
            <?php if ($requests): ?>
                <div class="leave-carousel request-carousel<?= count($requests) === 1 ? ' single-slide' : '' ?>" aria-label="Leave requests for the current year">
                    <?php if (count($requests) > 1): ?>
                        <button class="leave-carousel-control" type="button" data-direction="-1" aria-label="Previous leave request" title="Previous" disabled>
                            &larr;
                        </button>
                    <?php endif; ?>
                    <div class="leave-carousel-viewport">
                        <div class="leave-carousel-track" aria-live="polite">
                            <?php foreach ($requests as $index => $request): ?>
                                <?php
                                $startDate = date('M j, Y', strtotime($request->start_date));
                                $endDate = date('M j, Y', strtotime($request->end_date));
                                ?>
                                <div class="leave-carousel-slide request-carousel-slide" role="group" aria-roledescription="slide" aria-label="<?= $index + 1 ?> of <?= count($requests) ?>">
                                    <h3>
                                        <?= htmlspecialchars($request->leave_type, ENT_QUOTES, 'UTF-8') ?> -
                                        <?= (int) $request->working_days ?> day<?= (int) $request->working_days === 1 ? '' : 's' ?>
                                    </h3>
                                    <p class="request-date-range">
                                        <?= htmlspecialchars($startDate, ENT_QUOTES, 'UTF-8') ?>
                                        <?php if ($startDate !== $endDate): ?>
                                            &ndash; <?= htmlspecialchars($endDate, ENT_QUOTES, 'UTF-8') ?>
                                        <?php endif; ?>
                                    </p>
                                    <p class="request-submitted">Requested <?= htmlspecialchars(date('M j, Y', strtotime($request->created_at)), ENT_QUOTES, 'UTF-8') ?></p>
                                    <span class="request-status"><?= htmlspecialchars(ucfirst($request->status), ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php if (count($requests) > 1): ?>
                        <button class="leave-carousel-control" type="button" data-direction="1" aria-label="Next leave request" title="Next">
                            &rarr;
                        </button>
                        <span class="leave-carousel-position" aria-hidden="true">1 / <?= count($requests) ?></span>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p class="leave-balance-empty">No leave requests for this year.</p>
            <?php endif; ?>
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
                },
                eventDidMount: function (info) {
                    const capitalizeFirst = function (value) {
                        const text = String(value ?? '').toLowerCase();
                        return text.charAt(0).toUpperCase() + text.slice(1);
                    };
                    const tooltipText = `${capitalizeFirst(info.event.extendedProps.leave_type)} - ${capitalizeFirst(info.event.extendedProps.status)}`;
                    info.el.setAttribute('data-bs-toggle', 'tooltip');
                    info.el.setAttribute('data-bs-placement', 'bottom');
                    info.el.setAttribute('title', tooltipText);
                    bootstrap.Tooltip.getOrCreateInstance(info.el, { placement: 'bottom' });
                },
                eventWillUnmount: function (info) {
                    bootstrap.Tooltip.getInstance(info.el)?.dispose();
                }
            }
        );

        calendar.render();
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.leave-carousel').forEach(function (carousel) {
            const track = carousel.querySelector('.leave-carousel-track');
            const slides = Array.from(track.children);
            const position = carousel.querySelector('.leave-carousel-position');
            const controls = Array.from(carousel.querySelectorAll('.leave-carousel-control'));
            let activeSlide = 0;

            const updateCarousel = function () {
                track.style.transform = `translateX(-${activeSlide * 100}%)`;
                if (position) position.textContent = `${activeSlide + 1} / ${slides.length}`;
                slides.forEach((slide, index) => slide.setAttribute('aria-hidden', String(index !== activeSlide)));
                controls.forEach(function (button) {
                    button.disabled = (Number(button.dataset.direction) < 0 && activeSlide === 0)
                        || (Number(button.dataset.direction) > 0 && activeSlide === slides.length - 1);
                });
            };

            controls.forEach(function (button) {
                button.addEventListener('click', function () {
                    activeSlide = Math.max(0, Math.min(slides.length - 1, activeSlide + Number(button.dataset.direction)));
                    updateCarousel();
                });
            });

            updateCarousel();
        });
    });
</script>