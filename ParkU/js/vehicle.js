/**
 * vehicle.js
 * * Client-side logic for the vehicle management page.
 * * Functionality included:
 * 1. Automatic dismissal of the status notification after a delay.
 * 2. Handling the click event for "Edit" buttons to initiate an edit workflow.
 * -> NEW: This now redirects to 'edit_vehicle.php' with the vehicle ID.
 * 3. Basic client-side validation for the "Add Vehicle" form.
 */

document.addEventListener('DOMContentLoaded', () => {
    // --- 1. Notification Dismissal Logic ---
    const notification = document.getElementById('status-notification');
    
    if (notification) {
        // Set a timeout to fade out and remove the notification
        setTimeout(() => {
            // Apply opacity transition (assuming CSS handles the smooth fade)
            notification.style.opacity = '0';
            
            // Wait for the transition to complete before removing from DOM
            setTimeout(() => {
                notification.remove();
            }, 500); // 500ms should match the CSS transition duration
        }, 5000); // Notification remains visible for 5 seconds
    }


    // --- 2. Edit Action Handler (Redirects to edit_vehicle.php) ---
    
    // Select all buttons with the class 'edit-button'
    document.querySelectorAll('.edit-button').forEach(button => {
        button.addEventListener('click', (event) => {
            // Stop the default action (if it was a link/form submit)
            event.preventDefault(); 
            
            // Get the ID of the vehicle to be edited from the data attribute
            const vehicleID = event.currentTarget.dataset.vehicleId; 

            if (vehicleID) {
                console.log(`Redirecting to edit Vehicle ID: ${vehicleID}`);
                
                // Construct the URL to your PHP file, passing the ID as a query parameter
                window.location.href = `edit_vehicle.php?id=${vehicleID}`;

            } else {
                 console.error('Edit button missing data-vehicle-id attribute.');
            }
        });
    });


    // --- 3. Simple Form Validation on Add Vehicle Form ---
    const addVehicleForm = document.querySelector('.add-vehicle-form form');
    if (addVehicleForm) {
        addVehicleForm.addEventListener('submit', (event) => {
            const plateNumberInput = document.getElementById('plateNumber');
            const plateNumber = plateNumberInput ? plateNumberInput.value.trim() : '';
            
            // Check for minimum plate length (simple validation)
            if (plateNumber.length < 4) {
                alert('Please enter a valid plate number (at least 4 characters).');
                event.preventDefault(); // Stop form submission
                plateNumberInput.focus();
                return;
            }
            
            // Client-side file size check for the image (if file input exists)
            const fileInput = document.getElementById('vehicleImage');
            if (fileInput && fileInput.files.length > 0) {
                const fileSize = fileInput.files[0].size; // size in bytes
                const maxSize = 5 * 1024 * 1024; // 5MB limit
                
                if (fileSize > maxSize) {
                    alert('The image file is too large. Maximum size is 5MB.');
                    event.preventDefault();
                    return;
                }
            }
        });
    }
});