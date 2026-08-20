/**
 * PARK U - Sign Up Form Handler (Client-Side Logic)
 * * IMPORTANT: This script is synchronized to the following IDs in signup.php:
 * 1. Form: #signup-form
 * 2. User Type Select: #type
 * 3. Student ID Container: #student-id-field
 * 4. Student ID Input: #studentID
 */

document.addEventListener('DOMContentLoaded', () => {
    // --- 1. DOM Element Selection (MUST MATCH signup.php IDs EXACTLY) ---
    const registrationForm = document.getElementById('signup-form');
    const feedbackMessageDiv = document.getElementById('form-messages'); 
    // Select element for User Type now uses id="type"
    const userTypeSelect = document.getElementById('type'); 
    // Container div for Student ID field
    const studentIdFieldContainer = document.getElementById('student-id-field'); 
    // Input for Student ID now uses id="studentID"
    const studentIdInput = document.getElementById('studentID'); 
    
    if (!registrationForm || !feedbackMessageDiv || !userTypeSelect || !studentIdInput || !studentIdFieldContainer) {
        console.error("Initialization Error: One or more required DOM elements are missing or IDs do not match the HTML. Check the following IDs: #signup-form, #form-messages, #type, #student-id-field, #studentID.");
        return; 
    }
    
    const submitButton = registrationForm.querySelector('button[type="submit"]');

    // --- 2. Dynamic UI Logic (Show/Hide Student ID) ---

    /**
     * Toggles the visibility and required status of the student ID field based on User Type.
     * It uses the 'hidden' CSS class to control visibility (you must ensure .hidden { display: none !important; } is in your style.css).
     */
    function toggleStudentIdField() {
        const selectedType = userTypeSelect.value;
        const isStudent = selectedType === 'Student';

        if (isStudent) {
            // Show the field by removing the 'hidden' class
            studentIdFieldContainer.classList.remove('hidden'); 
            studentIdInput.setAttribute('required', 'required');
            studentIdInput.focus();
        } else {
            // Hide the field by adding the 'hidden' class
            studentIdFieldContainer.classList.add('hidden');
            studentIdInput.removeAttribute('required');
            studentIdInput.value = ''; // Clear the field for non-students
        }
    }

    // Attach listener and set initial state
    userTypeSelect.addEventListener('change', toggleStudentIdField);
    // Important: The initial state should hide the student ID field if no selection is made, 
    // or show it if 'Student' is selected (which isn't the default).
    // The initial select option is disabled, so we manually hide the field first.
    studentIdFieldContainer.classList.add('hidden');
    studentIdInput.removeAttribute('required');

    // --- 3. Helper Function to Display Server Feedback ---

    /**
     * Displays a message (error or success) to the user in the form.
     */
    function displayFeedback(message, type = 'error') {
        feedbackMessageDiv.textContent = message;
        
        // Ensure the base class is applied for padding/border structure
        feedbackMessageDiv.className = 'form-messages'; 
        feedbackMessageDiv.style.display = 'block';

        if (type === 'error') {
            // Apply RED inline styles for error messages
            feedbackMessageDiv.style.backgroundColor = '#fcebeb'; // Light red background
            feedbackMessageDiv.style.color = '#cc0000';          // Dark red text color
            feedbackMessageDiv.style.borderColor = '#ffbaba';      // Red border
        } else if (type === 'success') {
            // Apply GREEN inline styles for success messages
            feedbackMessageDiv.style.backgroundColor = '#ebfcee'; // Light green background
            feedbackMessageDiv.style.color = '#38a169';          // Green text color
            feedbackMessageDiv.style.borderColor = '#92dfa9';
        } else {
            // Reset to default if type is unknown
            feedbackMessageDiv.style.backgroundColor = 'transparent';
            feedbackMessageDiv.style.color = 'inherit';
            feedbackMessageDiv.style.borderColor = 'transparent';
        }
    }

    // --- 4. Form Submission Handler (AJAX) ---
    
    /**
     * Handles the asynchronous form submission to the PHP backend.
     */
    const handleRegistration = async (event) => {
        event.preventDefault(); 
        
        // --- Client-Side Validation: Password Match ---
        const passwordInput = document.getElementById('password').value;
        const confirmPasswordInput = document.getElementById('confirmPassword').value;

        if (passwordInput !== confirmPasswordInput) {
             displayFeedback('Error: Passwords do not match. Please verify your entries.');
             return;
        }
        
        // --- Client-Side Validation: User Type Selected ---
        if (userTypeSelect.value === "") {
             displayFeedback('Error: Please select a User Type (Student, Faculty, or Admin).');
             return;
        }


        // --- Prepare and Send Data ---
        const formData = new FormData(registrationForm);
        
        // Indicate loading state
        submitButton.textContent = 'Processing...';
        submitButton.disabled = true;
        displayFeedback('Registering your account...', 'success');

        try {
            // Send Data to PHP Backend
            const response = await fetch('api/save_registration.php', {
                method: 'POST',
                body: formData 
            });

            // PHP is expected to return a JSON object (e.g., {success: true, message: '...'})
            const result = await response.json();

            if (response.ok && result.success) {
                // Registration successful
                displayFeedback(result.message || 'Registration successful! Redirecting to login...', 'success');
                
                // Redirect after a delay
                setTimeout(() => {
                    window.location.href = 'login.php'; 
                }, 2000);

            } else {
                // Registration failed due to server logic (e.g., email already exists, 400 Bad Request)
                const errorMessage = result.message || `Registration failed. Server status: ${response.status}`;
                displayFeedback(errorMessage);
            }

        } catch (error) {
            // Network or server communication error
            console.error('AJAX Network Error:', error);
            displayFeedback('A critical connection error occurred. Check server status (XAMPP) and the API endpoint path.');
        } finally {
            // Restore Button State if not redirecting
            if (submitButton.disabled) {
                 submitButton.textContent = 'Sign Up';
                 submitButton.disabled = false;
            }
        }
    };

    // --- 5. Attach Event Listener for Submission ---
    registrationForm.addEventListener('submit', handleRegistration);
});