<?php
session_start();
include 'db_connect.php';

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle Add to Cart request (via AJAX)
if (isset($_POST['add_to_cart'])) {
    $product_id = intval($_POST['product_id']);
    $_SESSION['cart'][$product_id] = ($_SESSION['cart'][$product_id] ?? 0) + 1;
    echo json_encode(['cart_count' => array_sum($_SESSION['cart'])]);
    exit();
}

// Fetch cart count
$cart_count = array_sum($_SESSION['cart'] ?? []);

// Function to fetch products
function getProducts($category, $offset = 0, $limit = 4, $search = '') {
    global $conn;
    $searchQuery = mysqli_real_escape_string($conn, $search);
    $sql = "SELECT * FROM products 
            WHERE category = '$category' 
            AND (name LIKE '%$searchQuery%' OR description LIKE '%$searchQuery%') 
            LIMIT $offset, $limit";
    return mysqli_query($conn, $sql);
}

// Handle AJAX "See More" requests
if (isset($_GET['loadMore'])) {
    $category = mysqli_real_escape_string($conn, $_GET['category']);
    $offset = intval($_GET['offset']);
    $limit = intval($_GET['limit']);
    $search = $_GET['search'] ?? '';

    $result = getProducts($category, $offset, $limit, $search);

    while ($product = mysqli_fetch_assoc($result)) {
        $image = !empty($product['image_url']) ? htmlspecialchars($product['image_url']) : 'images/default.jpg';
        echo '<div class="product-card" onclick="toggleDetails(this)">
                <img src="' . $image . '" alt="' . htmlspecialchars($product['name']) . '">
                <h3>' . htmlspecialchars($product['name']) . '</h3>
                <p>' . htmlspecialchars($product['description']) . '</p>
                <p class="price">$' . number_format($product['price'], 2) . '</p>
                <button onclick="event.stopPropagation(); addToCart(' . $product['id'] . ')">Add to Cart</button>
                <div class="more-details">
                    <p><strong>Author:</strong> ' . htmlspecialchars($product['author'] ?? 'N/A') . '</p>
                    <p><strong>ISBN:</strong> ' . htmlspecialchars($product['isbn'] ?? 'N/A') . '</p>
                    <p><strong>Publisher:</strong> ' . htmlspecialchars($product['publisher'] ?? 'N/A') . '</p>
                    <p><strong>Edition:</strong> ' . htmlspecialchars($product['edition'] ?? 'N/A') . '</p>
                </div>
              </div>';
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home - McNeese BookHub</title>
    <link rel="stylesheet" href="home.css">
    <script>
        function addToCart(productId) {
            fetch('home.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'add_to_cart=1&product_id=' + productId
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('cart-count').textContent = data.cart_count;
                alert('Product added to cart!');
            });
        }

        function loadMore(category) {
            const container = document.getElementById(category + "-products");
            const offset = container.children.length;
            const limit = 4;
            const search = document.querySelector('input[name="search"]').value;

            fetch(`home.php?loadMore=true&category=${category}&offset=${offset}&limit=${limit}&search=${encodeURIComponent(search)}`)
                .then(response => response.text())
                .then(data => {
                    container.insertAdjacentHTML('beforeend', data);
                });
        }

        function clearSearch() {
            document.querySelector('input[name="search"]').value = '';
            window.location.href = 'home.php';
        }

        function toggleDetails(card) {
            card.classList.toggle('expanded');
        }
    </script>
</head>
<body>
<header class="navbar">
    <div class="logo">📚 McNeese BookHub</div>
    <ul class="nav-links">
        <li><a href="home.php">Home</a></li>
        <li><a href="cart_checkout.php">Cart (<span id="cart-count"><?= $cart_count ?></span>)</a></li>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <li><a href="admin.php">Admin</a></li>
        <?php endif; ?>
        <li><a href="<?= isset($_SESSION['username']) ? 'logout.php' : 'login.php' ?>">
            <?= isset($_SESSION['username']) ? 'Logout' : 'Login' ?>
        </a></li>
    </ul>
</header>

<main>
    <section class="search-section">
        <form method="GET" class="search-form">
            <input type="text" name="search" placeholder="Search products..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            <button type="submit">Search</button>
            <?php if (!empty($_GET['search'])): ?>
                <button type="button" class="clear-btn" onclick="clearSearch()">Clear</button>
            <?php endif; ?>
        </form>
    </section>

    <section class="products-section">
        <h2>Available Books</h2>
        <div id="Book-products" class="product-container">
            <?php
            $bookResult = getProducts('Book', 0, 4, $_GET['search'] ?? '');
            while ($product = mysqli_fetch_assoc($bookResult)):
                $image = !empty($product['image_url']) ? htmlspecialchars($product['image_url']) : 'images/defaultBooks.jpg';
            ?>
                <div class="product-card" onclick="toggleDetails(this)">
                    <img src="<?= $image ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                    <h3><?= htmlspecialchars($product['name']) ?></h3>
                    <p><?= htmlspecialchars($product['description']) ?></p>
                    <p class="price">$<?= number_format($product['price'], 2) ?></p>
                    <button onclick="event.stopPropagation(); addToCart(<?= $product['id'] ?>)">Add to Cart</button>
                    <div class="more-details">
                        <p><strong>Author:</strong> <?= htmlspecialchars($product['author'] ?? 'N/A') ?></p>
                        <p><strong>ISBN:</strong> <?= htmlspecialchars($product['isbn'] ?? 'N/A') ?></p>
                        <p><strong>Publisher:</strong> <?= htmlspecialchars($product['publisher'] ?? 'N/A') ?></p>
                        <p><strong>Edition:</strong> <?= htmlspecialchars($product['edition'] ?? 'N/A') ?></p>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        <div class="see-more-container">
            <button onclick="loadMore('Book')">See More Books</button>
        </div>
    </section>

    <section class="products-section">
        <h2>Available Office Supplies</h2>
        <div id="Office Supply-products" class="product-container">
            <?php
            $officeResult = getProducts('Office Supply', 0, 4, $_GET['search'] ?? '');
            while ($product = mysqli_fetch_assoc($officeResult)):
                $image = !empty($product['image_url']) ? htmlspecialchars($product['image_url']) : 'images/defaultOfficeSupplies.jpg';
            ?>
                <div class="product-card" onclick="toggleDetails(this)">
                    <img src="<?= $image ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                    <h3><?= htmlspecialchars($product['name']) ?></h3>
                    <p><?= htmlspecialchars($product['description']) ?></p>
                    <p class="price">$<?= number_format($product['price'], 2) ?></p>
                    <button onclick="event.stopPropagation(); addToCart(<?= $product['id'] ?>)">Add to Cart</button>
                    <div class="more-details">
                        <p><strong>Author:</strong> <?= htmlspecialchars($product['author'] ?? 'N/A') ?></p>
                        <p><strong>ISBN:</strong> <?= htmlspecialchars($product['isbn'] ?? 'N/A') ?></p>
                        <p><strong>Publisher:</strong> <?= htmlspecialchars($product['publisher'] ?? 'N/A') ?></p>
                        <p><strong>Edition:</strong> <?= htmlspecialchars($product['edition'] ?? 'N/A') ?></p>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        <div class="see-more-container">
            <button onclick="loadMore('Office Supply')">See More Office Supplies</button>
        </div>
    </section>
</main>
<script>
function toggleDetails(card) {
    card.classList.toggle('expanded');
}

function addToCart(productId) {
    fetch('home.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'add_to_cart=1&product_id=' + productId
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('cart-count').textContent = data.cart_count;
    });
}
</script>

</body>
</html>
