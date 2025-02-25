<?php
session_start();
include 'db_connect.php';

if (isset($_POST['login_Btn'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM logindetails WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        if ($password === $user['password']) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_id'] = $user['id'];

            // Load saved cart
            $_SESSION['cart'] = [];
            $cartQuery = mysqli_query($conn, "SELECT * FROM user_carts WHERE user_id = {$user['id']}");
            while ($cartItem = mysqli_fetch_assoc($cartQuery)) {
                $_SESSION['cart'][$cartItem['product_id']] = $cartItem['quantity'];
            }

            header('Location: home.php'); // Redirect to home
            exit();
        } else {
            echo "<script>alert('Incorrect password'); window.location.href='login.php';</script>";
        }
    } else {
        echo "<script>alert('User not found'); window.location.href='login.php';</script>";
    }
}
?>
