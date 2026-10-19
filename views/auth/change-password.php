<div class="form-page">
    <div class="form-card-wrapper">
        <a class="form-back-link" href="/calendar" aria-label="Back to calendar" title="Back to calendar">
            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 12H5"></path>
                <path d="m12 19-7-7 7-7"></path>
            </svg>
        </a>
        <div class="form-card">
            <h2 class="text-center mb-4">Change password</h2>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success" role="status">Your password was changed successfully.</div>
            <?php endif; ?>
            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="/change-password">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label" for="current-password">Old password</label>
                    <input class="form-control" id="current-password" type="password" name="current_password" autocomplete="current-password" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="new-password">New password</label>
                    <input class="form-control" id="new-password" type="password" name="new_password" autocomplete="new-password" minlength="8" pattern="(?=.*[A-Z])(?=.*[0-9]).{8,}" aria-describedby="password-requirements" required>
                    <div class="form-text" id="password-requirements">At least 8 characters, including one uppercase letter and one number.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="confirm-password">Confirm new password</label>
                    <input class="form-control" id="confirm-password" type="password" name="confirm_password" autocomplete="new-password" aria-describedby="password-match-error" required>
                    <div class="invalid-feedback" id="password-match-error" role="alert" hidden>
                        The new password and confirmation do not match.
                    </div>
                </div>
                <button class="btn btn-primary w-100 submit_btn" type="submit">Change password</button>
            </form>
        </div>
    </div>
</div>
<script>
    (() => {
        const newPassword = document.getElementById('new-password');
        const confirmPassword = document.getElementById('confirm-password');
        const mismatchMessage = document.getElementById('password-match-error');

        function validatePasswordMatch() {
            const mismatch = confirmPassword.value !== '' && newPassword.value !== confirmPassword.value;
            mismatchMessage.hidden = !mismatch;
            confirmPassword.setCustomValidity(mismatch ? mismatchMessage.textContent.trim() : '');
        }

        newPassword.addEventListener('input', validatePasswordMatch);
        confirmPassword.addEventListener('input', validatePasswordMatch);
    })();
</script>
