<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

include('../config/database.php'); // Ensure correct relative path
include('../admin/admin_dashboard.php'); // Include the admin header

// Handle delete dog request
if (isset($_GET['delete_id'])) {
    $dog_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM dogs WHERE dog_id = ?");
    $stmt->bind_param("i", $dog_id);
    if ($stmt->execute()) {
        $message = "Dog deleted successfully!";
    } else {
        $error = "Failed to delete dog.";
    }
}

// Initialize search and filter variables
$search_breed = '';
$filter_status = '';
$dogs_query = "
    SELECT d.*, u.username AS buyer_name, u.mobile AS buyer_phone, u.email AS buyer_email, u.address AS buyer_address
    FROM dogs d
    LEFT JOIN purchase_dog pd ON d.dog_id = pd.dog_id
    LEFT JOIN users u ON pd.user_id = u.user_id
    WHERE 1=1
";

// Handle search and filter inputs
if (isset($_GET['search_breed']) && $_GET['search_breed'] !== '') {
    $search_breed = htmlspecialchars($_GET['search_breed']);
    $dogs_query .= " AND d.breed LIKE ?";
}
if (isset($_GET['filter_status']) && $_GET['filter_status'] !== '') {
    $filter_status = htmlspecialchars($_GET['filter_status']);
    if ($filter_status === 'sold') {
        $dogs_query .= " AND d.is_available = 1";
    } elseif ($filter_status === 'unsold') {
        $dogs_query .= " AND d.is_available = 0";
    }
}

$stmt = $conn->prepare($dogs_query);

// Bind parameters for search
if ($search_breed !== '') {
    $search_param = "%$search_breed%";
    $stmt->bind_param("s", $search_param);
}

$stmt->execute();
$dogs = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Dogs</title>
    <link rel="stylesheet" href="../assets/css/manage_dogs.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Manage Dogs</h2>
            <a href="add_dog.php" class="add-dog-button">Add Dog</a>
        </div>

        <!-- Search Form -->
        <form class="search-form" method="GET" action="manage_dogs.php">
            <input type="text" name="search_breed" value="<?php echo htmlspecialchars($search_breed); ?>" placeholder="Search dog by breed">
            
            <!-- Filter Dropdown -->
            <select name="filter_status">
                <option value="" <?php echo $filter_status === '' ? 'selected' : ''; ?>>All</option>
                <option value="sold" <?php echo $filter_status === 'sold' ? 'selected' : ''; ?>>Sold</option>
                <option value="unsold" <?php echo $filter_status === 'unsold' ? 'selected' : ''; ?>>Unsold</option>
            </select>
            
            <input type="submit" value="Search">
        </form>

        <!-- Dog Section -->
        <div class="dog-section">
            <?php while ($dog = $dogs->fetch_assoc()): ?>
                <div class="dog-ticket">
                    <div class="dog-image">
                        <img src="../assets/images/<?php echo htmlspecialchars($dog['image']); ?>" alt="Dog Image">
                    </div>
                    <div class="dog-details">
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($dog['name']); ?></p>
                        <p><strong>Breed:</strong> <?php echo htmlspecialchars($dog['breed']); ?></p>
                        <p><strong>Age:</strong> <?php echo htmlspecialchars($dog['age']); ?> years</p>
                        <p><strong>Price:</strong> $<?php echo htmlspecialchars($dog['price']); ?></p>
                        <p><strong>Status:</strong> <?php echo $dog['is_available'] == 0 ? 'Unsold' : 'Sold'; ?></p>
                        
                        <?php if ($dog['is_available'] == 1): ?>
                            <div class="buyer-details">
                                <h4>Buyer Details:</h4>
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($dog['buyer_name']); ?></p>
                                <p><strong>Phone:</strong> <?php echo htmlspecialchars($dog['buyer_phone']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($dog['buyer_email']); ?></p>
                                <p><strong>Address:</strong> <?php echo htmlspecialchars($dog['buyer_address']); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="action-buttons">
                        <a href="edit_dog.php?id=<?php echo $dog['dog_id']; ?>">Edit</a>
                        <a href="manage_dogs.php?delete_id=<?php echo $dog['dog_id']; ?>" class="delete-button" onclick="return confirm('Are you sure you want to delete this dog?')">Delete</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>
