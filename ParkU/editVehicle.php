<?php
require 'api/connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['user_id'];
$vehicleID = $_GET['vehicleID'] ?? null;

if (!$vehicleID || !is_numeric($vehicleID)) {
    header("Location: vehicle.php");
    exit();
}

// Fetch vehicle details (ownership check included)
$stmt = $conn->prepare("
    SELECT * FROM tbl_vehicle 
    WHERE vehicleID = ? AND userID = ?
");
$stmt->bind_param("ii", $vehicleID, $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: vehicle.php");
    exit();
}

$vehicle = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Vehicle</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

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

<section class="reservation-panel">
<h3>Edit Vehicle</h3>

<form action="api/edit_vehicle.php" method="POST" enctype="multipart/form-data">

    <input type="hidden" name="vehicle_id" value="<?php echo $vehicle['vehicleID']; ?>">
    <input type="hidden" name="user_id" value="<?php echo $userID; ?>">
    <input type="hidden" name="old_image_path" value="<?php echo htmlspecialchars($vehicle['image']); ?>">

        <div class="form-row">
    <div class="field-group">
        <label>Plate Number</label>
        <div class="input-container">
        <input type="text" name="plateNumber" value="<?php echo htmlspecialchars($vehicle['plateNumber']); ?>" required>
        </div>
    </div>

    <div class="field-group">
        <label>Vehicle Type</label>
        <div class="input-container">
        <select name="vehicleType" required>
            <?php
            $types = ['Sedan','SUV','Van','Motorcycle','Pickup'];
            foreach ($types as $type) {
                $selected = ($vehicle['vehicleType'] === $type) ? 'selected' : '';
                echo "<option value='$type' $selected>$type</option>";
            }
            ?>
        </select>
        </div>
    </div>
        </div>

    <div class="form-row">
    <div class="field-group">
        <label>Vehicle Brand</label>
        <div class="input-container">
        <input type="text" name="vehicleBrand" value="<?php echo htmlspecialchars($vehicle['vehicleBrand']); ?>" required>
        </div>
    </div>

    <div class="field-group">
        <label>Model</label>
        <div class="input-container">
        <input type="text" name="model" value="<?php echo htmlspecialchars($vehicle['model']); ?>" required>
        </div>
    </div>
        </div>

        <div class="form-row">
    <div class="field-group">
        <label>Transmission</label>
        <div class="input-container">
        <select name="transmission" required>
            <option value="Automatic" <?php if($vehicle['transmission']=='Automatic') echo 'selected'; ?>>Automatic</option>
            <option value="Manual" <?php if($vehicle['transmission']=='Manual') echo 'selected'; ?>>Manual</option>
        </select>
        </div>
    </div>

    <div class="field-group">
        <label>Year</label>
        <div class="input-container">
        <input type="number" name="year" value="<?php echo $vehicle['year']; ?>" required>
        </div>
    </div>
        </div>

        <div class="form-row">
    <div class="field-group">
        <label>Color</label>
        <div class="input-container">
        <input type="text" name="color" value="<?php echo htmlspecialchars($vehicle['color']); ?>">
        </div>
    </div>

    <div class="field-group">
        <label>Change Vehicle Image (optional)</label>
        <div class="input-container">
        <input type="file" name="vehicleImage" accept="image/*">
        </div>
    </div>
        </div>

    <div style="margin-top: 15px;">
    <button type="submit" class="check-button-primary">
        Save Changes
    </button>

    <button type="button"
        class="vehicle-action-button"
        onclick="window.location.href='vehicle.php'">
        Cancel
    </button>
</div>

</form>
</section>
</div>

</body>
</html>
