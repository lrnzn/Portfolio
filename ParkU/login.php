<?php 

// --- LOGOUT HANDLER ---
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    // 1. Clear all session variables
    $_SESSION = array();

    // 2. Destroy the session cookie (if one exists)
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();
    
    header("Location: login.php");
    exit;
}

// SECURITY CHECK: If the user IS logged in, redirect them to the homepage immediately.
if (isset($_SESSION['user_id'])) {
    header('Location: homepage.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css"> 
</head>
<body class="login-page">

    <div class="login-card welcome-panel">
        
        <a href="homepage.php" class="logo login-logo">PARK U</a>
        <h2>Welcome Back!</h2>
        <p>Sign in to reserve your parking spot.</p>
       
        <form action="api/validate_login.php" method="POST" class="login-form"> 
            
            <div class="field-group">
                <label for="username">Email or Student ID</label>
                <div class="input-container">
                    <input type="text" id="username" name="username" placeholder="Enter your email or student ID" required>
                </div>
            </div>

            <div class="field-group">
                <label for="password">Password</label>
                <div class="input-container">
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>
            </div>
            
            <div id="form-messages" class="form-messages"></div>

            <button type="submit" class="check-button-primary login-button">Log In</button>
        </form>

        <p class="auth-footer-link">
            Don't have an account? <a href="signup.php">Sign up here</a>
        </p>
    </div>

    <script src="js/login.js"></script>

</body>
</html>