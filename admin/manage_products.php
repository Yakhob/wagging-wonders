<?php
// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'dog_shop');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
include('../admin/admin_dashboard.php'); // Include the admin header

// Handle delete product request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $sql = "DELETE FROM products WHERE product_id = $delete_id";
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Product deleted successfully'); window.location.href='manage_products.php';</script>";
    } else {
        echo "Error deleting product: " . $conn->error;
    }
}

// Handle search request for product ID
$search_product_id = '';
if (isset($_GET['search_product_id'])) {
    $search_product_id = intval($_GET['search_product_id']); // Ensure the input is an integer
    $sql = "SELECT product_id, name, price, category, stock_quantity, product_image FROM products WHERE product_id = $search_product_id";
} else {
    // Fetch all products if no search is performed
    $sql = "SELECT product_id, name, price, category, stock_quantity, product_image FROM products";
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
    <link rel="stylesheet" href="../assets/css/manage_products.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Manage Products</h2>
            <a href="add_product.php" class="add-product-button">Add New Product</a>
        </div>

        <!-- Search Form -->
        <form method="GET" action="manage_products.php" class="search-form">
            <input type="text" name="search_product_id" value="<?php echo htmlspecialchars($search_product_id); ?>" placeholder="Search by Product ID">
            <input type="submit" value="Search">
        </form>

        <div class="product-section">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='product-ticket'>
                            <div class='product-image'>
                                <img src='../assets/images/{$row['product_image']}' alt='Product Image'>
                            </div>
                            <div class='product-details'>
                                <p><strong>Name:</strong> {$row['name']}</p>
                                <p><strong>Price:</strong> {$row['price']}</p>
                                <p><strong>Category:</strong> {$row['category']}</p>
                                <p><strong>Stock:</strong> {$row['stock_quantity']}</p>
                            </div>
                            <div class='action-buttons'>
                                <a href='update_product.php?id={$row['product_id']}' class='update-button'>Update</a>
                                <a href='manage_products.php?delete_id={$row['product_id']}' class='delete-button' onclick='return confirm(\"Are you sure you want to delete this product?\");'>Delete</a>
                                <a href='product_sales.php?product_id={$row['product_id']}' class='sales-button'>View Sales</a>
                            </div>
                          </div>";
                }
            } else {
                echo "<p>No products found</p>";
            }
            ?>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>
