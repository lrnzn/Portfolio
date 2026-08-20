// LibroTrack — Signup Page JS | public/assets/js/signup.js

function toggleSignupPassword() {
    const input = document.getElementById('password');
    input.type = input.type === 'password' ? 'text' : 'password';
}

// Confirm password validation before submit
document.querySelector('form').addEventListener('submit', function (e) {
    const password        = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm-password').value;

    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match. Please try again.');
        document.getElementById('confirm-password').focus();
    }
});