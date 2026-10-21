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

    const calendarElement = document.getElementById('calendar');
    if (!calendarElement) return;
    if (typeof FullCalendar === 'undefined') {
        throw new Error('FullCalendar failed to load for the static app preview.');
    }

    const calendar = new FullCalendar.Calendar(calendarElement, {
        initialView: 'dayGridMonth',
        initialDate: '2026-10-01',
        height: 'auto',
        firstDay: 1,
        headerToolbar: {
            left: 'requestLeave',
            center: 'title',
            right: 'today prev,next'
        },
        customButtons: {
            requestLeave: {
                text: 'Request Leave'
            }
        },
        buttonText: {
            today: 'Today'
        },
        events: [
            {
                title: 'Ethan Brooks',
                start: '2026-10-05',
                end: '2026-10-08',
                backgroundColor: '#33FF57',
                extendedProps: {
                    leave_type: 'Annual Leave',
                    status: 'approved'
                }
            },
            {
                title: 'Grace Foster',
                start: '2026-10-12',
                end: '2026-10-15',
                backgroundColor: '#33FF57',
                classNames: ['pending-leave-request'],
                extendedProps: {
                    leave_type: 'Annual Leave',
                    status: 'pending'
                }
            },
            {
                title: 'Lucas Perry',
                start: '2026-10-19',
                end: '2026-10-21',
                backgroundColor: '#3357FF',
                classNames: ['rejected-leave-request'],
                extendedProps: {
                    leave_type: 'Sick Leave',
                    status: 'rejected'
                }
            }
        ],
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
    });

    calendar.render();
});
