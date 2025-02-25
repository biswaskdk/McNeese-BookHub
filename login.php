<?php
// Database connection
$conn = mysqli_connect("localhost", "root", "", "websitelogin");

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle form submission
if (isset($_POST['login_Btn'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // Query to fetch user details
    $sql = "SELECT * FROM logindetails WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $resultPassword = $row['password'];

        if ($password === $resultPassword) {
            header('Location: home.php');
            exit();
        } else {
            echo "<script>alert('Login unsuccessful: Incorrect password.'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Login unsuccessful: User not found.'); window.history.back();</script>";
    }
}
?>
