<?php

require 'api/connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

/* ==========================
   RECEIVE BOOKING DATA
========================== */

$vehicleID = $_GET['vehicleID'] ?? null;
$date      = $_GET['date'] ?? null;
$time      = $_GET['time'] ?? null;
$duration  = $_GET['duration'] ?? null;
$editReservationID = $_GET['editReservationID'] ?? null;
$originalSlotID = $_GET['originalSlotID'] ?? null;
$parkingName = $_GET['parkingName'] ?? null;


if (!$vehicleID || !$date || !$time || !$duration) {
    $_SESSION['message'] = "Missing booking information.";
    $_SESSION['message_type'] = "error";
    header("Location: homepage.php");
    exit();
}

$previousSlotID = null;
$previousSlotNumber = null;

$stmt = $conn->prepare("
    SELECT slotID, slot_number, is_PWD_reserved
    FROM tbl_parking_slot
    WHERE parkingName = ?
    ORDER BY slot_number
");
$stmt->bind_param("s", $parkingName);
$stmt->execute();
$result = $stmt->get_result();




if ($editReservationID) {
    $stmt = $conn->prepare("
        SELECT ps.slotID, ps.slot_number
        FROM tbl_reservation r
        JOIN tbl_parking_slot ps ON r.slotID = ps.slotID
        WHERE r.reservationID = ?
    ");
    $stmt->bind_param("i", $editReservationID);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        $previousSlotID = $row['slotID'];
        $previousSlotNumber = $row['slot_number'];
    }
    $stmt->close();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Live Parking Map</title>
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
        <a href="archived.php">Archived</a>
        <a href="help.php">Help</a>
        <a href="login.php?action=logout">Sign Out</a>
    </nav>
</header>

<div class="app-container">

    <section class="welcome-panel">
        <h2>LIVE PARKING MAP</h2>
        <p>Select a parking area to continue your reservation</p>


        <?php if ($editReservationID && $previousSlotNumber): ?>
                <p class="info-text">
                    Previously selected slot: <strong><?php echo htmlspecialchars($previousSlotNumber);?></strong>
                </p>
            <?php endif; ?>

    </section>

    <section class="reservation-panel">

        <div class="campus-map">
            <div class="map-container">

                <!-- MAIN GATE LOT -->
                <div class="building">
                    <div class="parking-lots">
                        <div class="lot available"
                            onclick="goToSlots('Main Gate Lot')">
                            <h4>Main Gate Lot</h4>
                            <p>Near Main Entrance</p>
                        </div>

                        <!-- GREEN BENCHES -->
                        <div class="lot available"
                            onclick="goToSlots('Green Benches')">
                            <h4>Green Benches</h4>
                            <p>Next to Canteen</p>
                        </div>
                    </div>
                </div>
                <div class="building">
                    <div class="parking-lots">
                        <!-- TECH BUILDING -->
                        <div class="lot available"
                            onclick="goToSlots('Tech Building Lot')">
                            <h4>Tech Building Lot</h4>
                            <p>Science & Tech Area</p>
                        </div>

                        <!-- LIBRARY -->
                        <div class="lot available"
                            onclick="goToSlots('Library Lot')">
                            <h4>Library Lot</h4>
                            <p>Quiet Zone</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>

</div>

<script>
function goToSlots(parkingName) {

    const params = new URLSearchParams({
        vehicleID: <?php echo json_encode($vehicleID); ?>,
        date: <?php echo json_encode($date); ?>,
        time: <?php echo json_encode($time); ?>,
        duration: <?php echo json_encode($duration); ?>,
        parkingName: parkingName
    });

    <?php if ($editReservationID): ?>
        params.set('editReservationID', <?php echo json_encode($editReservationID); ?>);
        params.set('originalSlotID', <?php echo json_encode($originalSlotID); ?>);
    <?php endif; ?>

    window.location.href = "selectSlot.php?" + params.toString();
}

</script>

</body>
</html>
