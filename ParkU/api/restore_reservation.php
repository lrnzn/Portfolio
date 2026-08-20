<?php
require 'connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$userID = $_SESSION['user_id'];
$reservationID = $_GET['id'] ?? null;

if (!$reservationID || !is_numeric($reservationID)) {
    header("Location: ../archived.php");
    exit();
}

$stmt = $conn->prepare("
    UPDATE tbl_reservation r
    JOIN tbl_vehicle v ON r.vehicleID = v.vehicleID
    SET r.archived = 0
    WHERE r.reservationID = ? AND v.userID = ?
");
$stmt->bind_param("ii", $reservationID, $userID);
$stmt->execute();

$stmt->close();
$conn->close();

header("Location: ../archived.php");
exit();
