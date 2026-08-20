<?php

require 'api/connection.php';

/* ===============================
   AUTH CHECK
================================ */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['user_id'];

/* ===============================
   INPUTS (IMPORTANT)
================================ */

$vehicleID = $_GET['vehicleID'] ?? null;
$date      = $_GET['date'] ?? null;
$time      = $_GET['time'] ?? null;
$durationHours = (int)($_GET['duration'] ?? 1);
$editReservationID = $_GET['editReservationID'] ?? null;
$parkingName = $_GET['parkingName'] ?? null;
$originalSlotID = $_GET['originalSlotID'] ?? null;


if (!$vehicleID || !$date || !$time || $durationHours <= 0) {
    header("Location: reservations.php");
    exit();
}

/* ===============================
   CONSTANTS
================================ */


if (!$parkingName) {
    $_SESSION['error_message'] = "No parking area selected.";
    header("Location: liveMap.php");
    exit();
}

$hourlyRate  = 10.00;
$baseCost    = $hourlyRate * $durationHours;

/* ===============================
   VEHICLE VALIDATION
================================ */
$stmt = $conn->prepare("
    SELECT vehicleType, plateNumber
    FROM tbl_vehicle
    WHERE vehicleID = ? AND userID = ?
");
$stmt->bind_param("ii", $vehicleID, $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: reservations.php");
    exit();
}
$vehicle = $result->fetch_assoc();
$stmt->close();

/* ===============================
   LOAD ALL SLOTS
================================ */
$allSlots = [];
$stmt = $conn->prepare("
    SELECT slotID, slot_number, is_PWD_reserved
    FROM tbl_parking_slot
    WHERE parkingName = ?
    ORDER BY slot_number
");
$stmt->bind_param("s", $parkingName);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $allSlots[$row['slot_number']] = [
        'db_slotID' => $row['slotID'],
        'number'    => $row['slot_number'],
        'is_pwd'    => (bool)$row['is_PWD_reserved'],
        'status_class' => 'available'
    ];
}
$stmt->close();

/* ===============================
   FIND OCCUPIED SLOTS
   (EXCLUDE CURRENT RESERVATION)
================================ */
$occupiedSlotIDs = [];

$sql = "
    SELECT slotID
    FROM tbl_reservation
    WHERE status IN ('Reserved', 'Occupied')
        AND (
            TIMESTAMP(?, ?) <
            ADDTIME(
                TIMESTAMP(date, time),
                SEC_TO_TIME(duration * 3600)
            )
        AND
            ADDTIME(
                TIMESTAMP(?, ?),
                SEC_TO_TIME(? * 3600)
            ) >
            TIMESTAMP(date, time)
      )
";

if ($editReservationID) {
    $sql .= " AND reservationID != ?";
}

$stmt = $conn->prepare($sql);

if ($editReservationID) {
    $stmt->bind_param(
        "ssssii",
        $date, $time, $date, $time, $durationHours, $editReservationID
    );
} else {
    $stmt->bind_param(
        "ssssi",
        $date, $time, $date, $time, $durationHours
    );
}

$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $occupiedSlotIDs[] = $row['slotID'];
}



$previousSlotID = null;
$previousSlotNumber = null;

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


$conn->close();

/* ===============================
   APPLY SLOT STATES
================================ */
$availableCount = 0;
foreach ($allSlots as &$slot) {
    if (in_array($slot['db_slotID'], $occupiedSlotIDs)) {
        $slot['status_class'] = 'occupied';
    } else {
        $slot['status_class'] = $slot['is_pwd'] ? 'available-pwd' : 'available';
        $availableCount++;
    }
}
unset($slot);

/* ===============================
   GROUP BY ROW
================================ */
$slotsByRow = [];
foreach ($allSlots as $slot) {
    $rowKey = substr($slot['number'], 0, 1);
    $slotsByRow[$rowKey][] = $slot;
}
ksort($slotsByRow);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Parking Slot</title>
    <link rel="stylesheet" href="css/style.css"> 
