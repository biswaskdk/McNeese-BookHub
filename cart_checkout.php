<?php
session_start();
include 'db_connect.php';

// Redirect if user is not logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle item removal
if (isset($_GET['remove'])) {
    $productId = $_GET['remove'];
    unset($_SESSION['cart'][$productId]);
}

// Handle payment submission
if (isset($_POST['checkout'])) {
    $paymentMethod = $_POST['payment_method'];
    echo "<script>alert('Order placed successfully using $paymentMethod!');</script>";
    $_SESSION['cart'] = []; // Clear cart after checkout
}

// Fetch product details from cart
function getCartItems() {
    global $conn;
    $cart = $_SESSION['cart'];
    $items = [];

    if (!empty($cart)) {
        $ids = implode(',', array_keys($cart));
        $sql = "SELECT * FROM products WHERE id IN ($ids)";
        $result = mysqli_query($conn, $sql);

        while ($product = mysqli_fetch_assoc($result)) {
            $product['quantity'] = $cart[$product['id']];
            $product['total_price'] = $product['price'] * $product['quantity'];
            $items[] = $product;
        }
    }

    return $items;
}

$cartItems = getCartItems();
$totalPrice = array_sum(array_column($cartItems, 'total_price'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cart & Checkout - McNeese BookHub</title>
    <link rel="stylesheet" href="css/cart_checkout.css">
</head>
<body>
    <header>
        <h1>Cart & Checkout</h1>
        <nav>
            <a href="home.php">Home</a> |
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <section class="cart-section">
        <h2>Your Cart</h2>
        <?php if (empty($cartItems)): ?>
            <p>Your cart is empty.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                            <td>$<?php echo number_format($item['total_price'], 2); ?></td>
                            <td><a href="?remove=<?php echo $item['id']; ?>">Remove</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3"><strong>Total Price:</strong></td>
                        <td><strong>$<?php echo number_format($totalPrice, 2); ?></strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        <?php endif; ?>
    </section>

    <?php if (!empty($cartItems)): ?>
        <section class="checkout-section">
            <h2>Payment Options</h2>
            <form method="POST">
                <label for="payment_method">Select Payment Method:</label>
                <select name="payment_method" id="payment_method" required>
                    <option value="Credit Card">Credit Card</option>
                    <option value="PayPal">PayPal</option>
                    <option value="Cash on Delivery">Cash on Delivery</option>
                </select>
                <button type="submit" name="checkout">Place Order</button>
            </form>
        </section>
    <?php endif; ?>
</body>
</html>
