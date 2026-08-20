    <?php 

    require 'api/update_reservation_status.php';

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $userID = $_SESSION['user_id'];
    $upcomingReservations = [];
    $pastReservations = [];
    $now = new DateTime('now', new DateTimeZone('Asia/Manila')); // Current Unix timestamp

    if (isset($conn)) {
        // SQL query to fetch all reservations for the user, joined with vehicle details
        $sql = "
            SELECT 
                r.reservationID, 
                r.date, 
                r.time, 
                r.duration, 
                r.status AS reservationStatus, -- Status uses the field from tbl_Reservation
                r.totalFee,
                v.plateNumber,
                v.vehicleType,
                ps.slot_number,
                ps.parkingName
            FROM 
                tbl_reservation r
            JOIN 
                tbl_vehicle v ON r.vehicleID = v.vehicleID
            JOIN
                tbl_parking_slot ps ON r.slotID = ps.slotID
            WHERE 
                v.userID = ?
                AND r.archived = 0                    -- Securely filter reservations by the user's vehicle ownership
            ORDER BY 
                r.date DESC, r.time DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                
                // 3. Reconstruct StartTime and calculate EndTime using Duration
                $startTimeString = $row['date'] . ' ' . $row['time'];
                $durationHours = (int)$row['duration'];

                try {
                    $startTime = new DateTime($startTimeString);
                    $endTime = clone $startTime;
                    $endTime->modify('+' . $durationHours . ' hours');
                } catch (Exception $e) {
                    // Skip if date/time parsing fails
                    continue; 
                }
                
                // Format dates and times for display
                $formattedDate = $startTime->format('M d, Y');
                $formattedTime = $startTime->format('g:i A') . ' - ' . $endTime->format('g:i A');
                
                // Determine status for card display
                $status = strtoupper($row['reservationStatus']);
                
                // Check if the reservation end time is in the future AND is not already cancelled
                $nowTs = time();
                $endTs = $endTime->getTimestamp();

                if ($status === 'COMPLETED') {
                    $displayStatus = 'COMPLETED';
                    $pastReservations[] = [
                        'slot' => htmlspecialchars($row['slot_number']),
                        'parkingName' => htmlspecialchars($row['parkingName']),
                        'date' => $formattedDate,
                        'time' => $formattedTime,
                        'vehicle' => htmlspecialchars($row['vehicleType'] . ' (' . $row['plateNumber'] . ')'),
                        'status' => $displayStatus,
                        'reservationID' => $row['reservationID']
                    ];
                } elseif ($endTs > $nowTs && $status !== 'CANCELLED') {
                    $displayStatus = 'ACTIVE';
                    $upcomingReservations[] = [
                        'slot' => htmlspecialchars($row['slot_number']),
                        'parkingName' => htmlspecialchars($row['parkingName']),
                        'date' => $formattedDate,
                        'time' => $formattedTime,
                        'vehicle' => htmlspecialchars($row['vehicleType'] . ' (' . $row['plateNumber'] . ')'),
                        'status' => $displayStatus,
                        'reservationID' => $row['reservationID']
                    ];
                } else {
                    // Past or Cancelled reservations
                    $displayStatus = ($status === 'CANCELLED') ? 'CANCELLED' : 'COMPLETED';
                    
                    $pastReservations[] = [
                        'slot' => htmlspecialchars($row['slot_number']),
                        'parkingName' => htmlspecialchars($row['parkingName']),
                        'date' => $formattedDate,
                        'time' => $formattedTime,
                        'vehicle' => htmlspecialchars($row['vehicleType'] . ' (' . $row['plateNumber'] . ')'),
                        'status' => $displayStatus,
                        'reservationID' => $row['reservationID']
                    ];
                }
            }
        }
        $stmt->close();
        $conn->close();
    }

    $message = '';
    $message_type = '';
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $message_type = $_SESSION['message_type'] ?? 'info';
        unset($_SESSION['message']);
        
    }
    ?>


    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>My Reservations</title>
        <link rel="stylesheet" href="css/style.css">
    </head>
    <body>

    <?php 
        if ($message) {
        echo "<div id='status-notification' class='notification-box {$message_type}'>";
        echo $message ;
        echo "</div>";
    }
    ?>

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
                <h2>MY RESERVATIONS</h2>
                <p>View your past, present, and future parking bookings at CHMSU.</p>
            </section>

            

            <section class="reservation-panel"> 
                <h3>Upcoming Reservations (<?php echo count($upcomingReservations); ?>)</h3>
                
                <?php if (count($upcomingReservations) > 0): ?>
                    <?php foreach ($upcomingReservations as $reservation): ?>
                        <div class="reservation-card upcoming-card">
                            <div class="card-status">
                                <span class="status-tag active"><?php echo $reservation['status']; ?></span>
                            </div>
                            <div class="card-details">
                                <p class="slot-info">Slot: <strong><?php echo $reservation['slot']; ?></strong> (<?php echo $reservation['parkingName']; ?>) </p>
                                <p class="datetime-info">Date: <?php echo $reservation['date']; ?> | Time: <?php echo $reservation['time']; ?></p>
                                <p class="vehicle-info">Vehicle: <?php echo $reservation['vehicle']; ?></p>
                            </div>
                            <div class="card-actions">
                                
                                <button 
                                    class="check-button-primary action-button"
                                    onclick="confirmCancel(<?php echo $reservation['reservationID']; ?>)">
                                    Cancel
                                </button>

                                <button 
                                    class="action-button secondary-button"
                                    onclick="window.location.href='editReservation.php?id=<?php echo $reservation['reservationID']; ?>'">
                                    Edit
                                </button>

                                <button 
                                    class="action-button secondary-button"
                                    onclick="window.location.href='parkingPass.php?id=<?php echo $reservation['reservationID']; ?>'">
                                    View Pass
                                </button>
                                
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-reservations">You have no active or upcoming reservations. Book one now!</div>
                <?php endif; ?>

            </section>
            
            <section class="reservation-panel">
                <h3>Past Reservations (<?php echo count($pastReservations); ?>)</h3>
                
                <?php if (count($pastReservations) > 0): ?>
                    <?php foreach ($pastReservations as $reservation): 
                        $statusClass = strtolower($reservation['status']);
                        // Determine button actions based on past status
                        $button1Text = ($statusClass === 'completed') ? 'View Receipt' : 'View Summary';
                    ?>
                        <div class="reservation-card past-card">
                            <div class="card-status">
                                <span class="status-tag <?php echo $statusClass; ?>"><?php echo $reservation['status']; ?></span>
                            </div>
                            <div class="card-details">
                                <p class="slot-info">Slot: <strong><?php echo $reservation['slot']; ?></strong> (<?php echo $reservation['parkingName']; ?>)</p>
                                <p class="datetime-info">Date: <?php echo $reservation['date']; ?> | Time: <?php echo $reservation['time']; ?></p>
                                <p class="vehicle-info">Vehicle: <?php echo $reservation['vehicle']; ?></p>
                            </div>
                            <div class="card-actions">

                                <button class="action-button danger-button"
                                    onclick="confirmArchive(<?php echo $reservation['reservationID']; ?>)">
                                    Archive
                                </button>

                                <button class="action-button secondary-button"><?php echo $button1Text; ?></button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-reservations">No past reservation history found.</div>
                <?php endif; ?>
            </section>
        </div>

        <script src="js/notification.js"></script>
        <script src="js/reservation.js"></script>

    </body>

    </html>