</head>
<body>

    <header class="main-header">
        <a href="homepage.php" class="logo">PARK U</a>
        <nav class="main-nav">
            <a href="homepage.php" class="active-link">Home</a>
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
            <h2>SELECT YOUR SLOT</h2>
            <p>
        <?php echo htmlspecialchars($vehicle['vehicleType']); ?>
        (<?php echo htmlspecialchars($vehicle['plateNumber']); ?>) —
        <?php echo htmlspecialchars($date); ?> @ <?php echo htmlspecialchars($time); ?>
            </p>

            <?php if ($editReservationID && $previousSlotNumber): ?>
                <p class="info-text">
                    Previously selected slot: <strong><?php echo htmlspecialchars($previousSlotNumber); ?></strong>
                </p>
            <?php endif; ?>

        </section>

        <section class="reservation-panel"> 
            <h3><?php echo $parkingName; ?> - Available Slots (<?php echo $availableCount . '/' . count($allSlots); ?> Free)</h3>

            <div class="parking-grid">

                <div class="parking-legend">
                    <span class="legend-item"><span class="slot-box available"></span> Available</span>
                    <span class="legend-item"><span class="slot-box available-pwd">PWD</span> PWD Slot</span>
                    <span class="legend-item"><span class="slot-box occupied"></span> Occupied</span>
                    <span class="legend-item"><span class="slot-box selected"></span> Selected</span>
                </div>

                <!-- DYNAMICALLY RENDER SLOTS -->
                <?php foreach ($slotsByRow as $rowId => $slots): ?>
                    <div class="slot-row">
                        <div class="slot-id">Row <?php echo $rowId; ?></div>
                        <?php foreach ($slots as $slot): ?>
                            <?php 
                                $isDisabled = ($slot['status_class'] === 'occupied');
                            ?>

                            
                            <button class="slot-box <?php echo $slot['status_class'];?>" 

                                    data-slot-number="<?php echo $slot['number']; ?>"
                                    data-db-slot-id="<?php echo $slot['db_slotID']; ?>"
                                    <?php if ($isDisabled) echo 'disabled'; ?>>

                                <?php echo $slot['number']; ?>
                                <?php if ($slot['is_pwd'] && !$isDisabled) echo '<br><small style="font-size: 0.7em;">PWD</small>'; ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>

            </div>

            <div class="slot-details-summary">
                <p>Selected Slot: <strong id="selected-slot-display">None</strong></p>
                <p>Estimated Cost: <strong id="estimated-cost-display">₱<?php echo number_format($baseCost, 2); ?></strong> (<?php echo $durationHours; ?> Hour<?php echo ($durationHours > 1) ? 's' : ''; ?> @ ₱<?php echo number_format($hourlyRate, 2); ?>/hr)</p>
            </div>

            <div class="next-step-button">
                <a href="#" id="proceed-button" class="check-button-primary disabled" aria-disabled="true">
                    Proceed to Payment
                </a>
            </div>
        </section>
    </div>

    <script>
        window.APP_DATA = {
        vehicleID: <?php echo json_encode($vehicleID); ?>,
        date: <?php echo json_encode($date); ?>,
        time: <?php echo json_encode($time); ?>,
        durationHours: <?php echo json_encode($durationHours); ?>,
        baseCost: <?php echo json_encode($baseCost); ?>,
        parkingName: <?php echo json_encode($parkingName); ?>,
        editReservationID: <?php echo json_encode($editReservationID); ?>,
        originalSlotID: <?php echo json_encode($originalSlotID); ?>,

        previousSlotID: <?php echo json_encode($previousSlotID); ?>,
        previousSlotNumber: <?php echo json_encode($previousSlotNumber); ?>
        };
    </script>

    <script src="js/selectSlot.js"></script>

</body>
</html>