<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

include('../config/database.php');

// Get the service ID from the URL
if (isset($_GET['id'])) {
    $service_id = intval($_GET['id']);
    
    // Fetch the service details from the database
    $stmt = $conn->prepare("SELECT * FROM grooming_services WHERE service_id = ?");
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    $service = $stmt->get_result()->fetch_assoc();
    
    if (!$service) {
        header("Location: manage_grooming.php");
        exit();
    }
}

// Handle update service request
if (isset($_POST['update_service'])) {
    $service_name = htmlspecialchars($_POST['service_name']);
    $description = htmlspecialchars($_POST['description']);
    $price = floatval($_POST['price']);
    $availability = intval($_POST['availability']);

    // Handle file upload (if a new image is provided)
    $service_image = $service['service_image']; // Keep the old image if no new image is uploaded
    if (isset($_FILES['service_image']) && $_FILES['service_image']['error'] == 0) {
        $image_tmp_name = $_FILES['service_image']['tmp_name'];
        $image_name = basename($_FILES['service_image']['name']);
        $target_dir = "../assets/images/";
        $target_file = $target_dir . $image_name;

        // Move uploaded file to the target directory
        if (move_uploaded_file($image_tmp_name, $target_file)) {
            $service_image = $image_name; // Update image name in the database
        } else {
            $error = "Failed to upload image.";
        }
    }

    // Update the service in the database
    $stmt = $conn->prepare("UPDATE grooming_services SET service_name = ?, description = ?, price = ?, availability = ?, service_image = ? WHERE service_id = ?");
    $stmt->bind_param("ssdisi", $service_name, $description, $price, $availability, $service_image, $service_id);
    if ($stmt->execute()) {
        $message = "Service updated successfully!";
    } else {
        $error = "Failed to update service.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/edit_service.css">
    <title>Edit Service</title>
</head>
<body>
<a href="manage_grooming.php" class="back-button">Back to Manage Services</a>

    <div class="admin-container">
        <h2>Edit Grooming Service</h2>
        <?php if (isset($message)) echo "<p class='success'>$message</p>"; ?>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

        <form action="edit_service.php?id=<?php echo $service['service_id']; ?>" method="POST" enctype="multipart/form-data">
            <label for="service_name">Service Name:</label>
            <input type="text" name="service_name" value="<?php echo htmlspecialchars($service['service_name']); ?>" required>

            <label for="description">Description:</label>
            <textarea name="description" required><?php echo htmlspecialchars($service['description']); ?></textarea>

            <label for="price">Price:</label>
            <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($service['price']); ?>" required>

            <label for="availability">Availability:</label>
            <select name="availability">
                <option value="1" <?php if ($service['availability'] == 1) echo "selected"; ?>>Available</option>
                <option value="0" <?php if ($service['availability'] == 0) echo "selected"; ?>>Unavailable</option>
            </select>

            <!-- Display current image and option to update -->
            <label for="service_image">Service Image (Current Image: <?php echo htmlspecialchars($service['service_image']); ?>):</label>
            <input type="file" name="service_image" accept="image/*">

            <input type="submit" name="update_service" value="Update Service">
        </form>
    </div>
</body>
</html>
