<?php 

require 'api/connection.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Prepare data
$userID = $_SESSION['user_id'];
$user = [];
$message = '';
$message_type = ''; // 'success' or 'error'

// Handle status messages from update_profile.php ---
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'success') {
        $message = 'Profile successfully updated!';
        $message_type = 'success';
    } elseif (isset($_GET['msg'])) {
        // Use htmlspecialchars and urldecode to safely display the error message
        $message = 'Update failed: ' . htmlspecialchars(urldecode($_GET['msg']));
        $message_type = 'error';
    }
}

// Fetch user data from the database using prepared statements for security
// Note: Assuming `userID` is an integer type (i), change to string (s) if needed
$stmt = $conn->prepare("SELECT studentID, fname, mname, lname, nameExt, email, type, password_hash, image FROM tbl_user WHERE userID = ?");
$stmt->bind_param("i", $userID); 
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    // Set display variables
    $username = htmlspecialchars($user['fname'] ?? 'User');
    $type = htmlspecialchars($user['type'] ?? 'Student');
    $studentID = htmlspecialchars($user['studentID'] ?? 'N/A');
    $fname = htmlspecialchars($user['fname'] ?? '');
    $mname = htmlspecialchars($user['mname'] ?? '');
    $lname = htmlspecialchars($user['lname'] ?? '');
    $nameExt = htmlspecialchars($user['nameExt'] ?? '');
    $email = htmlspecialchars($user['email'] ?? '');

// Determine current profile image path ***
    $default_image_path = 'img/user icon.png'; 
    $current_image_path = $default_image_path;

    if (!empty($user['image'])) {
        $stored_path = $user['image'];
        // Check if the file actually exists on the server before using the path
        if (file_exists($stored_path)) {
            $current_image_path = $stored_path;
        } 
        // If file doesn't exist, it uses the default path.
    }
    // -------------------------------------------------------------

} else {
    // Handle case where user data is missing (should log out for safety)
    $conn->close();
    header('Location: login.php?action=logout');
    exit;
}

$stmt->close();
$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php 
    if ($message) {
    echo "<div id='status-notification' class='notification-box {$message_type}'>";
    echo $message ;
    echo "</div>";
}
?>

    <header class="main-header">
        <a href="homepage.php" class="logo">PARK U</a>
        <nav class="main-nav">
            <a href="homepage.php">Home</a>
            <a href="profile.php"  class="active-link">My Profile</a>
            <a href="vehicle.php">My Vehicle</a>
            <a href="reservations.php">My Reservations</a>
            <a href="archived.php">Archived</a>
            <a href="help.php">Help</a>
            <a href="login.php?action=logout">Sign Out</a>
        </nav>
    </header>

    <div class="app-container">

        <section class="welcome-panel">
       
            <h2>MY PROFILE</h2>
            <p>View and manage your personal account details.</p>
            
        </section>

        <section class="reservation-panel"> 
            
                <!-- Start of the form for profile updates -->
            <form action="api/update_profile.php" method="POST" class="profile-layout-form" enctype="multipart/form-data">

                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($userID); ?>">
                
            <div class="profile-layout">
                
                <div class="avatar-section">
                    
                    <div class="profile-picture-container" onclick="document.getElementById('profile_picture_input').click();">
                        <img 
                            src="<?php echo htmlspecialchars($current_image_path); ?>" 
                            alt="User Icon"
                            class="profile-img"
                            id="profileImagePreview"
                            onerror="this.onerror=null; this.src='<?php echo $default_image_path; ?>';"
                        >
                        <label for="profile_picture_input" class="upload-button"></label>
                    </div>
                    <!-- Hidden file input field -->
                    <input type="file" name="profile_picture" id="profile_picture_input" accept="image/jpeg,image/png,image/gif" style="display: none;">
                    <p class="text-center text-sm text-gray-500 mb-4">Click the picture to change it.</p>
                    
                    <h4 class="username"><?php echo ($username); ?></h4>
                    <p class="type"><?php echo $type; ?> (CHMSU)</p>
                    <button type="submit" class="check-button-primary small-button">Save Changes</button>
                </div>

                <div class="info-section">
                    
                    <h3>Personal Information</h3>
                    
                    <div class="form-row">
                        <div class="field-group">
                            <label for="student-id">Student ID</label>
                            <div class="input-container">
                                <input type="text" id="studentID" value="<?php echo $studentID; ?>" readonly>
                            </div>
                        </div>
                        <div class="field-group">
                            <label for="fname">First Name</label>
                            <div class="input-container">
                                <input type="text" id="fname" name="fname" value="<?php echo $fname; ?>">
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="field-group">
                            <label for="mname">Middle Name</label>
                            <div class="input-container">
                                <input type="text" id="mname" name="mname" value="<?php echo $mname; ?>">
                            </div>
                        </div>
                    
                    
                        <div class="field-group">
                            <label for="lname">Last Name</label>
                            <div class="input-container">
                                <input type="text" id="lname" name="lname" value="<?php echo $lname; ?>">
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="field-group">
                            <label for="nameExt">Name Extension</label>
                            <div class="input-container">
                                <input type="text" id="nameExt" name="nameExt" value="<?php echo $nameExt; ?>">
                            </div>
                        </div>
                    
                    

                         <div class="field-group">
                            <label for="email">Email Address</label>
                            <div class="input-container">
                                <input type="email" id="email" name="email" value="<?php echo $email; ?>">
                            </div>
                        </div>
                        
                    </div>

                    <h3 class="security-title">Security</h3>
                    <div class="form-row">
                        <div class="field-group">
                            <label for="password-change">New Password (Optional)</label>
                            <div class="input-container">
                                <input type="password" id="new_password" name="new_password" placeholder="Leave blank to keep current">
                            </div>
                        </div>
                        <div class="field-group">
                            <label for="last-login">Confirm Password</label>
                            <div class="input-container">
                                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </div>

<script src="js/profile.js"></script>
<script src="js/notification.js"></script>

</body>

</html>