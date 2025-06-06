<?php
session_start();
include 'db_connect.php';

// Redirect if user is not logged in or not an admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Get the product ID from the URL
if (!isset($_GET['id'])) {
    echo "No product selected.";
    exit();
}

$product_id = intval($_GET['id']);

// Fetch existing product details
$sql = "SELECT * FROM products WHERE id = $product_id";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) === 0) {
    echo "Product not found.";
    exit();
}

$product = mysqli_fetch_assoc($result);

// Handle form submission
if (isset($_POST['update_product'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = floatval($_POST['price']);
    $quantity = intval($_POST['quantity']);
    $image_url = mysqli_real_escape_string($conn, $_POST['image_url']);
    $category = isset($_POST['category']) ? mysqli_real_escape_string($conn, $_POST['category']) : '';

    if (!empty($name) && !empty($description) && $price > 0 && $quantity >= 0 && !empty($category)) {
        $update_sql = "UPDATE products 
                       SET name='$name', description='$description', price='$price', quantity='$quantity', image_url='$image_url', category='$category'
                       WHERE id=$product_id";

        if (mysqli_query($conn, $update_sql)) {
            $success_message = "Product updated successfully!";
            // Refresh the product data
            $result = mysqli_query($conn, $sql);
            $product = mysqli_fetch_assoc($result);
        } else {
            $error_message = "Failed to update product: " . mysqli_error($conn);
        }
    } else {
        $error_message = "Please fill all fields with valid data.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product - McNeese BookHub</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        .container {
            width: 500px;
            margin: auto;
            border: 1px solid #ccc;
            padding: 25px;
            border-radius: 10px;
            background-color: #f9f9f9;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        form label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }

        form input[type="text"],
        form input[type="number"],
        form textarea,
        form select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        form button {
            margin-top: 15px;
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        form button:hover {
            background-color: #218838;
        }

        .message {
            margin-top: 15px;
            text-align: center;
            font-weight: bold;
        }

        .success {
            color: green;
        }

        .error {
            color: red;
        }

        .nav-links {
            margin-bottom: 15px;
            text-align: center;
        }

        .nav-links a {
            margin: 0 10px;
            text-decoration: none;
            color: #007bff;
        }

        .nav-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Edit Product</h1>
    <div class="nav-links">
        <a href="admin.php">Back to Admin</a> | 
        <a href="logout.php">Log Out</a>
    </div>

    <?php if (isset($success_message)): ?>
        <p class="message success"><?php echo $success_message; ?></p>
    <?php elseif (isset($error_message)): ?>
        <p class="message error"><?php echo $error_message; ?></p>
    <?php endif; ?>

    <form method="POST">
        <label for="name">Product Name:</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>

        <label for="description">Description:</label>
        <textarea name="description" rows="4" required><?php echo htmlspecialchars($product['description']); ?></textarea>

        <label for="price">Price ($):</label>
        <input type="number" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" step="0.01" required>

        <label for="quantity">Quantity:</label>
        <input type="number" name="quantity" value="<?php echo htmlspecialchars($product['quantity']); ?>" required>

        <label for="image_url">Image URL:</label>
        <input type="text" name="image_url" value="<?php echo htmlspecialchars($product['image_url']); ?>" required>

        <label for="category">Category:</label>
        <select name="category" required>
            <option value="Book" <?php echo ($product['category'] === 'Book') ? 'selected' : ''; ?>>Book</option>
            <option value="Office Supply" <?php echo ($product['category'] === 'Office Supply') ? 'selected' : ''; ?>>Office Supply</option>
        </select>

        <button type="submit" name="update_product">Update Product</button>
    </form>
</div>

</body>
</html>
