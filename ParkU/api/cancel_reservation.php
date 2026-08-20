<?php

require 'connection.php';

$message_type = '';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = "Error: You must be logged in to cancel a reservation.";
    header("Location: ../login.php");
    exit();
}

// Ensure a reservation ID is provided in the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['message'] = "Error: Invalid reservation ID provided.";
    header("Location: ../reservations.php");
    exit();
}

$reservationID = (int)$_GET['id'];
$userID = $_SESSION['user_id'];

if (isset($conn)) {
    // --- Step 1: Verify ownership and reservation status ---
    // We need to join with tbl_Vehicle to ensure this reservation belongs to the current user (via vehicle ownership).
    $checkSql = "
        SELECT 
            r.status 
        FROM 
            tbl_Reservation r
        JOIN 
            tbl_Vehicle v ON r.vehicleID = v.vehicleID
        WHERE 
            r.reservationID = ? AND v.userID = ?";
    
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("ii", $reservationID, $userID);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    $reservation = $result->fetch_assoc();
    $checkStmt->close();

    // Check if the reservation exists and belongs to the user
    if (!$reservation) {
        $_SESSION['message'] = "Error: Reservation not found or you are not authorized to cancel it.";
        $conn->close();
        $message_type = 'success';
        header("Location: ../reservations.php");
        exit();
    }
    
    // Check if the reservation is already cancelled
    if (strtolower($reservation['status']) === 'cancelled') {
        $_SESSION['message'] = "Notice: This reservation is already cancelled.";
        $conn->close();
        $message_type = 'error';
        header("Location: ../reservations.php");
        exit();
    }

    // --- Step 2: Update the reservation status to 'Cancelled' ---
    $updateSql = "
        UPDATE 
            tbl_Reservation 
        SET 
            status = 'Cancelled' 
        WHERE 
            reservationID = ?";
    
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("i", $reservationID);

    if ($updateStmt->execute()) {
        // Success message
        $_SESSION['message'] = "Success: Reservation #{$reservationID} has been successfully cancelled.";
        $_SESSION['message_type'] = 'success';
    } else {
        // Failure message
        $_SESSION['message'] = "Error: Failed to cancel reservation. Please try again or contact support. Error: " . $conn->error;
        $_SESSION['message_type'] = 'error';
    }

    $updateStmt->close();
    $conn->close();

} else {
    $_SESSION['message'] = "Critical Error: Database connection failed.";
}

// --- Step 3: Redirect back to the reservations page ---
header("Location: ../reservations.php");
exit();
?>