<?php
session_start();
include 'connection.php';

// Check database connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}
if (isset($_GET['clear_cart']) && $_GET['clear_cart'] == 1 && $cart_id > 0) {
    $clearStmt = $connection->prepare("DELETE FROM cart WHERE cart_id = ?");
    $clearStmt->bind_param("i", $cart_id);
    $clearStmt->execute();
    $clearStmt->close();

    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['cart'])) {
        $cart = json_decode($_POST['cart'], true);

        // Debug: Verify cart data
        echo '<pre>';
        print_r($cart);
        echo '</pre>';

        // Create a unique cart_id using timestamp
        $cart_id = time();

        foreach ($cart as $item) {
            $name = $item['name'];
            $quantity = $item['quantity'];
            $price = $item['price']; // resale price
            $subtotal = $price * $quantity;

            // Get product_id from product name
            $stmt = $connection->prepare("SELECT id FROM products WHERE name = ?");
            $stmt->bind_param("s", $name);
            $stmt->execute();
            $stmt->bind_result($product_id);
            $stmt->fetch();
            $stmt->close();

            // Debugging: Check if product_id is fetched correctly
            if ($product_id) {
                echo "Product ID: " . $product_id . "<br>";
            } else {
                echo "Product not found: " . htmlspecialchars($name);
                exit();
            }

            // ✅ Updated insert with product_name and price
            $insert = $connection->prepare("INSERT INTO cart (cart_id, product_id, product_name, quantity, price, subtotal) 
                                            VALUES (?, ?, ?, ?, ?, ?)");
            $insert->bind_param("iisidd", $cart_id, $product_id, $name, $quantity, $price, $subtotal);
            if ($insert->execute()) {

                echo "Item added to cart successfully.<br>";
            } else {
                echo "Error: " . $insert->error;
                exit();
            }
        }

        // Redirect to payment page with the cart_id
        header("Location: payment.php?cart_id=$cart_id");
        exit();
    } else {
        echo "No cart data received.";
    }
} else {
    echo "Invalid request method.";
}
?>