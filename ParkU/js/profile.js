document.addEventListener('DOMContentLoaded', function() {
    // Find the form using its class name
    const form = document.querySelector('.profile-layout-form');
    
    if (form) {
        // Add event listener for form submission
        form.addEventListener('submit', function(event) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            // Validate passwords only if they are being changed (i.e., either field has input)
            if (newPassword || confirmPassword) { 
                
                if (newPassword !== confirmPassword) {
                    alert('New password and confirmation password do not match.');
                    event.preventDefault();
                    return;
                }

            }
        });
    }
});

// Live profile image preview
const profileInput = document.getElementById("profile_picture_input");
const profilePreview = document.getElementById("profileImagePreview");

if (profileInput && profilePreview) {
    profileInput.addEventListener("change", function () {
        const file = this.files[0];

        if (!file) return;

        // Optional: basic validation
        if (!file.type.startsWith("image/")) {
            alert("Please select a valid image file.");
            this.value = "";
            return;
        }

        const reader = new FileReader();

        reader.onload = function (e) {
            profilePreview.src = e.target.result;
        };

        reader.readAsDataURL(file);
    });
}
