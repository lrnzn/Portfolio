<?php

require 'connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$userID = $_SESSION['user_id'];
$reservationID = $_GET['id'] ?? null;

if (!$reservationID || !is_numeric($reservationID)) {
    $_SESSION['message'] = "Invalid reservation.";
    $_SESSION['message_type'] = "error";
    header("Location: ../reservations.php");
    exit();
}

/*
  Archive ONLY if:
  - reservation belongs to user
  - reservation is already completed/cancelled
*/
$stmt = $conn->prepare("
    UPDATE tbl_reservation r
    JOIN tbl_vehicle v ON r.vehicleID = v.vehicleID
    SET r.archived = 1
    WHERE r.reservationID = ?
      AND v.userID = ?
      AND r.status IN ('RESERVED', 'CANCELLED')
");

$stmt->bind_param("ii", $reservationID, $userID);
$stmt->execute();

$_SESSION['message'] = "Reservation archived.";
$_SESSION['message_type'] = "success";

header("Location: ../reservations.php");
exit();
