<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo "<p>Please <a href='login.php'>log in</a> to view your cart.</p>";
    exit();
}

// Add to cart functionality
if (isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
    $_SESSION['cart'][$product_id] = ($_SESSION['cart'][$product_id] ?? 0) + 1;
}

// Remove item from cart
if (isset($_GET['remove'])) {
    $product_id = intval($_GET['remove']);
    unset($_SESSION['cart'][$product_id]);
    header("Location: cart_checkout.php");
    exit();
}

$cart_items = $_SESSION['cart'] ?? [];
$total_price = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cart & Checkout - McNeese BookHub</title>
    <link rel="stylesheet" href="cart_checkout.css">
    <style>
        
    </style>
</head>
<body>

    <header class="navbar">
        <div class="logo">📚 McNeese BookHub</div>
        <nav>
            <a href="home.php">Home</a>
            <a href="logout.php">Log Out</a>
        </nav>
    </header>

    <div class="container">
        <h2>Your Shopping Cart</h2>

        <?php if (!empty($cart_items)): ?>
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
                    <?php foreach ($cart_items as $product_id => $quantity): ?>
                        <?php
                            $product_query = mysqli_query($conn, "SELECT * FROM products WHERE id = '$product_id'");
                            $product = mysqli_fetch_assoc($product_query);

                            if ($product):
                                $item_total = $product['price'] * $quantity;
                                $total_price += $item_total;
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($product['name']) ?></td>
                                <td><?= $quantity ?></td>
                                <td>$<?= number_format($product['price'], 2) ?></td>
                                <td>$<?= number_format($item_total, 2) ?></td>
                                <td><a href="?remove=<?= $product_id ?>" class="remove-btn">Remove</a></td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="cart-summary">
                Total Price: $<?= number_format($total_price, 2) ?>
            </div>

            <section class="payment-section">
                <h3>Choose Payment Method</h3>
                <form action="process_payment.php" method="POST">
                    <select name="payment_method" required>
                        <option value="" disabled selected>Select a payment method</option>
                        <option value="Credit Card">Credit Card</option>
                        <option value="PayPal">PayPal</option>
                        <option value="Cash on Delivery">Cash on Delivery</option>
                    </select>
                    <button type="submit">Confirm Payment</button>
                </form>
            </section>

        <?php else: ?>
            <p>Your cart is empty. <a href="home.php">Continue shopping</a>.</p>
        <?php endif; ?>
    </div>

</body>
</html>
