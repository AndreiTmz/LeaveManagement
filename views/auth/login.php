<div class="login-page">
    <div class="form-card-wrapper">
        <div class="form-card">
            <h2 class="text-center mb-4">
                Leave Management
            </h2>

            <form method="POST" action="/login">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label" for="login-identifier">Email or username</label>
                    <input class="form-control" id="login-identifier" type="text" name="identifier" autocomplete="username" required
                        value="<?= htmlspecialchars($identifier ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <div class="password-field">
                        <input class="form-control" type="password" name="password" autocomplete="current-password" required>
                        <button class="password-visibility-toggle" type="button" aria-label="Show password" aria-pressed="false">
                            <i class="fa-solid fa-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>

                <?php if (!empty($errorMessage)): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($errorMessage) ?>
                    </div>
                <?php endif; ?>

                <button class="btn btn-primary w-100 submit_btn" type="submit">
                    Login
                </button>
            </form>
        </div>
    </div>
</div>