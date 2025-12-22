<?php 
session_start();

// Include database connection
require_once('../config/database.php');
include('../admin/admin_dashboard.php'); // Include the admin header

// Initialize variables
$search_query = "";

// Check if a search query is submitted
if (isset($_GET['search'])) {
    $search_query = trim($_GET['search']);
    $stmt = $conn->prepare("SELECT * FROM users WHERE username LIKE ?");
    $search_param = "%" . $search_query . "%";
    $stmt->bind_param("s", $search_param);
} else {
    // Fetch all users if no search query is submitted
    $stmt = $conn->prepare("SELECT * FROM users");
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/manage_users.css">
    <title>Manage Users</title>
</head>
<body>
<h2>Manage Users</h2>
    <div class="manage-users-container">
        

        <!-- Search Form -->
        <form action="manage_users.php" method="GET" class="search-form">
            <input type="text" name="search" placeholder="Search by Username" value="<?php echo htmlspecialchars($search_query); ?>">
            <button type="submit">Search</button>
            <a href="manage_users.php" class="reset-button">Reset</a>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Mobile</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($user = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['mobile']); ?></td>
                            <td>
                                <a href="delete_user.php?id=<?php echo $user['user_id']; ?>" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No users found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
