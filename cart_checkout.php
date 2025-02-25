<?php
if (isset($_POST['checkout_Btn'])) {
    $address = htmlspecialchars($_POST['address']);
    echo "<script>
        alert('Order placed successfully!\\nShipping to: $address');
        window.location.href='home.html';
    </script>";
}
?>
