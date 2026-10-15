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
                        replacementHelp.textContent = 'A replacement is not required for this leave type.';
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
                startDate.addEventListener('change', refreshReplacements);
                endDate.addEventListener('change', refreshReplacements);
                refreshReplacements();
            })();
        </script>
        </div>
    </div>
</div>