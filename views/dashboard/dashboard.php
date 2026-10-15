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

<dialog class="leave-event-dialog" id="leave-event-dialog" aria-labelledby="leave-event-title">
    <div class="leave-event-dialog-header">
        <h2 id="leave-event-title">Leave request</h2>
        <button type="button" class="leave-event-close" data-close-leave-dialog aria-label="Close">&times;</button>
    </div>
    <dl class="leave-event-details">
        <dt>Employee</dt><dd id="leave-event-employee"></dd>
        <dt>Department</dt><dd id="leave-event-department"></dd>
        <dt>Leave type</dt><dd id="leave-event-type"></dd>
        <dt>Dates</dt><dd id="leave-event-dates"></dd>
        <dt>Working days</dt><dd id="leave-event-working-days"></dd>
        <dt>Status</dt><dd id="leave-event-status"></dd>
        <dt>Requested</dt><dd id="leave-event-created-at"></dd>
        <dt>Created by</dt><dd id="leave-event-created-by"></dd>
        <dt>Replacement</dt><dd id="leave-event-replacement"></dd>
        <dt>Approved by</dt><dd id="leave-event-approved-by"></dd>
    </dl>
    <p id="leave-event-message" class="leave-event-message" role="status" aria-live="polite"></p>
    <div id="leave-event-actions" class="leave-event-actions" hidden>
        <button type="button" class="leave-decision-button reject" data-decision="reject">Reject</button>
        <button type="button" class="leave-decision-button approve" data-decision="approve">Approve</button>
    </div>
</dialog>
<form id="leave-decision-form" hidden>
    <?= csrf_field() ?>
    <input type="hidden" name="leave_id">
    <input type="hidden" name="decision">
</form>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.21/index.global.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const leaveDialog = document.getElementById('leave-event-dialog');
        const leaveActions = document.getElementById('leave-event-actions');
        const leaveMessage = document.getElementById('leave-event-message');
        const decisionForm = document.getElementById('leave-decision-form');

        const setLeaveDetail = function (name, value) {
            document.getElementById(`leave-event-${name}`).textContent = value || 'Not specified';
        };
        const formatDate = function (value) {
            if (!value) return 'Not specified';
            return new Date(`${value}T00:00:00`).toLocaleDateString(undefined, {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        };
        const countWorkingDays = function (startValue, endValue) {
            const start = new Date(`${startValue}T00:00:00`);
            const end = new Date(`${endValue}T00:00:00`);
            let days = 0;
            for (const date = new Date(start); date <= end; date.setDate(date.getDate() + 1)) {
                if (date.getDay() !== 0 && date.getDay() !== 6) days++;
            }
            return days;
        };

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
                eventClick: function (info) {
                    const event = info.event;
                    const details = event.extendedProps;
                    setLeaveDetail('employee', details.employee);
                    setLeaveDetail('department', details.department);
                    setLeaveDetail('type', details.leave_type);
                    setLeaveDetail('dates', `${formatDate(details.start_date)} – ${formatDate(details.end_date)}`);
                    setLeaveDetail('working-days', String(countWorkingDays(details.start_date, details.end_date)));
                    setLeaveDetail('status', details.status);
                    setLeaveDetail('created-at', new Date(details.created_at.replace(' ', 'T')).toLocaleString());
                    setLeaveDetail('created-by', details.created_by);
                    setLeaveDetail('replacement', details.replacement || 'None');
                    setLeaveDetail('approved-by', details.approved_by || 'Not approved');
                    leaveMessage.textContent = '';
                    decisionForm.elements.leave_id.value = event.id;
                    const isPending = String(details.status).toLowerCase() === 'pending';
                    leaveActions.hidden = !(details.can_decide && isPending);
                    leaveActions.querySelectorAll('button').forEach(button => {
                        button.disabled = false;
                    });
                    leaveDialog.showModal();
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

        leaveDialog.querySelector('[data-close-leave-dialog]').addEventListener('click', function () {
            leaveDialog.close();
        });
        leaveActions.addEventListener('click', async function (event) {
            const button = event.target.closest('[data-decision]');
            if (!button || button.disabled) return;

            button.disabled = true;
            leaveActions.querySelectorAll('button').forEach(action => {
                action.disabled = true;
            });
            decisionForm.elements.decision.value = button.dataset.decision;

            try {
                const response = await fetch('/calendar/leave-decision', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body: new URLSearchParams(new FormData(decisionForm))
                });
                const result = await response.json();
                if (!response.ok) {
                    throw new Error(result.error || 'Unable to update this leave request.');
                }

                setLeaveDetail('status', result.status);
                leaveMessage.textContent = `Leave request ${result.status.toLowerCase()}.`;
                leaveActions.hidden = true;
                calendar.refetchEvents();
            } catch (error) {
                leaveMessage.textContent = error.message;
                leaveActions.querySelectorAll('button').forEach(action => {
                    action.disabled = false;
                });
            }
        });
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