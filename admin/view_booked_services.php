<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

include('../config/database.php');

// Handle status update
if (isset($_POST['update_status'])) {
    $booking_id = intval($_POST['booking_id']);
    $new_status = htmlspecialchars($_POST['status']);

    // Fetch the user_id for the selected booking
    $stmt = $conn->prepare("SELECT user_id FROM grooming_bookings WHERE booking_id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($user_id);
    $stmt->fetch();

    // Check if user_id exists
    if ($user_id) {
        // Update the booking status
        $update_stmt = $conn->prepare("UPDATE grooming_bookings SET book_status = ? WHERE booking_id = ?");
        $update_stmt->bind_param("si", $new_status, $booking_id);

        if ($update_stmt->execute()) {
            // Create a message for the user
            $message_text = "Your grooming service has been $new_status.";
            $message_type = 'service_booking';
            $timestamp = date('Y-m-d H:i:s');

            // Insert the message into the messages table
            $insert_message = $conn->prepare("INSERT INTO messages (user_id, message, type, timestamp, related_id) VALUES (?, ?, ?, ?, ?)");
            $insert_message->bind_param("isssi", $user_id, $message_text, $message_type, $timestamp, $booking_id);

            if ($insert_message->execute()) {
                $message = "Booking status updated successfully, and message sent to the user.";
            } else {
                $error = "Failed to insert message.";
            }
        } else {
            $error = "Failed to update booking status.";
        }
    } else {
        $error = "User not found for the selected booking.";
    }
}

// Handle filters and search
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$sort_option = isset($_GET['sort']) ? $_GET['sort'] : 'most_recent';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Base query
$query = "
    SELECT 
        gb.booking_id,
        gb.booking_date,
        gb.booking_time,
        gb.book_status,
        u.username AS user_name,
        u.email AS user_email,
        u.mobile AS user_mobile,
        gs.service_name
    FROM 
        grooming_bookings gb
    JOIN 
        users u ON gb.user_id = u.user_id
    JOIN 
        grooming_services gs ON gb.service_id = gs.service_id
    WHERE 1=1
";

// Apply search filter
if (!empty($search_query)) {
    $query .= " AND gs.service_name LIKE '%" . $conn->real_escape_string($search_query) . "%'";
}

// Apply status filter
if (!empty($status_filter)) {
    $query .= " AND gb.book_status = '" . $conn->real_escape_string($status_filter) . "'";
}

// Apply sort option
if ($sort_option == 'closest_booking') {
    $query .= " ORDER BY gb.booking_date ASC, gb.booking_time ASC";
} elseif ($sort_option == 'most_recent') {
    $query .= " ORDER BY gb.booking_date DESC, gb.booking_time DESC";
}

// Execute query
$bookings = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/view_booked_services.css">
    <title>View Booked Services</title>
</head>
<body>
<a href="manage_grooming.php" class="back-button">Back to Manage Services</a>

    <div class="admin-container">
        <h2>View Booking</h2>
        <?php if (isset($message)) echo "<p class='success'>$message</p>"; ?>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

        <!-- Filters and Search -->
        <form method="GET" action="view_booked_services.php" class="filter-form">
            <label for="status">Filter by Status:</label>
            <select name="status" id="status">
                <option value="">All</option>
                <option value="pending" <?php if ($status_filter == 'pending') echo 'selected'; ?>>Pending</option>
                <option value="booked" <?php if ($status_filter == 'booked') echo 'selected'; ?>>Booked</option>
                <option value="rejected" <?php if ($status_filter == 'rejected') echo 'selected'; ?>>Rejected</option>
            </select>

            <label for="sort">Sort by:</label>
            <select name="sort" id="sort">
                <option value="most_recent" <?php if ($sort_option == 'most_recent') echo 'selected'; ?>>Most Recent</option>
                <option value="closest_booking" <?php if ($sort_option == 'closest_booking') echo 'selected'; ?>>Closest Booking</option>
            </select>

            <label for="search">Search by Service Name:</label>
            <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($search_query); ?>">

            <button type="submit">Apply Filters</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>User Name</th>
                    <th>Email</th>
                    <th>Mobile</th>
                    <th>Service Name</th>
                    <th>Booking Date</th>
                    <th>Booking Time</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($bookings->num_rows > 0): ?>
                    <?php while ($booking = $bookings->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($booking['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($booking['user_email']); ?></td>
                            <td><?php echo htmlspecialchars($booking['user_mobile']); ?></td>
                            <td><?php echo htmlspecialchars($booking['service_name']); ?></td>
                            <td><?php echo htmlspecialchars($booking['booking_date']); ?></td>
                            <td><?php echo htmlspecialchars($booking['booking_time']); ?></td>
                            <td><?php echo htmlspecialchars($booking['book_status']); ?></td>
                            <td>
                                <form action="view_booked_services.php" method="POST">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                    <select name="status">
                                        <option value="pending" <?php if ($booking['book_status'] == 'pending') echo "selected"; ?>>Pending</option>
                                        <option value="booked" <?php if ($booking['book_status'] == 'booked') echo "selected"; ?>>Booked</option>
                                        <option value="rejected" <?php if ($booking['book_status'] == 'rejected') echo "selected"; ?>>Rejected</option>
                                    </select>
                                    <input type="submit" name="update_status" value="Update Status">
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">No bookings found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
