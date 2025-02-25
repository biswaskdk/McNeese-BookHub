<?php
$conn = mysqli_connect("localhost", "root", "", "mcneese_bookhub");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
