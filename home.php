<?php
session_start();
include 'db_connect.php';

// Redirect to login if not logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.html');
    exit();
}

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle Add to Cart
if (isset($_POST['add_to_cart'])) {
    $productId = $_POST['product_id'];
    $_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + 1;
    header('Location: home.php'); // Prevent form resubmission
    exit();
}

// Fetch products from database
function fetchProducts($category) {
    global $conn;
    $sql = "SELECT * FROM products WHERE category = '$category'";
    $result = mysqli_query($conn, $sql);
    return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}

// Render product cards with "Add to Cart" button
function renderProductCards($products) {
    foreach ($products as $product) {
        echo "<div class='product-card'>
                <img src='images/" . htmlspecialchars($product['image_url']) . "' alt='" . htmlspecialchars($product['name']) . "'>
                <h3>" . htmlspecialchars($product['name']) . "</h3>
                <p>" . htmlspecialchars($product['description']) . "</p>
                <p>Price: \$" . number_format($product['price'], 2) . "</p>
                <form method='POST' class='cart-form'>
                    <input type='hidden' name='product_id' value='{$product['id']}'>
                    <button type='submit' name='add_to_cart'>Add to Cart</button>
                </form>
            </div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home - McNeese BookHub</title>
    <link rel="stylesheet" href="home.css">
</head>
<body>

    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="logo">📚 McNeese BookHub</div>
        <ul class="nav-links">
            <li><a href="home.php">Home</a></li>
            <li><a href="cart_checkout.php">Cart (<?php echo array_sum($_SESSION['cart']); ?>)</a></li>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <li><a href="admin.php">Admin</a></li> <!-- Shown only for admin users -->
            <?php endif; ?>
            <li>
                <?php if (isset($_SESSION['username'])): ?>
                    <a href="logout.php">Log Out</a>
                <?php else: ?>
                    <a href="login.html">Log In</a>
                <?php endif; ?>
            </li>
        </ul>
    </nav>

    <main>
        <section class="products-section">
            <h2>Available Books</h2>
            <div class="product-container">
                <?php renderProductCards(fetchProducts('Book')); ?>
            </div>

            <h2>Available Office Supplies</h2>
            <div class="product-container">
                <?php renderProductCards(fetchProducts('Office Supply')); ?>
            </div>
        </section>
    </main>

</body>
</html>
