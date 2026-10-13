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

        <form method="POST" action="request">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label">Start Date</label>
                <input class="form-control" type="date" name="start_date" required
                    value="<?= htmlspecialchars($startDate ?? '') ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">End Date</label>
                <input class="form-control" type="date" name="end_date" required
                    value="<?= htmlspecialchars($endDate ?? '') ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Leave type</label>
                <select class="form-control" name="leave_type" required>
                    <option value="">Select leave type</option>
                    <?php foreach ($leaveTypes ?? [] as $leaveType): ?>
                        <option value="<?= htmlspecialchars($leaveType['id']) ?>" 
                            <?= (isset($selectedLeaveType) && $selectedLeaveType == $leaveType['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($leaveType['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($errorMessage) ?>
                </div>
            <?php endif; ?>

            <button class="btn btn-primary w-100 submit_btn" type="submit">
                Request leave
            </button>
        </form>
        </div>
    </div>
</div>