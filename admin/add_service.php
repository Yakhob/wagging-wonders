<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

include('../config/database.php');

// Handle add service request
if (isset($_POST['add_service'])) {
    $service_name = htmlspecialchars($_POST['service_name']);
    $description = htmlspecialchars($_POST['description']);
    $price = floatval($_POST['price']);
    $availability = intval($_POST['availability']);

    // Handle file upload
    $service_image = '';
    if (isset($_FILES['service_image']) && $_FILES['service_image']['error'] == 0) {
        $image_tmp_name = $_FILES['service_image']['tmp_name'];
        $image_name = basename($_FILES['service_image']['name']);
        $target_dir = "../assets/images/";
        $target_file = $target_dir . $image_name;

        // Move uploaded file to the target directory
        if (move_uploaded_file($image_tmp_name, $target_file)) {
            $service_image = $image_name; // Store image name in the database
        } else {
            $error = "Failed to upload image.";
        }
    }

    // Insert the new service into the database
    $stmt = $conn->prepare("INSERT INTO grooming_services (service_name, description, price, availability, service_image) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdis", $service_name, $description, $price, $availability, $service_image);
    if ($stmt->execute()) {
        $message = "Service added successfully!";
        // Redirect to the manage_grooming.php after adding the service
        header("Location: manage_grooming.php");
        exit();
    } else {
        $error = "Failed to add service.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/add_service.css">
    <title>Add New Service</title>
</head>
<body>
<a href="manage_grooming.php" class="back-button">Back to Manage Services</a>
    <div class="admin-container">
        <h2>Add New Grooming Service</h2>
        <?php if (isset($message)) echo "<p class='success'>$message</p>"; ?>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

        <form action="add_service.php" method="POST" enctype="multipart/form-data">
            <label for="service_name">Service Name:</label>
            <input type="text" name="service_name" required>

            <label for="description">Description:</label>
            <textarea name="description" required></textarea>

            <label for="price">Price:</label>
            <input type="number" step="0.01" name="price" required>

            <label for="availability">Availability:</label>
            <select name="availability">
                <option value="1">Available</option>
                <option value="0">Unavailable</option>
            </select>

            <label for="service_image">Service Image:</label>
            <input type="file" name="service_image" accept="image/*">

            <input type="submit" name="add_service" value="Add Service">
        </form>
    </div>
</body>
</html>
