<?php
session_start();
include 'connection.php';

$cart_id = isset($_GET['cart_id']) ? intval($_GET['cart_id']) : 0;

if ($cart_id > 0) {
    $stmt = $connection->prepare("DELETE FROM cart WHERE cart_id = ?");
    $stmt->bind_param("i", $cart_id);
    if ($stmt->execute()) {
        // Optional: also clear any session/cart tracking if needed
        header("Location: dashboard.php?message=cart_cleared");
        exit();
    } else {
        echo "Failed to clear cart.";
    }
    $stmt->close();
} else {
    echo "Invalid cart ID.";
}
?>
