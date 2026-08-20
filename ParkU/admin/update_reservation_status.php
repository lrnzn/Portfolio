<?php

require 'admin_guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: reservations.php");
    exit();
}

$reservationID = $_POST['reservationID'] ?? null;
$newStatus     = $_POST['status'] ?? null;

if (!$reservationID || !$newStatus) {
    header("Location: reservations.php");
    exit();
}

// If admin sets COMPLETED, mark it as manual
if ($newStatus === 'Completed') {
    $stmt = $conn->prepare("
        UPDATE tbl_reservation
        SET status = 'Completed',
            manually_completed = 1
        WHERE reservationID = ?
    ");
    $stmt->bind_param("i", $reservationID);
} else {
    // For other statuses (Cancelled, Reserved, etc.)
    $stmt = $conn->prepare("
        UPDATE tbl_reservation
        SET status = ?
        WHERE reservationID = ?
    ");
    $stmt->bind_param("si", $newStatus, $reservationID);
}

$stmt->execute();
$stmt->close();

header("Location: reservations.php");
exit();
