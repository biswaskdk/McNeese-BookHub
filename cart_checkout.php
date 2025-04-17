<?php
session_start();
include 'db_connect.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo "<p>Please <a href='login.php'>log in</a> to view your cart.</p>";
    exit();
}

$success_msg = "";

// Handle payment confirmation
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['payment_method'])) {
    $errors = [];
    $payment_method = $_POST['payment_method'];

    if ($payment_method === "Credit Card" || $payment_method === "Debit Card") {
        if (empty($_POST['card_name'])) $errors[] = "Cardholder name is required.";
        if (!preg_match('/^\d{16}$/', $_POST['card_number'])) $errors[] = "Card number must be 16 digits.";
        if (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $_POST['expiry'])) $errors[] = "Expiry must be MM/YY.";
        if (!preg_match('/^\d{3,4}$/', $_POST['cvv'])) $errors[] = "CVV must be 3 or 4 digits.";
        if (empty($_POST['address']) || empty($_POST['city']) || empty($_POST['state']) || empty($_POST['zip'])) {
            $errors[] = "Billing address is required.";
        }
    }

    if ($payment_method === "PayPal") {
        if (!filter_var($_POST['paypal_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid PayPal email.";
        }
    }

    if (empty($errors)) {
        // Save order
        $card_name = $_POST['card_name'] ?? null;
        $paypal_email = $_POST['paypal_email'] ?? null;
        $address = $_POST['address'] ?? null;
        $city = $_POST['city'] ?? null;
        $state = $_POST['state'] ?? null;
        $zip = $_POST['zip'] ?? null;

        $order_stmt = $conn->prepare("INSERT INTO orders (user_id, payment_method, card_name, paypal_email, address, city, state, zip) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $order_stmt->bind_param("isssssss", $user_id, $payment_method, $card_name, $paypal_email, $address, $city, $state, $zip);
        $order_stmt->execute();
        $order_id = $order_stmt->insert_id;
        $order_stmt->close();

        // Generate formatted order number (e.g. ORD-0001)
        $order_number = "ORD-" . str_pad($order_id, 4, "0", STR_PAD_LEFT);

        // Save cart items to order_items
        if (!empty($_SESSION['cart'])) {
            $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");

            foreach ($_SESSION['cart'] as $product_id => $quantity) {
                $product_query = mysqli_query($conn, "SELECT price FROM products WHERE id = $product_id");
                $product = mysqli_fetch_assoc($product_query);
                $price = $product['price'];

                $item_stmt->bind_param("iiid", $order_id, $product_id, $quantity, $price);
                $item_stmt->execute();
            }

            $item_stmt->close();
        }

        unset($_SESSION['cart']);
        $success_msg = "✅ Order Completed!<br>Order Number: <strong>$order_number</strong>";
    } else {
        foreach ($errors as $error) {
            echo "<p style='color:red;'>$error</p>";
        }
    }
}

// Cart logic
if (isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
    $_SESSION['cart'][$product_id] = ($_SESSION['cart'][$product_id] ?? 0) + 1;
}

if (isset($_GET['increase'])) {
    $product_id = intval($_GET['increase']);
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]++;
    }
    header("Location: cart_checkout.php");
    exit();
}

if (isset($_GET['decrease'])) {
    $product_id = intval($_GET['decrease']);
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]--;
        if ($_SESSION['cart'][$product_id] < 1) {
            unset($_SESSION['cart'][$product_id]);
        }
    }
    header("Location: cart_checkout.php");
    exit();
}

if (isset($_GET['remove'])) {
    $product_id = intval($_GET['remove']);
    unset($_SESSION['cart'][$product_id]);
    header("Location: cart_checkout.php");
    exit();
}

if (isset($_GET['clear_cart'])) {
    unset($_SESSION['cart']);
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
    <script>
        function togglePaymentFields() {
            const method = document.getElementById("payment_method").value;
            const cardFields = document.getElementById("card-fields");
            const billingFields = document.getElementById("billing-fields");
            const paypalField = document.getElementById("paypal-field");

            cardFields.style.display = "none";
            billingFields.style.display = "none";
            paypalField.style.display = "none";

            document.querySelectorAll("#card-fields input, #billing-fields input, #paypal-field input")
                .forEach(el => el.disabled = true);

            if (method === "Credit Card" || method === "Debit Card") {
                cardFields.style.display = "block";
                billingFields.style.display = "block";
                document.querySelectorAll("#card-fields input, #billing-fields input")
                    .forEach(el => el.disabled = false);
            } else if (method === "PayPal") {
                paypalField.style.display = "block";
                document.querySelectorAll("#paypal-field input")
                    .forEach(el => el.disabled = false);
            }
        }

        window.addEventListener("DOMContentLoaded", togglePaymentFields);
    </script>
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

    <?php if (!empty($success_msg)): ?>
        <div class="success-msg" style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <?= $success_msg ?>
        </div>
    <?php endif; ?>

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
                            <td>
                                <a href="?decrease=<?= $product_id ?>">➖</a>
                                <?= $quantity ?>
                                <a href="?increase=<?= $product_id ?>">➕</a>
                            </td>
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

        <div style="margin-top: 10px;">
            <a href="?clear_cart=true" style="color: white; background-color: #dc3545; padding: 8px 16px; border-radius: 5px; text-decoration: none;">🗑️ Clear Cart</a>
        </div>

        <section class="payment-section" style="margin-top: 30px;">
            <h3>Choose Payment Method</h3>
            <form method="POST">
                <label for="payment_method">Payment Method:</label>
                <select name="payment_method" id="payment_method" onchange="togglePaymentFields()" required>
                    <option value="" disabled selected>Select a payment method</option>
                    <option value="Credit Card">Credit Card</option>
                    <option value="Debit Card">Debit Card</option>
                    <option value="PayPal">PayPal</option>
                    <option value="Cash on Delivery">Cash on Delivery</option>
                </select>

                <!-- Card Fields -->
                <div id="card-fields" style="display:none;">
                    <label for="card_name">Name on Card:</label>
                    <input type="text" name="card_name" id="card_name" placeholder="Cardholder Name">
                    <div class="card-row">
                        <input type="text" name="card_number" id="card_number" maxlength="16" pattern="\d{16}" placeholder="Card Number">
                        <input type="text" name="expiry" id="expiry" maxlength="5" pattern="(0[1-9]|1[0-2])/\d{2}" placeholder="MM/YY">
                        <input type="text" name="cvv" id="cvv" maxlength="4" pattern="\d{3,4}" placeholder="CVV">
                    </div>
                </div>

                <!-- PayPal Field -->
                <div id="paypal-field" style="display:none; margin-top: 20px;">
                    <label for="paypal_email">PayPal Email:</label>
                    <input type="email" name="paypal_email" id="paypal_email" placeholder="example@email.com">
                </div>

                <!-- Billing Address -->
                <div id="billing-fields" style="display:none;">
                    <h3>Billing Address</h3>
                    <label for="address">Street Address:</label>
                    <input type="text" name="address" id="address" placeholder="123 Main St">
                    <div class="address-row">
                        <input type="text" name="city" id="city" placeholder="City">
                        <input type="text" name="state" id="state" placeholder="State">
                        <input type="text" name="zip" id="zip" placeholder="ZIP Code" pattern="\d{5}">
                    </div>
                </div>

                <button type="submit" style="margin-top: 20px;">Confirm Payment</button>
            </form>
        </section>

    <?php else: ?>
        <p>Your cart is empty. <a href="home.php">Continue shopping</a>.</p>
    <?php endif; ?>
</div>

</body>
</html>
