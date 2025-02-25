<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'db_connect.php';

// Save cart before logout
if (isset($_SESSION['username']) && !empty($_SESSION['cart'])) {
    $username = $_SESSION['username'];
    $userQuery = mysqli_query($conn, "SELECT id FROM logindetails WHERE username = '$username'");

    if ($userQuery && mysqli_num_rows($userQuery) > 0) {
        $userId = mysqli_fetch_assoc($userQuery)['id'];
        mysqli_query($conn, "DELETE FROM user_carts WHERE user_id = $userId");

        foreach ($_SESSION['cart'] as $productId => $quantity) {
            mysqli_query($conn, "INSERT INTO user_carts (user_id, product_id, quantity) VALUES ($userId, $productId, $quantity)");
        }
    }
}

// Clear session safely
$_SESSION = [];
session_unset();
session_destroy();

// **Ensure no output above header() or this fails!**
header('Location: login.html');
exit();
?>
