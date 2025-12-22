<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

include('../config/database.php'); // Ensure correct relative path

// Fetch the dog details to edit
if (isset($_GET['id'])) {
    $dog_id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM dogs WHERE dog_id = ?");
    $stmt->bind_param("i", $dog_id);
    $stmt->execute();
    $dog = $stmt->get_result()->fetch_assoc();

    if (!$dog) {
        header("Location: manage_dogs.php");
        exit();
    }
} else {
    header("Location: manage_dogs.php");
    exit();
}

// Handle the form submission to update dog details
if (isset($_POST['update_dog'])) {
    $name = htmlspecialchars($_POST['name']);
    $breed = htmlspecialchars($_POST['breed']);
    $age = intval($_POST['age']);
    $price = floatval($_POST['price']);
    $description = htmlspecialchars($_POST['description']);
    $image = $_FILES['image']['name'];
    $target = "../assets/images/" . basename($image);

    // Check if image is uploaded and move it to the target directory
    if ($image) {
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            // Update dog details with new image
            $stmt = $conn->prepare("UPDATE dogs SET name = ?, breed = ?, age = ?, price = ?, description = ?, image = ? WHERE dog_id = ?");
            $stmt->bind_param("sssdssi", $name, $breed, $age, $price, $description, $image, $dog_id);
        } else {
            $error = "Failed to upload image.";
        }
    } else {
        // Update dog details without changing the image
        $stmt = $conn->prepare("UPDATE dogs SET name = ?, breed = ?, age = ?, price = ?, description = ? WHERE dog_id = ?");
        $stmt->bind_param("sssdsi", $name, $breed, $age, $price, $description, $dog_id);
    }

    if ($stmt->execute()) {
        $message = "Dog details updated successfully!";
    } else {
        $error = "Failed to update dog details.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Dog</title>
    <link rel="stylesheet" href="../assets/css/edit_dog.css">
</head>
<body>
<a href="manage_dogs.php" class="back-button">Back to Manage Dogs</a>

    <div class="admin-container">
        <h2>Edit Dog</h2>
        <?php if (isset($message)) echo "<p class='success'>$message</p>"; ?>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

        <!-- Form to Edit Dog -->
        <form action="edit_dog.php?id=<?php echo $dog['dog_id']; ?>" method="POST" enctype="multipart/form-data">
            <label for="name">Name:</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($dog['name']); ?>" required>

            <label for="breed">Breed:</label>
            <input type="text" name="breed" value="<?php echo htmlspecialchars($dog['breed']); ?>" required>

            <label for="age">Age:</label>
            <input type="number" name="age" value="<?php echo htmlspecialchars($dog['age']); ?>" required>

            <label for="price">Price:</label>
            <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($dog['price']); ?>" required>

            <label for="description">Description:</label>
            <textarea name="description" required><?php echo htmlspecialchars($dog['description']); ?></textarea>

            <label for="image">Image (Leave blank to keep current image):</label>
            <input type="file" name="image">

            <input type="submit" name="update_dog" value="Update Dog">
        </form>

    </div>
</body>
</html>
