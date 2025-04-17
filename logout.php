<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'db_connect.php';

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];

    // Fetch user ID securely
    $stmt = $conn->prepare("SELECT id FROM logindetails WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $userId = $result->fetch_assoc()['id'];

        // Always delete old cart
        $del_stmt = $conn->prepare("DELETE FROM user_carts WHERE user_id = ?");
        $del_stmt->bind_param("i", $userId);
        $del_stmt->execute();
        $del_stmt->close();

        // Save current cart if it exists and has items
        if (!empty($_SESSION['cart'])) {
            $insert_stmt = $conn->prepare("INSERT INTO user_carts (user_id, product_id, quantity) VALUES (?, ?, ?)");
            foreach ($_SESSION['cart'] as $productId => $quantity) {
                $insert_stmt->bind_param("iii", $userId, $productId, $quantity);
                $insert_stmt->execute();
            }
            $insert_stmt->close();
        }
    }

    $stmt->close();
}

// Clear session
$_SESSION = [];
session_unset();
session_destroy();

// Redirect to login page
header('Location: login.html');
exit();
?>
