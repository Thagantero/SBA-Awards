for (const toggle of document.querySelectorAll('.password-toggle')) {
    const password = document.getElementById(toggle.getAttribute('aria-controls'));
    if (!password) continue;
    toggle.hidden = false;
    toggle.addEventListener('click', () => {
        const visible = password.type === 'password';
        password.type = visible ? 'text' : 'password';
        toggle.classList.toggle('password-visible', visible);
        toggle.setAttribute('aria-label', `${visible ? 'Hide' : 'Show'} ${toggle.dataset.passwordLabel}`);
    });
}

const password = document.getElementById('password');
const confirmation = document.getElementById('confirm_password');
if (password && confirmation) {
    function checkConfirmation() {
        confirmation.setCustomValidity(confirmation.value && password.value !== confirmation.value
            ? 'Passwords do not match.' : '');
    }
    password.addEventListener('input', checkConfirmation);
    confirmation.addEventListener('input', checkConfirmation);
}
