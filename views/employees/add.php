<div class="form-page">
    <div class="form-card-wrapper">
        <a class="form-back-link" href="/calendar" aria-label="Back to calendar" title="Back to calendar">
            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 12H5"></path>
                <path d="m12 19-7-7 7-7"></path>
            </svg>
        </a>
        <div class="form-card">
            <h2 class="text-center mb-4">Add employee</h2>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success" role="status">Employee added successfully.</div>
            <?php endif; ?>
            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="/employees/add">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label" for="first-name">First name</label>
                    <input class="form-control" id="first-name" type="text" name="first_name" maxlength="50" autocomplete="given-name" value="<?= htmlspecialchars($firstName ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="last-name">Last name</label>
                    <input class="form-control" id="last-name" type="text" name="last_name" maxlength="50" autocomplete="family-name" value="<?= htmlspecialchars($lastName ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="email">E-mail</label>
                    <input class="form-control" id="email" type="email" name="email" maxlength="100" autocomplete="email" value="<?= htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="username">Username</label>
                    <input class="form-control" id="username" type="text" name="username" maxlength="25" autocomplete="username" value="<?= htmlspecialchars($username ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="position-id">Position</label>
                    <select class="form-control" id="position-id" name="position_id" required>
                        <option value="">Select position</option>
                        <?php foreach ($positions as $position): ?>
                            <option value="<?= (int) $position['id'] ?>" <?= (string) ($selectedPositionId ?? '') === (string) $position['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($position['name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="btn btn-primary w-100 submit_btn" type="submit">Add employee</button>
            </form>
        </div>
    </div>
</div>
