/**
 * PARK U - Login Form Handler (Client-Side Logic)
 * * IMPORTANT: This script is synchronized to the following IDs/Classes in your HTML:
 * 1. Form: .login-form
 * 2. Message Container: #form-messages
 * 3. Login Button: .login-button
 * * This version uses inline styles for feedback colors, similar to signup.js.
 */

document.addEventListener('DOMContentLoaded', () => {
    // --- 1. DOM Element Selection ---
    const loginForm = document.querySelector('.login-form');
    const formMessages = document.getElementById('form-messages');
    const loginButton = document.querySelector('.login-button');

    if (!loginForm || !formMessages || !loginButton) {
        console.error("Initialization Error: One or more required DOM elements are missing. Check the following selectors: .login-form, #form-messages, .login-button.");
        return;
    }

    // --- 2. Helper Function to Display Server Feedback with Inline Styles ---

    /**
     * Displays a message (error or success) to the user in the form.
     */
    function displayFeedback(message, type = 'error') {
        formMessages.textContent = message;

        // Reset class to the base style (assuming 'form-messages' handles padding/margin)
        formMessages.className = 'form-messages';
        formMessages.style.display = 'block';

        if (type === 'error') {
            // Apply RED inline styles for error messages
            formMessages.style.backgroundColor = '#fcebeb'; // Light red background
            formMessages.style.color = '#cc0000';          // Dark red text color
            formMessages.style.borderColor = '#ffbaba';      // Red border
        } else if (type === 'success') {
            // Apply GREEN inline styles for success messages
            formMessages.style.backgroundColor = '#ebfcee'; // Light green background
            formMessages.style.color = '#38a169';          // Green text color
            formMessages.style.borderColor = '#92dfa9';
        } else {
            // Reset to default if type is unknown
            formMessages.style.backgroundColor = 'transparent';
            formMessages.style.color = 'inherit';
            formMessages.style.borderColor = 'transparent';
        }
    }


    // --- 3. Form Submission Handler (AJAX) ---
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault(); // Stop the default page reload/redirect

        displayFeedback('', 'neutral'); // Clear previous messages
        
        // Disable button and show loading state
        loginButton.disabled = true;
        loginButton.textContent = 'Logging in...';

        const actionUrl = loginForm.getAttribute('action');
        const formData = new FormData(loginForm);

        try {
            // Send the form data to the server
            const response = await fetch(actionUrl, {
                method: 'POST',
                body: formData 
            });

            if (response.status === 404) {
                 displayFeedback("Error: Login API file not found (404). Check the file path 'api/validate_login.php'.");
                 return;
            }
            
            // Read the JSON body from the PHP API
            const data = await response.json();
            
            if (data.success) {
                // Login successful (status 200 from PHP)
                displayFeedback(data.message || "Login Successful. Redirecting...", 'success');
                
                // Redirect after a short delay
                setTimeout(() => {
                    if (data.user_type === 'Admin') {
                        window.location.href = 'admin/admin_dashboard.php';
                    } else {
                        window.location.href = 'homepage.php';
                    }
                }, 500);

            } else {
                // Login failed (status 401 from PHP)
                displayFeedback(data.message || "An unknown login failure occurred.");
            }

        } catch (error) {
            console.error('Fetch error:', error);
            displayFeedback("Email/StudentID not found.");
        } finally {
            // Re-enable the button if not redirecting
            if (loginButton.disabled) {
                loginButton.disabled = false;
                loginButton.textContent = 'Log In';
            }
        }
    });
});