<?php 

require 'connection.php';

// Set the content type to application/json so the JS knows what to expect
header('Content-Type: application/json');

/**
 * Function to send a JSON response and terminate the script.
 * @param bool $success Indicates if the operation was successful.
 * @param string $message The message to send to the client.
 * @param int $httpCode The HTTP status code to send (e.g., 200, 400, 401).
 */
function sendJsonResponse($success, $message, $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}


$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

$stmt = $conn->prepare("SELECT userID, email, password_hash, type, studentID, fname FROM tbl_user WHERE email = ? or studentID = ?");
$stmt->bind_param('ss', $username, $username);
$stmt->execute();
$result = $stmt->get_result();

if($result){
    
    $user = $result->fetch_assoc();

    if(password_verify($password,$user['password_hash'])){
        $_SESSION['user_username'] = $username;
        $_SESSION['user_id'] = $user['userID'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_type'] = $user['type']; 
        $_SESSION['user_name'] = $user['fname'];
        
        //echo "<script> alert ('Login Succesful!'); window.location='../homepage.php'; </script>";
        echo json_encode([
            'success'   => true,
            'message'   => 'Login Successful. Redirecting...',
            'user_type' => $user['type']
        ]);
        exit;

    }else{

        //echo "<script> alert ('Incorrect Password'); window.history.back(); </script>";
        sendJsonResponse(false, "Incorrect Password.", 401);
    }

}else{

        //echo "<script> alert ('Username not found!'); window.history.back(); </script>";
        sendJsonResponse(false, "Username not found.", 401);
}

?>