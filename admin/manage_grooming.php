<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

include('../config/database.php');
include('../admin/admin_dashboard.php'); // Include the admin header

// Handle update request for availability
if (isset($_POST['update_availability'])) {
    $service_id = $_POST['service_id'];
    $availability = $_POST['availability'];

    $stmt = $conn->prepare("UPDATE grooming_services SET availability = ? WHERE service_id = ?");
    $stmt->bind_param("ii", $availability, $service_id);
    if ($stmt->execute()) {
        $message = "Service availability updated!";
    } else {
        $error = "Failed to update availability.";
    }
}

// Fetch all grooming services from the database
$services = $conn->query("SELECT * FROM grooming_services");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/manage_grooming.css">
    <title>Manage Grooming Services</title>
</head>
<body>
    <div class="admin-container">
        <h2>Manage Grooming Services</h2>

        <!-- Button to Add New Service -->
        <a href="add_service.php" class="button">Add New Service</a> 

        <!-- Button to View Booked Services -->
        <a href="view_booked_services.php" class="button">View Bookings</a>

        <!-- Service Section (Ticket-style layout) -->
        <div class="service-section">
            <?php while ($service = $services->fetch_assoc()): ?>
                <div class="service-ticket">
                    <div class="service-image">
                        <!-- Display the service image -->
                        <?php if (!empty($service['service_image'])): ?>
                            <img src="../assets/images/<?php echo htmlspecialchars($service['service_image']); ?>" alt="Service Image">
                        <?php else: ?>
                            <p>No image available</p>
                        <?php endif; ?>
                    </div>
                    <div class="service-details">
                        <h3><?php echo htmlspecialchars($service['service_name']); ?></h3>
                        <p><?php echo htmlspecialchars($service['description']); ?></p>
                        <p>Price: $<?php echo htmlspecialchars($service['price']); ?></p>
                        <p>Availability: <?php echo $service['availability'] == 1 ? "Available" : "Unavailable"; ?></p>
                    </div>
                    <div class="action-buttons">
                        <!-- Edit Service Button -->
                        <a href="edit_service.php?id=<?php echo $service['service_id']; ?>" class="button">Edit</a>

                        <!-- Delete Service Button -->
                        <a href="delete_service.php?id=<?php echo $service['service_id']; ?>" class="button delete-button" onclick="return confirm('Are you sure you want to delete this service?')">Delete</a>

                        <!-- Update Availability Form -->
                        <form action="manage_grooming.php" method="POST" style="display:inline;">
                            <input type="hidden" name="service_id" value="<?php echo $service['service_id']; ?>">
                            <select name="availability" class="availability-select">
                                <option value="1" <?php if ($service['availability'] == 1) echo "selected"; ?>>Available</option>
                                <option value="0" <?php if ($service['availability'] == 0) echo "selected"; ?>>Unavailable</option>
                            </select>
                            <input type="submit" name="update_availability" value="Update Availability" class="availability-submit">
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>
