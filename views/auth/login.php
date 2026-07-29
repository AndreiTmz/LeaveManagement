<div class="form-card">
    <h2 class="text-center mb-4">
        Leave Management
    </h2>

    <form method="POST" action="/login">

        <?= csrf_field() ?>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input class="form-control" type="email" name="email" required
                value="<?= htmlspecialchars($email ?? '') ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input class="form-control" type="password" name="password" required>
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