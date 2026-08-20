<?php

require 'connection.php';

global $conn;

// 1. Check for authenticated user
if (!isset($_SESSION['user_id'])) {
    // Redirect to login if not authenticated
    $_SESSION['message'] = "You must be logged in to delete a vehicle.";
    $_SESSION['message_type'] = "error";
    header("Location: ../login.php");
    exit();
}

$userID = $_SESSION['user_id'];
$vehicleID = $_GET['vehicleID'] ?? null;

// 2. Validate Vehicle ID
if (!$vehicleID || !is_numeric($vehicleID)) {
    $_SESSION['message'] = "Error: Invalid vehicle ID provided.";
    $_SESSION['message_type'] = "error";
    header("Location: ../vehicle.php");
    exit();
}

// 3. Delete the vehicle, ensuring it belongs to the logged-in user
// This prevents one user from deleting another user's vehicles.
$stmt = $conn->prepare("DELETE FROM tbl_vehicle WHERE vehicleID = ? AND userID = ?");
$stmt->bind_param("ii", $vehicleID, $userID);

if ($stmt->execute()) {
    // Check how many rows were affected (should be 1 if successful)
    if ($stmt->affected_rows > 0) {
        $_SESSION['message'] = "Success! The vehicle has been successfully deleted.";
        $_SESSION['message_type'] = "success";
    } else {
        // If 0 rows were affected, either the ID didn't exist or it didn't belong to the user
        $_SESSION['message'] = "Error: Vehicle not found or you do not have permission to delete it.";
        $_SESSION['message_type'] = "error";
    }
} else {
    // Database execution error
    $_SESSION['message'] = "Database Error: Could not delete vehicle. " . $stmt->error;
    $_SESSION['message_type'] = "error";
}

$stmt->close();
$conn->close();

// 4. Redirect back to the vehicle management page
header("Location: ../vehicle.php");
exit();
?>