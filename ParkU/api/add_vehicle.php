<?php

require 'connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Sanitize and Validate Input
    $userID = $_POST['user_id'] ?? null;
    $plateNumber = trim($_POST['plateNumber'] ?? '');
    $vehicleType = $_POST['vehicleType'] ?? null;
    $vehicleBrand = $_POST['vehicleBrand'] ?? null;
    $model = $_POST['model'] ?? null;
    $transmission = $_POST['transmission'] ?? null;
    $year = $_POST['year'] ?? null;
    $color = $_POST['color'] ?? null;

    if ($color === '') {
    $color = null;
    }

    if (!$userID || empty($plateNumber) || !$vehicleType || !$vehicleBrand || !$model || !$transmission || !$year) {
        $_SESSION['message'] = "Missing required vehicle information.";
        $_SESSION['message_type'] = 'error';
        header("Location: ../vehicle.php");
        exit();
    }

    // --- 2. GLOBAL DUPLICATE PLATE NUMBER CHECK (NEW CRITICAL LOGIC) ---
    // Check if the plate number already exists for ANY user in the database
    $check_stmt = $conn->prepare("SELECT plateNumber, userID FROM tbl_vehicle WHERE plateNumber = ?");
    $check_stmt->bind_param("s", $plateNumber);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $existing_vehicle = $check_result->fetch_assoc();
        
        // Check if the plate number belongs to the current user (in case they try to register the same vehicle twice)
        if ($existing_vehicle['userID'] == $userID) {
             $_SESSION['message'] = "This plate number is already registered under your account.";
        } else {
             // Block registration if the plate number belongs to a different user
             $_SESSION['message'] = "The plate number '{$plateNumber}' is already registered by another user. Please check the number or contact support.";
        }
        
        $_SESSION['message_type'] = 'error';
        $check_stmt->close();
        $conn->close();
        header("Location: ../vehicle.php");
        exit();
    }
    $check_stmt->close();


    // 3. Handle Vehicle Image Upload
    $imagePath = null;
    if (isset($_FILES['vehicleImage']) && $_FILES['vehicleImage']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['vehicleImage'];
        $fileName = basename($file['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = array('jpg', 'jpeg', 'png', 'gif');

        if (in_array($fileExt, $allowedExtensions)) {
            // Create a unique file name
            $newFileName = uniqid('vehicle_') . '.' . $fileExt;
            $uploadDir = '../img/vehicle_images/';
            
            // Ensure the uploads directory exists
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $uploadPath = $uploadDir . $newFileName;
            
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                // Save the path relative to the root for database storage
                $imagePath = 'img/vehicle_images/' . $newFileName;
            } else {
                $_SESSION['message'] = "File upload failed, but vehicle details may still be registered.";
                $_SESSION['message_type'] = 'warning';
                // Continue execution without the image path
            }
        } else {
            $_SESSION['message'] = "Invalid file type. Only JPG, PNG, and GIF allowed. Vehicle registered without image.";
            $_SESSION['message_type'] = 'warning';
            // Continue execution without the image path
        }
    }


    // 4. Insert Vehicle Data into Database
    $stmt = $conn->prepare("INSERT INTO tbl_vehicle (userID, plateNumber, vehicleType, vehicleBrand, model, color, transmission, year, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    // Note: All values are treated as strings (s) except for userID and year (i)
    $stmt->bind_param("issssssis", 
        $userID, 
        $plateNumber, 
        $vehicleType, 
        $vehicleBrand, 
        $model, 
        $color, 
        $transmission, 
        $year,
        $imagePath
    );

    if ($stmt->execute()) {
        $_SESSION['message'] = "New vehicle '{$plateNumber}' registered successfully!";
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = "Error registering vehicle: " . $conn->error;
        $_SESSION['message_type'] = 'error';
    }

    $stmt->close();
    $conn->close();

    // 5. Redirect back to the vehicle management page
    header("Location: ../vehicle.php");
    exit();

} else {
    // If accessed directly without POST data
    $_SESSION['message'] = "Invalid request method.";
    $_SESSION['message_type'] = 'error';
    header("Location: ../vehicle.php");
    exit();
}
?>