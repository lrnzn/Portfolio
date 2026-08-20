<?php 

require 'api/connection.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


$userID = $_SESSION['user_id'];
$details = null;
$message = '';

if (isset($_SESSION['reservation_details'])) {
    $details = $_SESSION['reservation_details'];
    $parkingName = $details['parkingName'];
}

// --- 1. Check for Reservation ID passed via URL (GET parameter) ---
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $message = "Error: No valid reservation ID provided.";
    // If no ID is provided, check if session has temporary booking data (for immediate post-booking view)
    if (isset($_SESSION['reservation_details'])) {
        $details = $_SESSION['reservation_details'];
        $reservationID = $details['reservationID'];
        // We will continue using the session data for this rare case, but the logic below is for existing passes.
    } else {
        $_SESSION['message'] = "Error: Invalid or missing Reservation ID.";
        header("Location: reservations.php");
        exit();
    }
} else {
    $reservationID = (int)$_GET['id'];
}

if (isset($conn) && $reservationID > 0) {
    // --- 2. Fetch ALL details securely from the database ---
    $sql = "
        SELECT
            r.reservationID, r.date, r.time AS startTime, r.duration, r.totalFee AS totalAmount,
            v.plateNumber AS plateNumber, v.vehicleType, v.vehicleID,
            ps.slot_number AS slotNumber, ps.parkingName
        FROM
            tbl_Reservation r
        JOIN
            tbl_Vehicle v ON r.vehicleID = v.vehicleID
        JOIN
            tbl_Parking_Slot ps ON r.slotID = ps.slotID
        WHERE
            r.reservationID = ? AND v.userID = ?"; // Crucial check: must belong to the logged-in user

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $reservationID, $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $details = $row;
    } else {
        // If the ID is in the URL but not found or doesn't belong to the user
        $message = "Error: Reservation #{$reservationID} not found or access denied.";
    }
    
$stmt->close();
} elseif (!$details) {
    // Only set error message if we didn't find details through session either
    $message = "Critical Error: Database connection failed or no details available.";
}

// --- Data Preparation and Formatting (Revised) ---
if ($details) {
    
    $parkingName = htmlspecialchars($details['parkingName']);
    $slotNumber = htmlspecialchars($details['slotNumber'] ?? 'N/A');
    $reservationID = htmlspecialchars($details['reservationID']);
    $duration = $details['duration'] ?? 1; // Default to 1 hour if not specified in DB
    $totalAmount = number_format($details['totalAmount'] ?? 0, 2); 
    $vehiclePlate = htmlspecialchars($details['plateNumber'] ?? 'N/A');
    $vehicleType = htmlspecialchars($details['vehicleType'] ?? 'N/A');

    // Calculate times based on date/time and duration from the DB
    try {
        $startTime = new DateTime($details['startTime'] ? $details['date'] . ' ' . $details['startTime'] : 'now');
        
        $endTime = clone $startTime;
        $endTime->modify('+' . $duration . ' hours');
        
        $durationText = $startTime->format('g:i A') . ' - ' . $endTime->format('g:i A') . " ({$duration} Hours)";
        $dateText = $startTime->format('l, M d, Y');
        
        // Reference Number (e.g., PARK-U-20251201-B1-123)
        $reference = 'PARK-U-' . $startTime->format('Ymd') . '-' . $slotNumber . '-' . $reservationID;

    } catch (Exception $e) {
        $message = "Error processing reservation times: " . $e->getMessage();
    }
    
    $vehicleDisplay = $vehicleType . ' (' . $vehiclePlate . ')';
    
    // Clear the session details if they were used for immediate viewing, 
    // but typically we rely on DB lookup now.
    if (isset($_SESSION['reservation_details'])) {
        unset($_SESSION['reservation_details']);
    }
}

if (isset($conn)) {
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <title>Parking Pass</title>
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

        <section class="welcome-panel">
            <h2>RESERVATION CONFIRMED!</h2>
            <p>Your parking pass is active. Please show this screen to security upon entry.</p>
        </section>

        <section class="reservation-panel parking-pass-panel">
            <h3>Active Parking Pass</h3>
            
            <div class="pass-card">
                
                <div class="slot-display">
                    <p class="slot-label">PARKING SLOT</p>
                    <p class="slot-id-large"><?php echo $slotNumber; ?></p>
                    <p class="lot-name"><?php echo htmlspecialchars($parkingName); ?></p>
                </div>
                
                <div class="qr-code-placeholder">
                    <div class="qr-box"></div> 
                    <p>Your Reservation ID: <br><?php echo $reservationID; ?></p>
                </div>

                <div class="pass-details-summary">
                    <p><strong>Vehicle:</strong> <?php echo $vehicleDisplay; ?></p>
                    <p><strong>Duration:</strong> <?php echo $durationText; ?></p>
                    <p><strong>Date:</strong> <?php echo $dateText; ?></p>
                    <p><strong>Total Paid:</strong> ₱<?php echo $totalAmount; ?></p>
                    <p><strong>Reference:</strong> <?php echo $reference; ?></p>
                </div>
                
                <p class="pass-footer"><?php echo $endTime->format('g:i A'); ?>.</p>

            </div>
            
            <div class="pass-actions">
                <a href="reservations.php" class="secondary-button">View All Reservations</a>
            </div>

        </section>
    </div>

</body>
</html>
