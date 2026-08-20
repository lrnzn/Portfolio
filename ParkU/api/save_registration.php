<?php

header('Content-Type: application/json');

require 'connection.php';

/**
 * Function to send a JSON response, close the connection, and terminate the script.
 */
function sendJsonResponse($success, $message, $http_code = 200) {
    global $conn;
    if ($conn && method_exists($conn, 'close')) {
        $conn->close();
    }
    http_response_code($http_code);
    echo json_encode(['success' => $success, 'message' => $message]);
    exit();
}

$fname = trim($_POST['fname'] ?? '');
$mname_raw = trim($_POST['mname'] ?? '');
$lname = trim($_POST['lname'] ?? '');
$nameExt_raw = trim($_POST['nameExt'] ?? '');
$type = trim($_POST['type'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$studentID_raw = trim($_POST['studentID'] ?? '');
$confirmPassword = ($_POST['confirmPassword'] ?? ''); 

if ($password !== $confirmPassword) {
    sendJsonResponse(false, "Passwords do not match.", 400);
}

$mname = empty($mname_raw) ? NULL : $mname_raw;
$nameExt = empty($nameExt_raw) ? NULL : $nameExt_raw;


$studentID = NULL;
if ($type === 'Student') {
    if (empty($studentID_raw)) {
        sendJsonResponse(false, "Student ID is required for Students.", 400);
    }
    $studentID = $studentID_raw; 
} 

// Check if email already exists
$check_stmt = $conn->prepare("SELECT COUNT(*) FROM tbl_user WHERE email = ?");
if (!$check_stmt) {
    sendJsonResponse(false, "Prepare failed: " . $conn->error, 500);
}
$check_stmt->bind_param("s", $email);
$check_stmt->execute();
$check_stmt->bind_result($count);
$check_stmt->fetch();
$check_stmt->close();

if ($count > 0) {
    sendJsonResponse(false, "Registration failed: This email is already registered.", 409);
}

// Check if student ID already exists (if provided)
if ($studentID) {
    $check_stmt_id = $conn->prepare("SELECT COUNT(*) FROM tbl_user WHERE studentID = ?");
    if (!$check_stmt_id) {
        sendJsonResponse(false, "Prepare failed: " . $conn->error, 500);
    }
    $check_stmt_id->bind_param("s", $studentID);
    $check_stmt_id->execute();
    $check_stmt_id->bind_result($id_count);
    $check_stmt_id->fetch();
    $check_stmt_id->close();
    
    if ($id_count > 0) {
        sendJsonResponse(false, "Registration failed: This Student ID is already registered.", 409);
    }
}



$hashed = password_hash($password, PASSWORD_BCRYPT);

$sql = "INSERT into tbl_user (fname, mname, lname, nameExt, type, email, password_hash, studentID) values (?,?,?,?,?,?,?,?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssss", $fname, $mname, $lname, $nameExt, $type, $email, $hashed, $studentID);

if($stmt->execute()){
    // FIX: Send the final success response using the function
    sendJsonResponse(true, 'Registration successful! Redirecting you to the login page.', 201); 
}else{
    // FIX: Send the failure response using the function
    error_log("MySQLi Insert Error: " . $stmt->error);
    sendJsonResponse(false, 'Database insertion failed: ' . $stmt->error, 500); 
}

// Fallback to ensure the script terminates with a JSON response
sendJsonResponse(false, "An unknown error occurred after processing.", 500);

?>