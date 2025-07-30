<?php
session_start();
include 'connection.php';

$cart_id = isset($_GET['cart_id']) ? intval($_GET['cart_id']) : 0;

$subtotal = 0.00;
$cart_items = [];

if ($cart_id > 0) {
    $query = "SELECT product_name, quantity, price, subtotal, product_id 
              FROM cart 
              WHERE cart_id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $cart_items[] = $row;
        $subtotal += $row['subtotal'];  // Accumulate subtotal
    }
    $stmt->close();
} else {
    echo "Invalid cart ID.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = $_POST['customer_name'];
    $customer_mobile = $_POST['customer_mobile'];
    $amount_received = floatval($_POST['amount_received']);

    $total_bill = $subtotal;
    $return_amount = $amount_received - $total_bill;

    // Insert into payment table
    $payment_stmt = $connection->prepare("INSERT INTO payment 
        (customer_name, customer_mobile, total_bill, amount_received, return_amount, subtotal) 
        VALUES (?, ?, ?, ?, ?, ?)");
    
    // Fix: Use 'd' for $subtotal (double), not 'i'
    $payment_stmt->bind_param("ssdddd", $customer_name, $customer_mobile, $total_bill, $amount_received, $return_amount, $subtotal);

    if ($payment_stmt->execute()) {
        $payment_id = $payment_stmt->insert_id;

        // Update inventory and products based on cart items
        $cart_stmt = $connection->prepare("SELECT product_id, quantity FROM cart WHERE cart_id = ?");
        $cart_stmt->bind_param("i", $cart_id);
        $cart_stmt->execute();
        $cart_result = $cart_stmt->get_result();

        while ($item = $cart_result->fetch_assoc()) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];

            // Get prices from inventory
            $price_stmt = $connection->prepare("SELECT original_price, resale_price FROM inventory WHERE product_id = ?");
            $price_stmt->bind_param("i", $product_id);
            $price_stmt->execute();
            $price_stmt->bind_result($original_price, $resale_price);
            $price_stmt->fetch();
            $price_stmt->close();

            $earnings = $resale_price * $quantity;
            $profit = ($resale_price - $original_price) * $quantity;

            // Update inventory stats
            $inv_update = $connection->prepare("UPDATE inventory 
                SET sold_items = sold_items + ?, 
                    in_stock = in_stock - ?, 
                    earnings = earnings + ?, 
                    profit = profit + ? 
                WHERE product_id = ?");
            $inv_update->bind_param("iiddi", $quantity, $quantity, $earnings, $profit, $product_id);
            $inv_update->execute();
            $inv_update->close();

            // Update product quantity
            $prod_update = $connection->prepare("UPDATE products 
                SET quantity = quantity - ? 
                WHERE id = ?");
            $prod_update->bind_param("ii", $quantity, $product_id);
            $prod_update->execute();
            $prod_update->close();
        }
        $cart_stmt->close();

        // Calculate due amount
        $due_amount = $total_bill - $amount_received;

        // Calculate total profit for sales_report
        $total_profit = 0.00;
        foreach ($cart_items as $item) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];

            $stmt = $connection->prepare("SELECT original_price FROM products WHERE id = ?");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $original_price = $row['original_price'];
            $stmt->close();

            $resale_price = $item['price'];
            $profit = ($resale_price - $original_price) * $quantity;
            $total_profit += $profit;
        }

        // Insert into sales_report
        $receipt_date = date('Y-m-d H:i:s');
        $sales_stmt = $connection->prepare("INSERT INTO sales_report 
            (invoice_no, sale_date, customer_name, customer_mobile, total_amount, paid_amount, due_amount, profit, cart_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $sales_stmt->bind_param("isssdddsi", $payment_id, $receipt_date, $customer_name, $customer_mobile, $total_bill, $amount_received, $due_amount, $total_profit, $cart_id);
        
        if (!$sales_stmt->execute()) {
            echo "Error inserting sales report: " . $sales_stmt->error;
        }
        $sales_stmt->close();

        $payment_stmt->close();

        // Redirect to receipt page
        header("Location: receipt.php?payment_id=$payment_id&cart_id=$cart_id");
        exit();
    } else {
        echo "Error processing payment: " . $payment_stmt->error;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light" style="background: linear-gradient(to right, #ffc107, #ff4081);">

<div class="container mt-5">
    <div class="card shadow-lg p-4">
        <div class="d-flex justify-content-between">
            <h5>Order Payment</h5>
            <button class="btn-close" onclick="window.location.href='dashboard.php'"></button>
        </div>
        <hr>

    <div class="mb-3">
        <div class="d-flex justify-content-between">
            <p class="mb-1"><strong>Subtotal:</strong> <span class="text-danger">Rs. <?php echo number_format($subtotal, 2); ?></span></p>
        
        <!-- Payment Method (Cash) -->
                <div class="d-flex align-items-center border p-2 rounded bg-light">
                    <img src="https://cdn-icons-png.flaticon.com/512/3135/3135706.png" alt="Cash Icon" width="30" height="30">
                    <span class="ms-2 fw-bold">Cash</span>
                </div>
        </div>  
    </div>
        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label fw-bold">Customer Name</label>
                <input type="text" class="form-control" name="customer_name" placeholder="Enter customer name" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Mobile Number</label>
                <input type="text" class="form-control" name="customer_mobile" placeholder="Enter mobile number" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Amount Received</label>
                <input type="number" step="0.01" class="form-control" name="amount_received" placeholder="Enter amount paid by customer" required>
            </div>
            <button type="submit" class="btn btn-warning w-100 mt-3">Proceed to Payment</button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>