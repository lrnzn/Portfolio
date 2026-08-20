<?php
require 'admin_guard.php';

// Total users
$totalUsers = $conn->query("
    SELECT COUNT(*) AS total 
    FROM tbl_user
")->fetch_assoc()['total'];

// Total vehicles
$totalVehicles = $conn->query("
    SELECT COUNT(*) AS total 
    FROM tbl_vehicle
")->fetch_assoc()['total'];

// Total reservations
$totalReservations = $conn->query("
    SELECT COUNT(*) AS total 
    FROM tbl_reservation
")->fetch_assoc()['total'];

// Active reservations
$activeReservations = $conn->query("
    SELECT COUNT(*) AS total 
    FROM tbl_reservation
    WHERE status IN ('Reserved', 'Occupied')
")->fetch_assoc()['total'];

// Cancelled reservations
$cancelledReservations = $conn->query("
    SELECT COUNT(*) AS total 
    FROM tbl_reservation
    WHERE status = 'Cancelled'
")->fetch_assoc()['total'];

// Archived reservations
$archivedReservations = $conn->query("
    SELECT COUNT(*) AS total 
    FROM tbl_reservation
    WHERE archived = 1
")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<header class="main-header">
    <a class="logo">PARK U — ADMIN</a>
    <nav class="main-nav">
        <a href="admin_dashboard.php" class="active-link">Dashboard</a>
        <a href="users.php">Users</a>
        <a href="vehicles.php">Vehicles</a>
        <a href="reservations.php">Reservations</a>
        <a href="../login.php?action=logout">Log Out</a>
    </nav>
</header>

<div class="admin-app-container">

<section class="welcome-panel">
    <h2>DASHBOARD</h2>
    <p>Overview of system usage and activity</p>
</section>

<section class="reservation-panel">

<div class="stats-grid">

    <div class="stat-card">
        <h3><?php echo $totalUsers; ?></h3>
        <p>Total Users</p>
    </div>

    <div class="stat-card">
        <h3><?php echo $totalVehicles; ?></h3>
        <p>Registered Vehicles</p>
    </div>

    <div class="stat-card">
        <h3><?php echo $totalReservations; ?></h3>
        <p>Total Reservations</p>
    </div>

    <div class="stat-card">
        <h3><?php echo $activeReservations; ?></h3>
        <p>Active Reservations</p>
    </div>

    <div class="stat-card">
        <h3><?php echo $cancelledReservations; ?></h3>
        <p>Cancelled</p>
    </div>

    <div class="stat-card">
        <h3><?php echo $archivedReservations; ?></h3>
        <p>Archived</p>
    </div>

</div>

</section>
</div>

</body>
</html>
