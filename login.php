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

        // Check password (plain text comparison — consider hashing in production)
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

            // Redirect based on role
            header('Location: ' . ($user['role'] === 'admin' ? 'admin.php' : 'home.php'));
            exit();
        } else {
            echo "<script>alert('Incorrect password'); window.location.href='login.php';</script>";
        }
    } else {
        echo "<script>alert('User not found'); window.location.href='login.php';</script>";
    }
}
?>

<!-- Login Form HTML -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - McNeese BookHub</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="login-container">
        <h2>Login to McNeese BookHub</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <button type="submit" name="login_Btn">Login</button>
        </form>
        <p style="margin-top: 10px;">
            <a href="forgot_password.php">Forgot Password?</a>
        </p>
    </div>
</body>
</html>
