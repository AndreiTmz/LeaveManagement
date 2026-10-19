document.querySelectorAll('.password-visibility-toggle').forEach((toggle) => {
    const passwordField = toggle.closest('.password-field');
    const input = passwordField?.querySelector('input[type="password"], input[type="text"]');
    const icon = toggle.querySelector('i');

    if (!input || !icon) {
        return;
    }

    toggle.addEventListener('click', () => {
        const isVisible = input.type === 'text';
        input.type = isVisible ? 'password' : 'text';
        toggle.setAttribute('aria-label', isVisible ? 'Show password' : 'Hide password');
        toggle.setAttribute('aria-pressed', String(!isVisible));
        icon.classList.toggle('fa-eye', isVisible);
        icon.classList.toggle('fa-eye-slash', !isVisible);
    });
});
