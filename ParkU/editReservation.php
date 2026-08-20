<?php

require 'api/connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['user_id'];
$reservationID = $_GET['id'] ?? null;

if (!$reservationID || !is_numeric($reservationID)) {
    header("Location: reservations.php");
    exit();
}


// Fetch reservation + ownership check
$stmt = $conn->prepare("
    SELECT r.*, v.vehicleID
    FROM tbl_reservation r
    JOIN tbl_vehicle v ON r.vehicleID = v.vehicleID
    WHERE r.reservationID = ? AND v.userID = ? AND r.status = 'Reserved'
");
$stmt->bind_param("ii", $reservationID, $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $stmt->close();
    header("Location: reservations.php");
    exit();
}

$reservation = $result->fetch_assoc();
$stmt->close();
$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Reservation</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header class="main-header">
        <a href="homepage.php" class="logo">PARK U</a>
        <nav class="main-nav">
        <a href="homepage.php">Home</a>
            <a href="profile.php">My Profile</a>
            <a href="vehicle.php">My Vehicle</a>
            <a href="reservations.php" class="active-link">My Reservations</a>
            <a href="archived.php">Archived</a>
            <a href="help.php">Help</a>
            <a href="login.php?action=logout">Sign Out</a>
        </nav>
</header>

<div class="app-container">

<section class="reservation-panel">
    <h3>Edit Reservation</h3>

    <form action="liveMap.php" method="GET">
        
        <input type="hidden" name="editReservationID" value="<?php echo (int)$reservationID; ?>">
        <input type="hidden" name="vehicleID" value="<?php echo (int)$reservation['vehicleID']; ?>">
        <input type="hidden" name="originalSlotID" value="<?php echo (int)$reservation['slotID']; ?>">

        
            <div class="input-container">
        <div class="field-row">
            <div class="field-group">
                <label>Date</label>
                <input type="date" name="date" required value="<?php echo $reservation['date']; ?>">
            </div>

            <div class="field-group">
                <label>Time</label>
                <input type="time" name="time" required value="<?php echo $reservation['time']; ?>">
            </div>
        </div>
            </div>

        <div class="field-group">
            <div class="input-container">
            <label>Duration (hours)</label>
            <input type="number" name="duration" min="1" required value="<?php echo $reservation['duration']; ?>">
            </div>
        </div>

        <div class="field-row">
            <button type="submit" class="check-button-primary">
                Continue
            </button>

            <button type="button"
                    class="action-button secondary-button"
                    onclick="window.location.href='reservations.php'">
                Cancel
            </button>
        </div>

    </form>
</section>

</div>
</body>
</html>
