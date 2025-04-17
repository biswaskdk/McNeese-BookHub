<?php
session_start();
include 'db_connect.php';

$login_error = "";

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

            // Redirect based on role
            header('Location: ' . ($user['role'] === 'admin' ? 'admin.php' : 'home.php'));
            exit();
        } else {
            $login_error = "Incorrect password.";
        }
    } else {
        $login_error = "User not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - McNeese Bookstore</title>
    <link rel="stylesheet" href="login.css">
    <style>
        .error-message {
            background-color: #f8d7da;
            color: #842029;
            border: 1px solid #f5c2c7;
            padding: 10px;
            margin-top: 15px;
            border-radius: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="image-container">
        <img src="images/MSU.png" alt="MSU Logo" width="280" height="140">
    </div>
    <h1>McNeese Bookstore</h1>

    <div class="login-form">
        <form action="login.php" method="POST">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Enter your username" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>

            <input type="submit" value="Log In" name="login_Btn">
        </form>

        <?php if (!empty($login_error)): ?>
            <div class="error-message"><?= htmlspecialchars($login_error) ?></div>
        <?php endif; ?>

        <p style="margin-top: 10px;">
            <a href="forgot_password.php">Forgot Password?</a>
        </p>
    </div>
</body>
</html>
