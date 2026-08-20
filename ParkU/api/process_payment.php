<?php

require 'connection.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// --- 1. Validate and Retrieve POST Data ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = "Invalid request method.";
    // Use '../' to jump up one directory to the root level
    header("Location: ../homepage.php");
    exit();
}

// Retrieve and sanitize data from the payment form
$vehicleID = filter_input(INPUT_POST, 'vehicleID', FILTER_VALIDATE_INT);
$date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING);
$time = filter_input(INPUT_POST, 'time', FILTER_SANITIZE_STRING);
$durationHours = filter_input(INPUT_POST, 'duration', FILTER_VALIDATE_INT);
$slotID = filter_input(INPUT_POST, 'slotID', FILTER_VALIDATE_INT);
$slotNumber = filter_input(INPUT_POST, 'slotNumber', FILTER_SANITIZE_STRING); 
$totalAmount = filter_input(INPUT_POST, 'totalAmount', FILTER_VALIDATE_FLOAT);
$paymentMethod = filter_input(INPUT_POST, 'paymentMethod', FILTER_SANITIZE_STRING);
$referenceNumber = filter_input(INPUT_POST, 'referenceNumber', FILTER_SANITIZE_STRING);
$parkingName = filter_input(INPUT_POST, 'parkingName', FILTER_SANITIZE_STRING);


/* validation type shi
if (!$vehicleID || !$date || !$time || $durationHours <= 0 || !$slotID || $totalAmount <= 0.00 || !$paymentMethod || !$referenceNumber) {
    $_SESSION['error_message'] = "Critical reservation data is missing or invalid. Please try again.";
    // Use '../' to jump up one directory to the root level
    header("Location: ../help.php");
    exit();
}
    */

// Prepare current date/time for reservation timestamps
$currentDate = date('Y-m-d');
$currentTime = date('H:i:s');
$reservationStatus = 'Reserved';

// --- 2. Start Transaction ---
// Assuming $conn is available from connection.php
if (!isset($conn)) {
    $_SESSION['error_message'] = "Database connection failed.";
    header("Location: ../homepage.php");
    exit();
}

$conn->begin_transaction();

try {

    $editReservationID = $_POST['editReservationID'] ?? null;
    
    if ($editReservationID) {

        // ✅ EDIT EXISTING RESERVATION
        $stmt = $conn->prepare("
            UPDATE tbl_reservation
            SET slotID = ?, date = ?, time = ?, duration = ?, totalFee = ?
            WHERE reservationID = ?
        ");
        $stmt->bind_param(
            "issidi",
            $slotID,
            $date,
            $time,
            $durationHours,
            $totalAmount,
            $editReservationID
        );
        $stmt->execute();
    
        $reservationID = $editReservationID; // IMPORTANT
    
    } else {
    
        // ✅ INSERT NEW RESERVATION
        $stmt = $conn->prepare("
            INSERT INTO tbl_reservation
            (vehicleID, slotID, date, time, duration, status, totalFee, dateReserved, timeReserved)
            VALUES (?, ?, ?, ?, ?, 'Reserved', ?, CURDATE(), CURTIME())
        ");
        $stmt->bind_param(
            "iissid",
            $vehicleID,
            $slotID,
            $date,
            $time,
            $durationHours,
            $totalAmount
        );
        $stmt->execute();
    
        $reservationID = $conn->insert_id;
    }
    


    $conn->commit();

    $_SESSION['reservation_details'] = [
        'reservationID' => $reservationID,
        'slotID'        => $slotID,
        'slotNumber'    => $slotNumber,
        'parkingName'   => $_POST['parkingName'] ?? 'Unknown Lot',
        'totalAmount'   => $totalAmount,
        'vehicleID'     => $vehicleID,
        'duration'      => $durationHours
    ];

    $conn->commit();
    
    header("Location: ../parkingPass.php?id=" . $reservationID);
    exit();

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['message'] = "Reservation update failed.";
    $_SESSION['message_type'] = "error";
    header("Location: ../reservations.php");
    exit();
}

?>