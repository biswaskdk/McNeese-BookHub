<?php
session_start();
include 'db_connect.php';

// Redirect if not admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: home.php');
    exit();
}

// Handle new user creation
if (isset($_POST['create_user'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $role = $_POST['role'];

    $checkUser = mysqli_query($conn, "SELECT * FROM logindetails WHERE username='$username'");
    if (mysqli_num_rows($checkUser) > 0) {
        $message = "Username already exists!";
    } else {
        $sql = "INSERT INTO logindetails (username, password, role) VALUES ('$username', '$password', '$role')";
        $message = mysqli_query($conn, $sql) ? "User created successfully!" : "Failed to create user.";
    }
}

// Handle new product addition
if (isset($_POST['add_product'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $category = $_POST['category'];
    $image_url = mysqli_real_escape_string($conn, $_POST['image_url']);

    $sql = "INSERT INTO products (name, description, price, quantity, category, image_url) 
            VALUES ('$name', '$description', '$price', '$quantity', '$category', '$image_url')";
    $message = mysqli_query($conn, $sql) ? "Product added successfully!" : "Failed to add product.";
}

// Fetch all products
$products = mysqli_query($conn, "SELECT * FROM products");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - McNeese BookHub</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <header class="navbar">
        <h1>Admin Dashboard</h1>
        <nav>
            <a href="home.php">Home</a>
            <a href="logout.php">Log Out</a>
        </nav>
    </header>

    <main class="container">
        <?php if (isset($message)) echo "<p class='message'>$message</p>"; ?>

        <section class="user-section">
            <h2>Add New User</h2>
            <form method="POST">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <select name="role" required>
                    <option value="" disabled selected>Select Role</option>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
                <button type="submit" name="create_user">Create User</button>
            </form>
        </section>

        <section class="product-section">
            <h2>Add New Product</h2>
            <form method="POST">
                <input type="text" name="name" placeholder="Product Name" required>
                <textarea name="description" placeholder="Product Description" required></textarea>
                <input type="number" name="price" placeholder="Price" step="0.01" required>
                <input type="number" name="quantity" placeholder="Quantity" required>
                <select name="category" required>
                    <option value="" disabled selected>Select Category</option>
                    <option value="Book">Book</option>
                    <option value="Office Supply">Office Supply</option>
                </select>
                <input type="text" name="image_url" placeholder="Image URL" required>
                <button type="submit" name="add_product">Add Product</button>
            </form>
        </section>

        <section class="edit-product-section">
            <h2>Edit Products</h2>
            <table>
                <thead>
                    <tr>
                        <th>Name</th><th>Category</th><th>Price</th><th>Quantity</th><th>Edit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($product = mysqli_fetch_assoc($products)): ?>
                        <tr>
                            <td><?= htmlspecialchars($product['name']) ?></td>
                            <td><?= htmlspecialchars($product['category']) ?></td>
                            <td>$<?= number_format($product['price'], 2) ?></td>
                            <td><?= $product['quantity'] ?></td>
                            <td><a href="edit_product.php?id=<?= $product['id'] ?>">Edit</a></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
