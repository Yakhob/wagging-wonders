<?php
$conn = new mysqli('localhost', 'root', '', 'dog_shop');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Validate and retrieve the product ID from the URL
if (!isset($_GET['product_id']) || !is_numeric($_GET['product_id'])) {
    die("Invalid product ID.");
}
$product_id = intval($_GET['product_id']);

// Fetch product details
$product_query = "SELECT name, stock_quantity FROM products WHERE product_id = $product_id";
$product_result = $conn->query($product_query);
if ($product_result->num_rows === 0) {
    die("Product not found.");
}
$product = $product_result->fetch_assoc();

// Fetch sales details
$sales_query = "
    SELECT u.user_id, u.username AS user_name, ps.quantity, ps.purchase_date 
    FROM purchases ps
    JOIN users u ON ps.user_id = u.user_id
    WHERE ps.product_id = $product_id
";
$sales_result = $conn->query($sales_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Sales</title>
    <link rel="stylesheet" href="../assets/css/product_sales.css">
</head>
<body>
     <!-- Back to Products Button -->
     <a href="manage_dogs.php" class="back-button">Manage Dogs</a>

<!-- Stock Quantity -->
<p class="stock-quantity">Available Stock: <?php echo $product['stock_quantity']; ?></p>
    <div class="container">
       

        <!-- Centered Heading -->
        <h2 class="centered-heading">Sales Details for <?php echo htmlspecialchars($product['name']); ?></h2>

        <!-- Sales Table -->
        <table class="sales-table">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>User Name</th>
                    <th>Quantity Purchased</th>
                    <th>Purchase Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($sales_result->num_rows > 0) {
                    while ($row = $sales_result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['user_id']}</td>
                                <td>{$row['user_name']}</td>
                                <td>{$row['quantity']}</td>
                                <td>{$row['purchase_date']}</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No sales data available for this product.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
$conn->close();
?>
