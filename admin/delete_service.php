<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

include('../config/database.php');

// Handle delete request
if (isset($_GET['id'])) {
    $service_id = intval($_GET['id']);
    
    // Delete the service from the database
    $stmt = $conn->prepare("DELETE FROM grooming_services WHERE service_id = ?");
    $stmt->bind_param("i", $service_id);
    if ($stmt->execute()) {
        header("Location: manage_grooming.php");
        exit();
    } else {
        $error = "Failed to delete service.";
    }
} else {
    header("Location: manage_grooming.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
    <title>Delete Service</title>
</head>
<body>
    <div class="admin-container">
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <p>Deleting service...</p>
    </div>
</body>
</html>
