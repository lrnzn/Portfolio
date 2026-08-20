<?php

require 'connection.php';

// Function to safely redirect with status messages
function redirectWithError($conn, $msg) {
    // Close connection safely if it's still open
    if ($conn && $conn->ping()) { $conn->close(); } 
    // Use rawurlencode for safety, then urldecode in the receiver (profile.php)
    header("Location: ../profile.php?status=error&msg=" . rawurlencode($msg));
    exit;
}

// 1. Ensure this script is only accessed via POST request
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirectWithError($conn, "Invalid request method.");
}

// 2. Security Check: Must be logged in and submitted user_id must match session
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login page
    header('Location: ../login.php');
    exit;
}

$submitted_id = $_POST['user_id'] ?? null;
$session_id = (int)$_SESSION['user_id']; 

if (empty($submitted_id) || (int)$submitted_id !== $session_id) {
    // Critical failure: submitted ID does not match the authenticated session ID
    redirectWithError($conn, "Security validation failed. User ID mismatch.");
}

// Sanitize and collect input data
$userID = $session_id; // Use the trusted session ID from the session
$fname = trim($_POST['fname'] ?? '');
$mname = trim($_POST['mname'] ?? '');
$lname = trim($_POST['lname'] ?? '');
$nameExt = trim($_POST['nameExt'] ?? '');
$email = trim($_POST['email'] ?? '');
$new_password = $_POST['new_password'] ?? ''; 

if ($mname === '') {
    $mname = null;
}


if ($nameExt === '') {
    $nameExt = null;
}

// -------------------------------------------------------------------
// Profile Picture Upload Handling (using 'image' column name)
// -------------------------------------------------------------------

// Target directory is '../img/' relative to the API folder
$upload_dir = '../img/user_images/'; 
// Variable to hold the path saved to the DB
$image_path = null; 
$picture_updated = false;

// 4. Handle File Upload if present and valid
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['profile_picture'];

    // 4.1 Create directory if it doesn't exist
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) {
            // Note: If you get an error here, check directory permissions for the parent folder (uploads).
            redirectWithError($conn, "Failed to create upload directory. Check file permissions.");
        }
    }

    // 4.2 File Validation (Size and Type)
    $max_size = 5 * 1024 * 1024; // 5 MB
    if ($file['size'] > $max_size) {
        redirectWithError($conn, "File size exceeds the 5MB limit.");
    }

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    // Use finfo to reliably determine MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime_type, $allowed_types)) {
        redirectWithError($conn, "Invalid file type. Only JPEG, PNG, and GIF are allowed.");
    }

    // 4.3 Generate a unique file name
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $unique_filename = uniqid('pp_', true) . '.' . $file_extension;
    $target_file = $upload_dir . $unique_filename;

    // 4.4 Move the uploaded file
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        // Store the relative path to be saved in the database
        // *** CRITICAL CHANGE: Path saved to DB is 'img/' + filename ***
        $image_path = 'img/user_images/' . $unique_filename; 
        $picture_updated = true;
    } else {
        // This is often where the 777 permission issue manifests
        redirectWithError($conn, "Failed to move uploaded file. Check directory permissions for 'img/'.");
    }
}
// -------------------------------------------------------------------

// 3. Validation

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectWithError($conn, "Invalid email format.");
}


try {
    // --- Determine update type and build query dynamically ---
    
    $params = [];
    $types = "";
    $set_clauses = [];

    // Base fields (always update)
    $set_clauses[] = "fname = ?";
    $params[] = $fname;
    $types .= "s";

    $set_clauses[] = "mname = ?";
    $params[] = $mname;
    $types .= "s";
    
    $set_clauses[] = "lname = ?";
    $params[] = $lname;
    $types .= "s";
    
    $set_clauses[] = "nameExt = ?";
    $params[] = $nameExt;
    $types .= "s";
    
    $set_clauses[] = "email = ?";
    $params[] = $email;
    $types .= "s";


    // Add profile picture to update if it was uploaded
    if ($picture_updated) {
        //  Using the actual column name 'image'
        $set_clauses[] = "image = ?"; 
        $params[] = $image_path;
        $types .= "s"; // Image path is a string
    }

    // Password field (conditional update)
    if (!empty($new_password)) {
        // Password complexity check
        
        // Hash the new password before storing
        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $set_clauses[] = "password_hash = ?";
        $params[] = $password_hash;
        $types .= "s";
    }

    // Finalize the SQL query
    $sql = "UPDATE tbl_user SET " . implode(", ", $set_clauses) . " WHERE userID = ?";
    $params[] = $userID; // The user ID for the WHERE clause
    $types .= "i";       // Assuming userID is an integer

    // Prepare and execute the update
    $stmt = $conn->prepare($sql);
    
    // Dynamically bind parameters using references
    $bind_names = [$types];
    for ($i = 0; $i < count($params); $i++) {
        $bind_names[] = &$params[$i];
    }
    // Bind parameters to the prepared statement
    call_user_func_array([$stmt, 'bind_param'], $bind_names);

    if ($stmt->execute()) {
        // Update session name for immediate display refresh
        $_SESSION['user_name'] = $fname; 
        
        $stmt->close();
        $conn->close();
        
        // Redirect back to profile page with success status
        header("Location: ../profile.php?status=success");
        exit;
    } else {
        $error_msg = $stmt->error;
        $stmt->close();
        redirectWithError($conn, "Database execution failed: " . $error_msg);
    }

} catch (Exception $e) {
    // Catch any unexpected exceptions
    redirectWithError($conn, "Server Exception: " . $e->getMessage());
}
?>