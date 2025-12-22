<?php
session_start();

// Include database connection
require_once('../config/database.php');

// Check if the `id` parameter is present in the URL
if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']); // Sanitize the user ID

    // Prepare and execute the DELETE statement
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        // Redirect back to manage_users.php with success message
        echo "<script>alert('User deleted successfully.'); window.location.href = 'manage_users.php';</script>";
    } else {
        // Display an error message if the delete fails
        echo "<script>alert('Error deleting user.'); window.location.href = 'manage_users.php';</script>";
    }

    $stmt->close();
} else {
    // Redirect back to manage_users.php if no user ID is provided
    header("Location: manage_users.php");
    exit();
}

// Close the database connection
$conn->close();
?>
