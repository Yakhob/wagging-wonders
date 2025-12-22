<?php
// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'dog_shop');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the product ID from the URL
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];

    // Fetch the product details
    $sql = "SELECT * FROM products WHERE product_id = $product_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        echo "Product not found!";
        exit();
    }
}

// Handle form submission for updating product
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $stock_quantity = $_POST['stock_quantity'];
    $product_image = $_FILES['product_image']['name'];

    if (!empty($product_image)) {
        // Update product image
        $target_dir = __DIR__ . "/../assets/images/";
        $target_file = $target_dir . basename($product_image);

        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file)) {
            $image_sql = ", product_image = '$product_image'";
        } else {
            echo "Error uploading the image.";
            $image_sql = "";
        }
    } else {
        $image_sql = "";
    }

    // Update the product in the database
    $sql = "UPDATE products SET 
            name = '$name', 
            description = '$description', 
            price = '$price', 
            category = '$category', 
            stock_quantity = '$stock_quantity' 
            $image_sql 
            WHERE product_id = $product_id";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Product updated successfully'); window.location.href='manage_products.php';</script>";
    } else {
        echo "Error updating product: " . $conn->error;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Product</title>
    <link rel="stylesheet" href="../assets/css/update_product.css">
</head>
<body>
    <div class="container">
        <h2>Update Product</h2>
        <form action="" method="POST" enctype="multipart/form-data">
            <label for="name">Product Name</label>
            <input type="text" id="name" name="name" value="<?php echo $product['name']; ?>" required>

            <label for="description">Description</label>
            <textarea id="description" name="description" rows="5" required><?php echo $product['description']; ?></textarea>

            <label for="price">Price</label>
            <input type="number" id="price" name="price" step="0.01" value="<?php echo $product['price']; ?>" required>

            <label for="category">Category</label>
            <select id="category" name="category" required>
                <option value="food" <?php if ($product['category'] == 'food') echo 'selected'; ?>>Food</option>
                <option value="accessory" <?php if ($product['category'] == 'accessory') echo 'selected'; ?>>Accessory</option>
            </select>

            <label for="stock_quantity">Stock Quantity</label>
            <input type="number" id="stock_quantity" name="stock_quantity" value="<?php echo $product['stock_quantity']; ?>" required>

            <label for="product_image">Product Image</label>
            <input type="file" id="product_image" name="product_image">
            <img src="../assets/images/<?php echo $product['product_image']; ?>" alt="Product Image" class="product-preview">

            <button type="submit">Update Product</button>
        </form>
    </div>
</body>
</html>
