<!DOCTYPE html>
<html>
    <head>
        <title>Login</title>
        <link rel="stylesheet" href="login.css">
    </head>
    <body>
        <form method="post" action="login.php">
            <h1>Login</h1>
            <div>
                <input type="text" placeholder="Username" name="username">
            </div>
            <div>
                <input type="password" placeholder="Password" name="password">
            </div>
            <input type="submit" value="Login" name="login_Btn">
            <div>
                Don't  you have an account with us?
            </div>
        </form>
    </body>
</html>
<?php
$conn = mysqli_connect("localhost", "root", "");
if(isset($_POST['login_Btn'])){
    $username=$_POST['username'];
    $password=$_POST['password'];
    $sql= "SELECT * FROM websitelogin.logindetails WHERE username = '$username'";
    $result = mysqli_query($conn,$sql);
    while($row = mysqli_fetch_assoc($result)){
        $resultPassword = $row['password'];
        if($password == $resultPassword){
            header('Location:home.html');
        }else{
            echo "<script>
            alert('Login unsuccessful');
            </script>";
        }
    }
}
?>