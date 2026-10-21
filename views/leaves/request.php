<div class="form-page">
    <div class="form-card-wrapper">
        <a class="form-back-link" href="/calendar" aria-label="Back to calendar" title="Back to calendar">
            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 12H5"></path>
                <path d="m12 19-7-7 7-7"></path>
            </svg>
        </a>
        <div class="form-card">
        <h2 class="text-center mb-4">
            Request Leave
        </h2>

        <form method="POST" action="/leaves/request">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label" for="start-date">Start Date</label>
                <input class="form-control" id="start-date" type="date" name="start_date" required
                    value="<?= htmlspecialchars($startDate ?? '') ?>">
            </div>

            <div class="mb-3">
                <label class="form-label" for="end-date">End Date</label>
                <input class="form-control" id="end-date" type="date" name="end_date" required
                    value="<?= htmlspecialchars($endDate ?? '') ?>">
            </div>

            <div class="mb-3">
                <label class="form-label" for="leave-type">Leave type</label>
                <select class="form-control" id="leave-type" name="leave_type" required>
                    <option value="">Select leave type</option>
                    <?php foreach ($leaveTypes ?? [] as $leaveType): ?>
                        <option value="<?= htmlspecialchars((string) $leaveType['id']) ?>"
                            data-replacement-required="<?= (int) $leaveType['replacement_required'] ?>"
                            <?= (isset($selectedLeaveType) && $selectedLeaveType == $leaveType['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($leaveType['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3" id="replacement-field" hidden>
                <label class="form-label" for="replacement-id">Replacement employee</label>
                <select class="form-control" id="replacement-id" name="replacement_id">
                    <option value="">Choose an eligible replacement</option>
                    <?php foreach ($replacementEmployees ?? [] as $replacement): ?>
                        <option value="<?= (int) $replacement['id'] ?>"
                            <?= (string) ($selectedReplacement ?? '') === (string) $replacement['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($replacement['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <p class="mb-3" id="replacement-help" aria-live="polite"></p>

            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($errorMessage) ?>
                </div>
            <?php endif; ?>

            <button class="btn btn-primary w-100 submit_btn" type="submit">
                Request leave
            </button>
        </form>

        <script>
            (() => {
                const leaveType = document.getElementById('leave-type');
                const startDate = document.getElementById('start-date');
                const endDate = document.getElementById('end-date');
                const replacementField = document.getElementById('replacement-field');
                const replacementSelect = document.getElementById('replacement-id');
                const replacementHelp = document.getElementById('replacement-help');
                const originalSelection = <?= json_encode(
                    (string) ($selectedReplacement ?? ''),
                    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
                ) ?>;
                let latestRequest = 0;

                function earliestAllowedDate() {
                    const date = new Date();
                    date.setHours(0, 0, 0, 0);
                    let workingDays = 0;

                    while (workingDays < 3) {
                        date.setDate(date.getDate() + 1);
                        if (date.getDay() !== 0 && date.getDay() !== 6) {
                            workingDays++;
                        }
                    }

                    return date;
                }

                function formatDate(date) {
                    return [
                        date.getFullYear(),
                        String(date.getMonth() + 1).padStart(2, '0'),
                        String(date.getDate()).padStart(2, '0')
                    ].join('-');
                }

                function minimumDateFor(input) {
                    const earliest = earliestAllowedDate();
                    if (input !== endDate || !startDate.value || startDate.value < formatDate(earliest)) {
                        return earliest;
                    }

                    return new Date(startDate.value + 'T00:00:00');
                }

                function initializeWeekdayPicker(input) {
                    input.type = 'text';
                    input.readOnly = true;
                    input.setAttribute('aria-haspopup', 'dialog');
                    input.setAttribute('aria-expanded', 'false');
                    input.setAttribute('autocomplete', 'off');

                    const picker = document.createElement('div');
                    picker.className = 'weekday-picker';
                    picker.hidden = true;
                    picker.setAttribute('role', 'dialog');
                    picker.setAttribute('aria-label', 'Choose an available leave date');
                    input.insertAdjacentElement('afterend', picker);

                    let visibleMonth;

                    function closePicker() {
                        picker.hidden = true;
                        input.setAttribute('aria-expanded', 'false');
                    }

                    function renderPicker() {
                        picker.replaceChildren();

                        const header = document.createElement('div');
                        header.className = 'weekday-picker-header';
                        const previous = document.createElement('button');
                        previous.type = 'button';
                        previous.textContent = 'Previous';
                        previous.setAttribute('aria-label', 'Previous month');
                        const minimumDate = minimumDateFor(input);
                        previous.disabled = visibleMonth <= new Date(
                            minimumDate.getFullYear(),
                            minimumDate.getMonth(),
                            1
                        );
                        previous.addEventListener('click', () => {
                            if (previous.disabled) {
                                return;
                            }
                            visibleMonth.setMonth(visibleMonth.getMonth() - 1);
                            renderPicker();
                        });
                        const title = document.createElement('strong');
                        title.textContent = visibleMonth.toLocaleDateString(undefined, {
                            month: 'long',
                            year: 'numeric'
                        });
                        const next = document.createElement('button');
                        next.type = 'button';
                        next.textContent = 'Next';
                        next.setAttribute('aria-label', 'Next month');
                        next.addEventListener('click', () => {
                            visibleMonth.setMonth(visibleMonth.getMonth() + 1);
                            renderPicker();
                        });
                        header.append(previous, title, next);
                        picker.append(header);

                        const grid = document.createElement('div');
                        grid.className = 'weekday-picker-grid';
                        for (const dayName of ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']) {
                            const dayLabel = document.createElement('span');
                            dayLabel.className = 'weekday-picker-label';
                            dayLabel.textContent = dayName;
                            grid.append(dayLabel);
                        }

                        const firstWeekday = (visibleMonth.getDay() + 6) % 7;
                        for (let blank = 0; blank < firstWeekday; blank++) {
                            grid.append(document.createElement('span'));
                        }
                        const daysInMonth = new Date(
                            visibleMonth.getFullYear(),
                            visibleMonth.getMonth() + 1,
                            0
                        ).getDate();
                        for (let day = 1; day <= daysInMonth; day++) {
                            const date = new Date(
                                visibleMonth.getFullYear(),
                                visibleMonth.getMonth(),
                                day
                            );
                            const dateButton = document.createElement('button');
                            dateButton.type = 'button';
                            dateButton.textContent = String(day);
                            dateButton.disabled = date < minimumDate
                                || date.getDay() === 0
                                || date.getDay() === 6;
                            dateButton.setAttribute('aria-label', date.toLocaleDateString());
                            dateButton.addEventListener('click', () => {
                                if (dateButton.disabled) {
                                    return;
                                }
                                input.value = formatDate(date);
                                closePicker();
                                input.dispatchEvent(new Event('change', { bubbles: true }));
                            });
                            grid.append(dateButton);
                        }
                        picker.append(grid);
                    }

                    input.addEventListener('click', () => {
                        if (picker.hidden) {
                            const selectedDate = input.value ? new Date(input.value + 'T00:00:00') : null;
                            const minimumDate = minimumDateFor(input);
                            const initialDate = selectedDate
                                && !Number.isNaN(selectedDate.getTime())
                                && selectedDate >= minimumDate
                                ? selectedDate
                                : minimumDate;
                            visibleMonth = new Date(initialDate.getFullYear(), initialDate.getMonth(), 1);
                            renderPicker();
                            picker.hidden = false;
                            input.setAttribute('aria-expanded', 'true');
                        } else {
                            closePicker();
                        }
                    });
                    input.addEventListener('keydown', event => {
                        if (event.key === 'Escape') {
                            closePicker();
                        }
                    });

                    return closePicker;
                }

                initializeWeekdayPicker(startDate);
                const closeEndDatePicker = initializeWeekdayPicker(endDate);

                async function refreshReplacements() {
                    const requestId = ++latestRequest;
                    const selectedOption = leaveType.options[leaveType.selectedIndex];
                    const required = selectedOption?.dataset.replacementRequired === '1';
                    const selectedReplacement = replacementSelect.value || originalSelection;
                    replacementField.hidden = !required;
                    replacementSelect.required = required;
                    replacementSelect.replaceChildren(new Option('Choose an eligible replacement', ''));

                    if (!selectedOption || !selectedOption.value) {
                        replacementHelp.textContent = '';
                        return;
                    }
                    if (!required) {
                        replacementHelp.textContent = '';
                        return;
                    }
                    if (!startDate.value || !endDate.value || startDate.value > endDate.value) {
                        replacementHelp.textContent = 'Enter a valid date range to see eligible replacements.';
                        return;
                    }

                    replacementHelp.textContent = 'Loading eligible replacements...';
                    try {
                        const query = new URLSearchParams({
                            leave_type: selectedOption.value,
                            start_date: startDate.value,
                            end_date: endDate.value
                        });
                        const response = await fetch('/leaves/replacements?' + query.toString(), {
                            headers: { 'Accept': 'application/json' }
                        });
                        if (!response.ok) {
                            throw new Error('Unable to load eligible replacements.');
                        }
                        const result = await response.json();
                        if (requestId !== latestRequest) {
                            return;
                        }

                        for (const employee of result.employees) {
                            replacementSelect.add(new Option(employee.name, employee.id));
                        }
                        const hasSelection = Array.from(replacementSelect.options)
                            .some(option => option.value === selectedReplacement);
                        if (hasSelection) {
                            replacementSelect.value = selectedReplacement;
                        }
                        replacementHelp.textContent = result.employees.length
                            ? 'Choose an employee available for the entire leave period.'
                            : 'No eligible replacement is available for the selected dates.';
                    } catch (error) {
                        if (requestId === latestRequest) {
                            replacementHelp.textContent = error.message;
                        }
                    }
                }

                leaveType.addEventListener('change', refreshReplacements);
                startDate.addEventListener('change', () => {
                    closeEndDatePicker();
                    endDate.value = startDate.value;
                    endDate.dispatchEvent(new Event('change', { bubbles: true }));
                });
                endDate.addEventListener('change', refreshReplacements);
                refreshReplacements();
            })();
        </script>
        </div>
    </div>
</div>