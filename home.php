<?php
// Database connection
$conn = mysqli_connect("localhost", "root", "", "mcneese_bookhub");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

/**
 * Displays products based on the given category.
 *
 * @param string $category The category to filter products ('Book' or 'Office Supply').
 */
function displayProducts($category) {
    global $conn;
    $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

    $sql = "SELECT * FROM products WHERE category = '$category' 
            AND (name LIKE '%$search%' OR description LIKE '%$search%' OR author LIKE '%$search%' OR isbn LIKE '%$search%')";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        while ($product = mysqli_fetch_assoc($result)) {
            echo '<div class="product-card">
                <img src="' . htmlspecialchars($product['image_url']) . '" alt="' . htmlspecialchars($product['name']) . '" />
                <h3>' . htmlspecialchars($product['name']) . '</h3>
                <p class="category">Category: ' . htmlspecialchars($product['category']) . '</p>
                <p>Description: ' . htmlspecialchars($product['description']) . '</p>';

            if (!empty($product['author'])) {
                echo '<p>Author: ' . htmlspecialchars($product['author']) . '</p>';
            }
            if (!empty($product['isbn'])) {
                echo '<p>ISBN: ' . htmlspecialchars($product['isbn']) . '</p>';
            }

            echo '<p>Price: <span class="price">$' . number_format($product['price'], 2) . '</span></p>
                  <p>Available: ' . htmlspecialchars($product['quantity']) . '</p>
            </div>';
        }
    } else {
        echo '<p>No products found in this category.</p>';
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
    <header>
        <nav>
            <a href="home.php">Home</a> |
            <a href="cart_checkout.php">Cart & Checkout</a> |
            <a href="admin.php">Admin</a> |
            <a href="login.php">Logout</a>
        </nav>
    </header>

    <section class="search-section">
        <form action="home.php" method="GET">
            <input type="text" name="search" placeholder="Search books or office supplies..." 
                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <input type="submit" value="Search">
        </form>
    </section>

    <section class="products-section">
        <h2>Available Books</h2>
        <div class="product-container">
            <?php displayProducts('Book'); ?>
        </div>
    </section>

    <section class="products-section">
        <h2>Available Office Supplies</h2>
        <div class="product-container">
            <?php displayProducts('Office Supply'); ?>
        </div>
    </section>
</body>
</html>
