<?php ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
    <link rel="stylesheet" href="css/style.css">

    <style>
        .hidden { 
            display: none !important; 
        }
    </style>
</head>

<body class="login-page">

    <div class="login-card welcome-panel">
        
        <h1 class="logo login-logo">PARK U</h1>
        <h2>Create Account</h2>
        <p>Register to start reserving your parking spot.</p>
       

        <form action="api/save_registration.php" method="POST" class="login-form" id="signup-form">
            <div class="form-row">
                <div class="field-group half-width">
                    <label for="fname">First Name</label>
                    <div class="input-container">
                        <input type="text" id="fname" name="fname" placeholder="Juan" required>
                    </div>
                </div>
                <div class="field-group half-width">
                    <label for="mname">Middle Name (Optional)</label>
                    <div class="input-container">
                        <input type="text" id="mname" name="mname" placeholder="Santos">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="field-group half-width">
                    <label for="lname">Last Name</label>
                    <div class="input-container">
                        <input type="text" id="lname" name="lname" placeholder="Dela Cruz" required>
                    </div>
                </div>
                <div class="field-group half-width">
                    <label for="nameExt">Name Extension (e.g., Jr., III)</label>
                    <div class="input-container">
                        <input type="text" id="nameExt" name="nameExt" placeholder="Sr./Jr./III" maxlength="5">
                    </div>
                </div>
            </div>

            <div class="field-group">
                    <label for="type">User Type</label>
                    <div class="input-container">
                        <select id="type" name="type" required>
                            <option value="" disabled selected>Select...</option>
                            <option value="Student">Student</option>
                            <option value="Faculty">Faculty</option>
                            <option value="Admin">Admin</option>
                        </select>
                    </div>
            </div>

            <div id="student-id-field" class="field-group">
                <label for="studentID">Student ID</label>
                <div class="input-container">
                    <input type="text" id="studentID" name="studentID" placeholder="e.g., DJS01010500">
                </div>
            </div>

            <div class="field-group">
                <label for="email">Email Address</label>
                <div class="input-container">
                    <input type="email" id="email" name="email" placeholder="Enter your email address" required>
                </div>
            </div>

            <div class="field-group">
                <label for="password">Password</label>
                <div class="input-container">
                    <input type="password" id="password" name="password" placeholder="Create a password" required>
                </div>
            </div>

            <div class="field-group">
                <label for="confirmPassword">Confirm Password</label>
                <div class="input-container">
                    <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Re-enter your password" required>
                </div>
            </div>

            <div id="form-messages" class="form-messages"></div>
            
            <button type="submit" class="check-button-primary login-button">Sign Up</button>
            
        </form>

        <p class="auth-footer-link">
            Already have an account? <a href="login.php">Log In here</a>
        </p>
    </div>

    <script src="js/signup.js"></script>

</body>
</html>