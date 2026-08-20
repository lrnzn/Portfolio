<?php

require 'admin_guard.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reservationID'], $_POST['status'])) {
    $reservationID = (int) $_POST['reservationID'];
    $status = $_POST['status'];

    $allowedStatuses = ['Reserved', 'Completed', 'Cancelled'];

    if (in_array($status, $allowedStatuses)) {
        $stmt = $conn->prepare("
            UPDATE tbl_reservation
            SET status = ?
            WHERE reservationID = ?
        ");
        $stmt->bind_param("si", $status, $reservationID);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch all reservations
$result = $conn->query("
    SELECT 
        r.reservationID,
        r.date,
        r.time,
        r.duration,
        r.status,
        ps.slot_number,
        ps.parkingName,
        u.fname,
        u.lname
    FROM tbl_reservation r
    JOIN tbl_vehicle v ON r.vehicleID = v.vehicleID
    JOIN tbl_user u ON v.userID = u.userID
    JOIN tbl_parking_slot ps ON r.slotID = ps.slotID
    ORDER BY r.date DESC, r.time DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Reservations</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        </style>
</head>
<body>

<header class="main-header">
    <a class="logo">PARK U — ADMIN</a>
    <nav class="main-nav">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="users.php">Users</a>
        <a href="vehicles.php">Vehicles</a>
        <a href="reservations.php" class="active-link">Reservations</a>
        <a href="../login.php?action=logout">Log Out</a>
    </nav>
</header>

<div class="admin-app-container">
<section class="reservation-panel admin-panel">
    <div class="panel-header">
        <h3>All Reservations</h3>
        <span class="panel-subtext">
            Total: <?php echo $result->num_rows; ?> reservations
        </span>
    </div>

    <table class="data-table admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Slot</th>
                <th>Lot</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($r = $result->fetch_assoc()): ?>
            <tr>
                <td>#<?php echo $r['reservationID']; ?></td>
                <td><?php echo $r['fname'].' '.$r['lname']; ?></td>
                <td><?php echo $r['slot_number']; ?></td>
                <td><?php echo $r['parkingName']; ?></td>
                <td><?php echo date('M d, Y', strtotime($r['date'])); ?></td>
                <td><?php echo date('g:i A', strtotime($r['time'])); ?> (<?php echo $r['duration']; ?>h)</td>
                <td>
                    <form action="update_reservation_status.php" method="POST">
                        <input type="hidden" name="reservationID" value="<?php echo $r['reservationID']; ?>">

                        <select name="status"
                            class="status-select
                            <?php
                                echo match ($r['status']) {
                                    'Reserved'  => 'status-reserved',
                                    'Completed' => 'status-completed',
                                    'Cancelled' => 'status-cancelled',
                                    default     => ''
                                };
                            ?>">
                            <option value="Reserved"  <?php if ($r['status']=='Reserved')  echo 'selected'; ?>>Reserved</option>
                            <option value="Completed" <?php if ($r['status']=='Completed') echo 'selected'; ?>>Completed</option>
                            <option value="Cancelled" <?php if ($r['status']=='Cancelled') echo 'selected'; ?>>Cancelled</option>
                        </select>
                </td>
                <td>
                        <button class="update-btn" type="submit">Update</button>
                    </form>
                </td>

            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</section>

</div>

</body>
</html>
