<?php

require '../api/connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$stmt = $conn->prepare("
    SELECT type 
    FROM tbl_user 
    WHERE userID = ?
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user || $user['type'] !== 'Admin') {
    header("Location: ../homepage.php");
    exit();
}