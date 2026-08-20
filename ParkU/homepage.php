<?php 

require 'api/connection.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['user_id'];
$username = htmlspecialchars($_SESSION['user_name'] ?? 'User');
$vehicles = [];

    $stmt = $conn->prepare("SELECT vehicleID, plateNumber, vehicleType, vehicleBrand, model FROM tbl_vehicle WHERE userID = ? ORDER BY vehicleID DESC");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $vehicles[] = $row;
        }
    }
    $stmt->close();

$conn->close();

// Set the default duration to 1 hour if not specified in the URL (for sticky form fields)
$selectedDuration = $_GET['duration'] ?? 1;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Homepage</title>
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
            <h2>WELCOME <?php echo strtoupper($username); ?></h2>
            <p>Reserve your parking spot in just a few clicks!</p>
        </section>

    <section class="reservation-panel"> 
        <h3>MAKE A NEW RESERVATION</h3>

        <form action="liveMap.php" method="GET" class="form-mockup">

        <div class="field-group">
            <label for="vehicle-select">Select Your Vehicle:</label>
                <div class="input-container">
                <select id="vehicle-select" name="vehicleID" required>
                    <option value="" disabled selected>Select a vehicle</option>

                        
                    <?php if (!empty($vehicles)): ?>
                        <?php foreach ($vehicles as $vehicle): ?>

                    <?php 
                    $display_name = htmlspecialchars($vehicle['vehicleType']) . 
                        " (" . htmlspecialchars($vehicle['plateNumber']) . ") - " . 
                        htmlspecialchars($vehicle['vehicleBrand']) . " " . 
                        htmlspecialchars($vehicle['model']);
                    ?>

                    <option value="<?php echo htmlspecialchars($vehicle['vehicleID']); ?>">
                        <?php echo $display_name; ?>
                    </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                    <option value="" disabled>No vehicles registered. Please add one in 'My Vehicle' in the navigation bar</option>

                    <?php endif; ?>
                </select>
                </div>
        </div>

                <div class="field-row">

                    <div class="field-group date-field">
                        <label for="reservation-date">Date:</label>
                        <div class="input-container">
                            <input type="date" id="reservation-date" name="date" required>
                        </div>
                    </div>

                    <div class="field-group time-field">
                        <label for="reservation-time">Time:</label>
                        <div class="input-container">
                            <input type="time" id="reservation-time" name="time" required>
                        </div>
                    </div>
                </div>
                
                <div class="field-row">
                    <div class="field-group duration-field">
                        <label for="duration">Duration (Hours):</label>
                        <div class="input-container">
                            <input type="number" id="duration" name="duration" required min="1" value="<?php echo htmlspecialchars($selectedDuration); ?>">
                        </div>
                    </div>
                    
                    <div class="field-group" style= "text-align: center;">
                        <label>&nbsp;</label>
                        <button class="check-button-primary" aria-label="Check Availability">Check Availability</button> 
                    </div>
                </div>
                    
        </form>
    </section>
    </div>

</body>
</html>