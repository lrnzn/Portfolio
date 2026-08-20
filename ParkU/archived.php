<?php
require 'api/connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['user_id'];
$archivedReservations = [];
$now = new DateTime();

/* ===============================
   FETCH ARCHIVED RESERVATIONS
================================ */
$sql = "
    SELECT 
        r.reservationID,
        r.date,
        r.time,
        r.duration,
        r.status,
        r.totalFee,
        v.vehicleType,
        v.plateNumber,
        ps.slot_number,
        ps.parkingName
    FROM tbl_reservation r
    JOIN tbl_vehicle v ON r.vehicleID = v.vehicleID
    JOIN tbl_parking_slot ps ON r.slotID = ps.slotID
    WHERE 
        v.userID = ?
        AND r.archived = 1
    ORDER BY r.date DESC, r.time DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {

    $startTime = new DateTime($row['date'] . ' ' . $row['time']);
    $endTime   = clone $startTime;
    $endTime->modify('+' . (int)$row['duration'] . ' hours');

    $archivedReservations[] = [
        'reservationID' => $row['reservationID'],
        'slot'          => htmlspecialchars($row['slot_number']),
        'parkingName'   => htmlspecialchars($row['parkingName']),
        'date'          => $startTime->format('M d, Y'),
        'time'          => $startTime->format('g:i A') . ' - ' . $endTime->format('g:i A'),
        'vehicle'       => htmlspecialchars($row['vehicleType'] . ' (' . $row['plateNumber'] . ')'),
        'status'        => strtoupper($row['status'])
    ];
}

$stmt->close();
$conn->close();

$archivedCount = count($archivedReservations);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Archived Reservations</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header class="main-header">
    <a href="homepage.php" class="logo">PARK U</a>
    <nav class="main-nav">
        <a href="homepage.php">Home</a>
        <a href="profile.php">My Profile</a>
        <a href="vehicle.php">My Vehicle</a>
        <a href="reservations.php">My Reservations</a>
        <a href="archived.php" class="active-link">Archived</a>
        <a href="help.php">Help</a>
        <a href="login.php?action=logout">Sign Out</a>
    </nav>
</header>

<div class="app-container">

    <section class="welcome-panel">
        <h2>ARCHIVED RESERVATIONS</h2>
        <p>Previously archived parking reservations.</p>
    </section>

    <section class="reservation-panel">
        <h3>Archived (<?php echo $archivedCount; ?>)</h3>

        <?php if ($archivedCount > 0): ?>
            <?php foreach ($archivedReservations as $res): ?>
                <div class="reservation-card past-card">

                    <div class="card-status">
                        <span class="status-tag archived">ARCHIVED</span>
                    </div>

                    <div class="card-details">
                        <p class="slot-info">
                            Slot: <strong><?php echo $res['slot']; ?></strong>
                            (<?php echo $res['parkingName']; ?>)
                        </p>
                        <p class="datetime-info">
                            Date: <?php echo $res['date']; ?> | Time: <?php echo $res['time']; ?>
                        </p>
                        <p class="vehicle-info">
                            Vehicle: <?php echo $res['vehicle']; ?>
                        </p>
                    </div>

                    <div class="card-actions">
                        <button
                            class="action-button secondary-button"
                            onclick="restoreReservation(<?php echo $res['reservationID']; ?>)">
                            Restore
                        </button>
                    </div>

                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-reservations">
                You have no archived reservations.
            </div>
        <?php endif; ?>

    </section>
</div>

<script src= "js/reservation.js"></script>
<script src= "js/rnotification.js"></script>
</body>
</html>
