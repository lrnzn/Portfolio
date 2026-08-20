// LibroTrack — Login Page JS | public/assets/js/login.js

function selectRole(role) {
    const isLibrarian = role === 'librarian';
    document.getElementById('role-input').value = role;
    document.getElementById('btn-librarian').classList.toggle('active', isLibrarian);
    document.getElementById('btn-student').classList.toggle('active', !isLibrarian);
    document.getElementById('librarian-fields').style.display = isLibrarian ? 'block' : 'none';
    document.getElementById('student-fields').style.display   = isLibrarian ? 'none'  : 'block';
    document.getElementById('register-prompt').style.display  = isLibrarian ? 'none'  : 'block';
}

function togglePassword() {
    const input = document.getElementById('password');
    input.type = input.type === 'password' ? 'text' : 'password';
}

function handleLogin(e) {
    e.preventDefault();
    const form = e.target;
    form.action = 'index.php?controller=Auth&action=authenticate';
    form.method = 'POST';
    form.submit();
}