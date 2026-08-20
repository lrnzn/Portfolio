<?php

require 'admin_guard.php';

$result = $conn->query("
    SELECT v.vehicleID, v.vehicleType, v.plateNumber, v.transmission, v.dateAdded,
           u.fname, u.lname
    FROM tbl_vehicle v
    JOIN tbl_user u ON v.userID = u.userID
    ORDER BY v.vehicleID ASC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Vehicles</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<header class="main-header">
    <a class="logo">PARK U — ADMIN</a>
    <nav class="main-nav">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="users.php">Users</a>
        <a href="vehicles.php"  class="active-link">Vehicles</a>
        <a href="reservations.php">Reservations</a>
        <a href="../login.php?action=logout">Log Out</a>
    </nav>
</header>

<div class="admin-app-container">
<section class="reservation-panel admin-panel">
    <div class="panel-header">
        <h3>All Vehicles</h3>
        <span class="panel-subtext">
            Total: <?php echo $result->num_rows; ?> vehicles
        </span>
    </div>

<table class="data-table admin-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Owner</th>
            <th>Type</th>
            <th>Transmission</th>
            <th>Plate</th>
            <th>Date Added</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($v = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $v['vehicleID']; ?></td>
            <td><?php echo $v['fname'].' '.$v['lname']; ?></td>
            <td><?php echo $v['vehicleType']; ?></td>
            <td><?php echo $v['transmission']; ?></td>
            <td><?php echo $v['plateNumber']; ?></td>
            <td><?php echo $v['dateAdded']; ?></td>
        </tr>
    </tbody>
<?php endwhile; ?>

</table>
</section>
</div>

</body>
</html>
