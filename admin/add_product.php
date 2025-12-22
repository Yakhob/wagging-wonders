<?php
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $stock_quantity = $_POST['stock_quantity'];
    $product_image = $_FILES['product_image']['name'];
    $target_dir = __DIR__ . "/../assets/images/";
    $target_file = $target_dir . basename($_FILES["product_image"]["name"]);

    // Ensure the directory exists
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true); // Create the directory
    }

    // Validate and move the uploaded file
    if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
        // Connect to the database
        $conn = new mysqli('localhost', 'root', '', 'dog_shop');
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Insert data into the database
        $sql = "INSERT INTO products (name, description, price, category, stock_quantity, product_image)
                VALUES ('$name', '$description', '$price', '$category', '$stock_quantity', '$product_image')";

        if ($conn->query($sql) === TRUE) {
            // Redirect to manage_products.php after success
            header("Location: manage_products.php");
            exit();
        } else {
            echo "<script>alert('Error: Unable to add product.');</script>";
        }

        $conn->close();
    } else {
        echo "<script>alert('Error: Unable to upload image.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <link rel="stylesheet" href="../assets/css/add_product.css">
</head>
<body>
    <div class="form-container">
    <a href="manage_products.php" class="back-button">Back to Manage Products</a>
        <h2>Add New Product</h2>
        <form action="add_product.php" method="POST" enctype="multipart/form-data">
            <label for="name">Product Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="description">Description:</label>
            <textarea id="description" name="description" required></textarea>

            <label for="price">Price:</label>
            <input type="number" id="price" name="price" step="0.01" required>

            <label for="category">Category:</label>
            <select id="category" name="category" required>
                <option value="food">Food</option>
                <option value="accessory">Accessory</option>
            </select>

            <label for="stock_quantity">Stock Quantity:</label>
            <input type="number" id="stock_quantity" name="stock_quantity" required>

            <label for="product_image">Product Image:</label>
            <input type="file" id="product_image" name="product_image" required>

            <button type="submit">Add Product</button>
        </form>
    </div>
</body>
</html>
