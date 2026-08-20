<?php
require 'admin_guard.php';

$result = $conn->query("
    SELECT userID, fname, lname, email, type, studentID, dateAdded
    FROM tbl_user
    ORDER BY dateAdded ASC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Users</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<header class="main-header">
    <a class="logo">PARK U — ADMIN</a>
    <nav class="main-nav">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="users.php"  class="active-link">Users</a>
        <a href="vehicles.php">Vehicles</a>
        <a href="reservations.php">Reservations</a>
        <a href="../login.php?action=logout">Log Out</a>
    </nav>
</header>

<div class="admin-app-container">
    <section class="reservation-panel admin-panel">
        <div class="panel-header">
            <h3>All Users</h3>
            <span class="panel-subtext">
                Total: <?php echo $result->num_rows; ?> users
            </span>
        </div>

        <table class="data-table admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Type</th>
                    <th>Student ID</th>
                    <th>Date Added</th>
                </tr>
            </thead>

            <tbody>
                <?php while ($u = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $u['userID']; ?></td>
                    <td><?php echo $u['fname'].' '.$u['lname']; ?></td>
                    <td><?php echo $u['email']; ?></td>
                    <td><?php echo $u['type']; ?></td>
                    <td><?php echo $u['studentID'] ?? '-'; ?></td>
                    <td><?php echo $u['dateAdded']; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </section>
</div>

</body>
</html>
