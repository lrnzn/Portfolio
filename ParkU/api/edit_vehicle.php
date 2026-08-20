<?php

require 'connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Sanitize and Validate Critical Input
    $vehicleID = $_POST['vehicle_id'] ?? null; // The ID of the vehicle being updated
    $userID = $_POST['user_id'] ?? null;
    $plateNumber = trim($_POST['plateNumber'] ?? '');
    $vehicleType = $_POST['vehicleType'] ?? null;
    $vehicleBrand = $_POST['vehicleBrand'] ?? null;
    $model = $_POST['model'] ?? null;
    $transmission = $_POST['transmission'] ?? null;
    $year = $_POST['year'] ?? null;
    $color = $_POST['color'] ?? null;
    $oldImagePath = $_POST['old_image_path'] ?? null; // Existing image path passed from the form

    // Normalize color and old image path if empty
    if ($color === '') { $color = null; }
    if ($oldImagePath === '') { $oldImagePath = null; }
    
    // Set initial image path to the existing one
    $imagePath = $oldImagePath; 
    
    // Basic validation
    if (!$vehicleID || !$userID || empty($plateNumber) || !$vehicleType || !$vehicleBrand || !$model || !$transmission || !$year) {
        $_SESSION['message'] = "Missing required information to update the vehicle.";
        $_SESSION['message_type'] = 'error';
        header("Location: ../vehicle.php");
        exit();
    }

    // --- 2. GLOBAL DUPLICATE PLATE NUMBER CHECK (EXCLUDING CURRENT VEHICLE) ---
    // Check if the plate number already exists for ANY other vehicle (i.e., vehicleID is NOT the current one)
    $check_stmt = $conn->prepare("SELECT vehicleID FROM tbl_vehicle WHERE plateNumber = ? AND vehicleID != ?");
    $check_stmt->bind_param("si", $plateNumber, $vehicleID);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $_SESSION['message'] = "The plate number '{$plateNumber}' is already registered by another vehicle (possibly another user). Please use a unique number.";
        $_SESSION['message_type'] = 'error';
        $check_stmt->close();
        $conn->close();
        header("Location: ../vehicle.php");
        exit();
    }
    $check_stmt->close();


    // --- 3. Handle Vehicle Image Upload/Replacement ---
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
                // New image uploaded successfully. Save the relative path.
                $imagePath = 'img/vehicle_images/' . $newFileName;
                
                // CRITICAL: Delete the old image file if it exists and is not the default image
                if ($oldImagePath && file_exists('../' . $oldImagePath) && $oldImagePath !== 'img/logo.png') {
                    unlink('../' . $oldImagePath);
                }
            } else {
                $_SESSION['message'] = "File upload failed. Vehicle details updated without changing the image.";
                $_SESSION['message_type'] = 'warning';
                // Keep the existing $imagePath (which defaults to $oldImagePath)
            }
        } else {
            $_SESSION['message'] = "Invalid file type. Only JPG, PNG, and GIF allowed. Vehicle details updated without changing the image.";
            $_SESSION['message_type'] = 'warning';
            // Keep the existing $imagePath (which defaults to $oldImagePath)
        }
    } 
    // If no file was uploaded, $imagePath retains $oldImagePath, which is correct for keeping the existing image.


    // --- 4. Update Vehicle Data in Database ---
    $stmt = $conn->prepare("UPDATE tbl_vehicle SET 
        plateNumber = ?, 
        vehicleType = ?, 
        vehicleBrand = ?, 
        model = ?, 
        color = ?, 
        transmission = ?, 
        year = ?, 
        image = ? 
        WHERE vehicleID = ? AND userID = ?");

    // Bind parameters: ssssssisii (10 parameters)
    // s: plateNumber, vehicleType, vehicleBrand, model, color, transmission, imagePath
    // i: year, vehicleID, userID
    $stmt->bind_param("ssssssisii", 
        $plateNumber, 
        $vehicleType, 
        $vehicleBrand, 
        $model, 
        $color, 
        $transmission, 
        $year,
        $imagePath, // New or old image path
        $vehicleID,
        $userID
    );

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
             $_SESSION['message'] = "Vehicle '{$plateNumber}' updated successfully!";
             $_SESSION['message_type'] = 'success';
        } else {
            // No rows affected might mean no change was made, or the vehicleID/userID combination failed.
             $_SESSION['message'] = "Vehicle details submitted, but no changes were detected or the vehicle could not be found under your account.";
             $_SESSION['message_type'] = 'info';
        }
    } else {
        $_SESSION['message'] = "Error updating vehicle: " . $conn->error;
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