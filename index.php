<?php
session_start();
include('config/database.php');
include('includes/functions.php');

// Check if the user is logged in
$isLoggedIn = isLoggedIn();

// Pagination and search setup
$limit = 9;
$page = isset($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$search_breed = isset($_GET['search_breed']) ? htmlspecialchars($_GET['search_breed']) : '';

// Get total dogs
if ($search_breed) {
    $total_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM dogs WHERE breed LIKE ?");
    $like_breed = '%' . $search_breed . '%';
    $total_stmt->bind_param("s", $like_breed);
} else {
    $total_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM dogs");
}
$total_stmt->execute();
$total_result = $total_stmt->get_result()->fetch_assoc();
$total_dogs = $total_result['total'];
$total_pages = ceil($total_dogs / $limit);

// Fetch dogs (only available)
if ($search_breed) {
    $stmt = $conn->prepare("SELECT dog_id, name, breed, age, price, image FROM dogs WHERE breed LIKE ? AND is_available = 0 ORDER BY RAND() LIMIT ? OFFSET ?");
    $stmt->bind_param("sii", $like_breed, $limit, $offset);
} else {
    $stmt = $conn->prepare("SELECT dog_id, name, breed, age, price, image FROM dogs WHERE is_available = 0 ORDER BY RAND() LIMIT ? OFFSET ?");
    $stmt->bind_param("ii", $limit, $offset);
}
$stmt->execute();
$dog_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wagging Wonders - Home</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/index.css">
</head>
<body>
    <?php include('includes/header.php'); ?>

    <div class="container py-5 home-container">
        <h1 class="text-center mb-4"> Welcome to Wagging Wonders 🐾</h1>

        <?php if ($isLoggedIn): ?>
            <div class="alert alert-success text-center">
                Hello, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>! 
                <a href="shop/profile.php" class="btn btn-sm btn-outline-primary ms-2">Go to your profile</a>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">
                <a href="auth/login.php" class="btn btn-primary">Login</a> to start shopping!
            </div>
        <?php endif; ?>

        <!-- Search Form -->
        <form method="GET" action="" class="d-flex justify-content-center mb-4 search-form">
            <input type="text" name="search_breed" class="form-control me-2" placeholder="Search by breed" value="<?php echo $search_breed; ?>">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>

        <!-- Dogs Grid -->
        <div class="row new-dogs">
            <?php if ($dog_result->num_rows > 0): ?>
                <?php while ($dog = $dog_result->fetch_assoc()): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            <a href="browse.php?dog_id=<?php echo $dog['dog_id']; ?>">
                                <img src="assets/images/<?php echo htmlspecialchars($dog['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($dog['name']); ?>">
                            </a>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($dog['name']); ?></h5>
                                <p class="card-text">
                                    <strong>Breed:</strong> <?php echo htmlspecialchars($dog['breed']); ?><br>
                                    <strong>Age:</strong> <?php echo htmlspecialchars($dog['age']); ?> years<br>
                                    <strong>Price:</strong> ₹<?php echo htmlspecialchars($dog['price']); ?>
                                </p>
                                <a href="browse.php?dog_id=<?php echo $dog['dog_id']; ?>" class="btn btn-outline-success w-100">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p class="text-muted">No dogs found for the breed "<strong><?php echo $search_breed; ?></strong>".</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <nav class="pagination justify-content-center">
            <ul class="pagination">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&search_breed=<?php echo urlencode($search_breed); ?>">Previous</a>
                    </li>
                <?php endif; ?>
                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&search_breed=<?php echo urlencode($search_breed); ?>">Next</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>

    <footer class="footer">
        🐾 Welcome to Wagging Wonders 🐶
Your one-stop destination for all things dog-related!

At Wagging Wonders, we’re passionate about delivering top-notch care and quality products for your beloved pets. Whether you're looking to adopt a furry friend, schedule a grooming appointment, or shop for healthy dog food and stylish accessories, we’ve got it all under one woof!
       <p> © 2025 Wagging Wonders. All rights reserved.</p>
    </footer>

    <!-- Bootstrap JS (optional if you need interactive components) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
