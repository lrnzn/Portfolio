<?php 

require 'api/connection.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$editReservationID = $_GET['editReservationID'] ?? null;
$parkingName = $_GET['parkingName'] ?? null;

$userID = $_SESSION['user_id'];
$username = htmlspecialchars($_SESSION['user_name'] ?? 'User');

$hourlyRate = 10.00;
$processingFee = 5.00; 

$vehicleID = $_GET['vehicleID'] ?? null;
$date = $_GET['date'] ?? null;
$time = $_GET['time'] ?? null;
$durationHours = (int)($_GET['duration'] ?? 0);
$slotID = (int)($_GET['slotID'] ?? 0);
$slotNumber = $_GET['slotNumber'] ?? 'N/A';
$baseCost = (float)($_GET['cost'] ?? 0.00); 

$totalAmount = $baseCost + $processingFee;

$vehicleDetails = ['type' => 'N/A', 'plate' => 'N/A'];
$stmt = $conn->prepare("SELECT vehicleType, plateNumber FROM tbl_vehicle WHERE vehicleID = ? AND userID = ?");
$stmt->bind_param("ii", $vehicleID, $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $v = $result->fetch_assoc();
    $vehicleDetails['type'] = htmlspecialchars($v['vehicleType']);
    $vehicleDetails['plate'] = htmlspecialchars($v['plateNumber']);
} else {
    $_SESSION['error_message'] = "Vehicle details could not be found.";
    header("Location: homepage.php");
    exit();
}
$stmt->close();

$reservationStartTime = new DateTime("$date $time");
$reservationEndTime = clone $reservationStartTime;
$reservationEndTime->modify("+$durationHours hours");

// Format date and time for display
$displayDate = $reservationStartTime->format('M d, Y');
$displayStartTime = $reservationStartTime->format('g:i A');
$displayEndTime = $reservationEndTime->format('g:i A');
$displayTimeRange = "{$displayStartTime} to {$displayEndTime}";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment</title>
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
            <h2>CONFIRM & PAY</h2>
            <p>Review your booking details and choose your payment method.</p>
        </section>

        <section class="reservation-panel"> 
            <h3>Booking Summary</h3>
            
            <div class="payment-summary">
                <h4>Reservation Details</h4>
                <p>Slot Location: <strong><?php echo htmlspecialchars($slotNumber); ?></strong> <?php echo htmlspecialchars($parkingName); ?></p>
                <p>Vehicle: <strong><?php echo $vehicleDetails['type'] . ' (' . $vehicleDetails['plate'] . ')'; ?></strong></p>
                <p>Date: <strong><?php echo $displayDate; ?></strong></p>
                <p>Time: <strong><?php echo $displayTimeRange; ?></strong> (<?php echo $durationHours; ?> Hour<?php echo ($durationHours > 1) ? 's' : ''; ?>)</p>
                
                <h4>Charges Breakdown</h4>
                <p>Parking Fee (<?php echo $durationHours; ?> hrs @ P<?php echo number_format($hourlyRate, 2); ?>/hr): <span><strong>P<?php echo number_format($baseCost, 2); ?></strong></span></p>
                <p>Reservation Processing Fee: <span><strong>P<?php echo number_format($processingFee, 2); ?></strong></span></p>
                <p class="total-amount">Total Amount Due: <span>P<?php echo number_format($totalAmount, 2); ?></span></p>
            </div>

            <form action="api/process_payment.php" method="POST">

                 <?php if ($editReservationID): ?>
                 <input type="hidden" name="editReservationID" value="<?php echo (int)$editReservationID; ?>">
                 <?php endif; ?>

                
                
                <!-- Hidden fields to pass booking data securely via POST -->
                <input type="hidden" name="vehicleID" value="<?php echo htmlspecialchars($vehicleID); ?>">
                <input type="hidden" name="date" value="<?php echo htmlspecialchars($date); ?>">
                <input type="hidden" name="time" value="<?php echo htmlspecialchars($time); ?>">
                <input type="hidden" name="duration" value="<?php echo htmlspecialchars($durationHours); ?>">
                <input type="hidden" name="slotID" value="<?php echo htmlspecialchars($slotID); ?>">
                <input type="hidden" name="slotNumber" value="<?php echo htmlspecialchars($slotNumber); ?>">
                <input type="hidden" name="cost" value="<?php echo htmlspecialchars($baseCost); ?>">
                <input type="hidden" name="totalAmount" value="<?php echo htmlspecialchars($totalAmount); ?>">
                <input type="hidden" name="parkingName" value="<?php echo htmlspecialchars($parkingName); ?>">

                

                <!-- payment method
                <h3>Select Payment Method</h3>

                <div class="payment-method-options">
                    
                    <label class="method-option" for="method-gcash">
                        <input type="radio" id="method-gcash" name="payment-method" value="gcash" required>
                        GCash (Mobile Wallet)
                    </label>
                    
                    <label class="method-option" for="method-card">
                        <input type="radio" id="method-card" name="payment-method" value="card">
                        Credit / Debit Card (Visa, Mastercard)
                    </label>

                    <label class="method-option" for="method-paymaya">
                        <input type="radio" id="method-paymaya" name="payment-method" value="paymaya">
                        PayMaya / Maya
                    </label>

                </div> -->

                <div class="field-group" style="margin-top: 30px;">
                    <label for="reference">Student ID / Reference Number</label>
                    <div class="input-container">
                        <input type="text" name="reference" id="reference" placeholder="Enter your Student ID or payment reference" required>
                    </div>
                </div>

                <button type="submit" class="check-button-primary login-button">Pay ₱<?php echo number_format($totalAmount, 2); ?></button>
            </form>
        </section>
    </div>

</body>
</html>
