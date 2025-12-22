<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

include('../config/database.php'); // Ensure correct relative path

// Handle add dog request
if (isset($_POST['add_dog'])) {
    $name = htmlspecialchars($_POST['name']);
    $breed = htmlspecialchars($_POST['breed']);
    $age = intval($_POST['age']);
    $price = floatval($_POST['price']);
    $description = htmlspecialchars($_POST['description']);
    $image = $_FILES['image']['name'];
    $target = "../assets/images/" . basename($image);

    // Image upload validation
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        $stmt = $conn->prepare("INSERT INTO dogs (name, breed, age, price, description, image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssdss", $name, $breed, $age, $price, $description, $image);
        if ($stmt->execute()) {
            $message = "Dog added successfully!";
        } else {
            $error = "Failed to add dog.";
        }
    } else {
        $error = "Failed to upload image.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Dog</title>
    <link rel="stylesheet" href="../assets/css/add_dog.css">
</head>
<body>
    <div class="admin-container">
    <a href="manage_dogs.php" class="back-button">Back to Manage Dogs</a>
        <h2>Add Dog</h2>
        <?php if (isset($message)) echo "<p class='success'>$message</p>"; ?>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

        <!-- Form to Add Dog -->
        <form action="add_dog.php" method="POST" enctype="multipart/form-data">
            <label for="name">Name:</label>
            <input type="text" name="name" required>

            <label for="breed">Breed:</label>
            <input type="text" name="breed" required>

            <label for="age">Age:</label>
            <input type="number" name="age" required>

            <label for="price">Price:</label>
            <input type="number" step="0.01" name="price" required>

            <label for="description">Description:</label>
            <textarea name="description" required></textarea>

            <label for="image">Image:</label>
            <input type="file" name="image" required>

            <input type="submit" name="add_dog" value="Add Dog">
        </form>
    </div>
</body>
</html>
