<?php 

require 'api/connection.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['user_id'];
$message = '';
$message_type = '';

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'] ?? 'info';
    // Clear the session messages after reading them
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// --- 2. FETCH EXISTING VEHICLES ---
$vehicles = [];

    $stmt = $conn->prepare("SELECT vehicleID, plateNumber, vehicleBrand, model, color, vehicleType, transmission, year, image FROM tbl_vehicle WHERE userID = ? ORDER BY vehicleID DESC");
    $stmt->bind_param("i", $userID); 
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $vehicles[] = $row;
        }
    }
    $stmt->close();   


/**
 * Helper function to clean up vehicle type for display
 */
function displayVehicleType($type) {
    // The vehicleType column uses values like 'Sedan', 'SUV/Van', 'Motorcycle' from your ENUM
    return str_replace(['_'], [' '], $type);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Vehicle</title>
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
            <a href="vehicle.php" class="active-link">My Vehicle</a>
            <a href="reservations.php">My Reservations</a>
            <a href="archived.php">Archived</a>
            <a href="help.php">Help</a>
            <a href="login.php?action=logout">Sign Out</a>
        </nav>
    </header>

    <div class="app-container">

        <section class="welcome-panel">
            <h2>MY VEHICLE</h2>
            <p>Manage your registered vehicles for quick reservation.</p>
        </section>

        <div class="vehicle-content-grid">

        <section class="reservation-panel vehicles-list"> 
            <h3>Registered Vehicles (<?php echo count($vehicles); ?>)</h3>

            <?php if (empty($vehicles)): ?>
                    <div class="text-center p-8 bg-gray-50 rounded-lg">
                        <p class="text-gray-500">You currently have no vehicles registered. Please use the form to add your first vehicle.</p>
                    </div>
            <?php else: ?>

                <?php foreach ($vehicles as $vehicle): ?>
                        <div class="vehicle-card">
                            <div class="vehicle-info-wrapper">
                            <!-- Vehicle Image Display -->
                                <?php 
                                $imagePath = $vehicle['image'] ?? null;
                                $imageUrl = $imagePath ? htmlspecialchars($imagePath) : 'img/logo.png';
                                ?>
                                <img src="<?php echo $imageUrl; ?>" 
                                     alt="Vehicle Image" 
                                     class="vehicle-image"
                                     onerror="this.onerror=null; this.src='<?php echo 'img/logo.png'; ?>';">
                                     
                            <div class="vehicle-info">
                                <p class="license-plate"><?php echo htmlspecialchars($vehicle['plateNumber']); ?></p>
                                <p class="description">
                                    <?php echo htmlspecialchars($vehicle['color'] ?? 'N/A'); ?> 
                                    <?php echo htmlspecialchars(displayVehicleType($vehicle['vehicleType'])); ?> 
                                    | <?php echo htmlspecialchars($vehicle['vehicleBrand'] ?? 'Unknown'); ?> 
                                    <?php echo htmlspecialchars($vehicle['model'] ?? 'Model'); ?>
                                </p>
                                <small class="text-gray-500">
                                    <?php echo htmlspecialchars($vehicle['transmission'] ?? 'N/A'); ?> (<?php echo htmlspecialchars($vehicle['year'] ?? 'N/A'); ?>)
                                </small>
                            </div>
                        </div>
                            <div class="vehicle-actions">
                                <!-- Using GET request to trigger delete action -->
                                <button class="vehicle-action-button edit-button"
                                        onclick="window.location.href='editVehicle.php?vehicleID=<?php echo $vehicle['vehicleID']; ?>'">Edit</button>
                                
                                <button class="vehicle-action-button delete-button"
                                        onclick="if(confirm('Are you sure you want to delete this vehicle? This action cannot be undone.')) window.location.href='api/delete_vehicle.php?vehicleID=<?php echo $vehicle['vehicleID']; ?>'">Delete</button>
                            </div>
                        </div>
                    <?php endforeach; ?>

            <?php endif; ?>
        
        </section>
        
        <section class="reservation-panel add-vehicle-form">
            <h3>Add New Vehicle</h3>
                
            <!-- Added enctype="multipart/form-data" for file uploads -->
            <form action="api/add_vehicle.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($userID); ?>">

            <div class="field-row">
                        <div class="field-group">
                            <label for="plateNumber">Plate Number</label>
                            <div class="input-container">
                            <input type="text" id="plateNumber" name="plateNumber" placeholder="e.g., ABC-1234" required>
                        </div>
            </div>

                        <div class="field-group">
                            <label for="vehicleType">Vehicle Type</label>
                            <div class="input-container">
                            <select id="vehicleType" name="vehicleType" required>
                            <option value="" disabled selected>Select Type</option>
                                    <option value="Sedan">Sedan</option>
                                    <option value="SUV">SUV</option>
                                    <option value="Van">Van</option>
                                    <option value="Motorcycle">Motorcycle</option>
                                    <option value="Pickup">Pickup</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="field-row">
                        <div class="field-group">
                            <label for="vehicleBrand">Vehicle Brand</label>
                            <div class="input-container">
                                <input type="text" id="vehicleBrand" name="vehicleBrand" placeholder="e.g., Toyota, Honda" required>
                            </div>
                        </div>
                        <div class="field-group">
                            <label for="model">Vehicle Model</label>
                            <div class="input-container">
                                <input type="text" id="model" name="model" placeholder="e.g., Fortuner, Civic" required>
                            </div>
                        </div>
                    </div>

                    <div class="field-row">
                        <div class="field-group">
                            <label for="transmission">Transmission</label>
                            <div class="input-container">
                                <select id="transmission" name="transmission" required>
                                    <option value="" disabled selected>Select Transmission</option>
                                    <option value="Automatic">Automatic</option>
                                    <option value="Manual">Manual</option>
                                </select>
                            </div>
                        </div>
                        <div class="field-group">
                            <label for="year">Year</label>
                            <div class="input-container">
                                <input type="number" id="year" name="year" placeholder="e.g., 2020" min="1900" max="<?php echo date('Y'); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="field-group">
                        <label for="color">Color</label>
                        <div class="input-container">
                            <input type="text" id="color" name="color" placeholder="e.g., Black, Red">
                        </div>
                    </div>

                    <div class="field-group">
                        <label for="vehicleImage">Vehicle Image (Optional)</label>
                        <div class="input-container">
                            <input type="file" id="vehicleImage" name="vehicleImage" accept="image/*">
                        </div>
                        <small class="hint-text">Max 5MB. JPG, PNG, GIF formats allowed.</small>
                    </div>

                    <button type="submit" class="check-button-primary">Register Vehicle</button>
                </form>
            </section>
        </div>
    </div>

<script src="js/notification.js"></script>
<script src="js/vehicle.js"></script>

</body>
</html>