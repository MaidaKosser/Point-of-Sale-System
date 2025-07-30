<?php
session_start();
include 'connection.php';


$subtotal = 0.00;
$cart_items = [];
$payment_data = [];

$cart_id = isset($_GET['cart_id']) ? intval($_GET['cart_id']) : 0;
$payment_id = isset($_GET['payment_id']) ? intval($_GET['payment_id']) : 0;
$invoice_no = isset($_GET['invoice_no']) ? intval($_GET['invoice_no']) : 0;
$is_refund = isset($_GET['refund']) && $_GET['refund'] == 1;


// 🔄 If invoice_no is not given but cart_id exists, try to fetch it
// 🔄 If NOT a refund AND invoice_no is missing but cart_id is present, get invoice_no from sales_report
if (!$is_refund && $invoice_no === 0 && $cart_id > 0) {
    $stmt = $connection->prepare("SELECT invoice_no FROM sales_report WHERE cart_id = ?");
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $invoice_no = $row['invoice_no'];
    }
    $stmt->close();
}


// 🔄 If invoice_no exists, fetch cart_id
if ($invoice_no > 0) {
    $stmt = $connection->prepare("SELECT cart_id FROM sales_report WHERE invoice_no = ?");
    $stmt->bind_param("i", $invoice_no);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $cart_id = $row['cart_id'];
    } else {
        echo "Invalid invoice number.";
        exit();
    }
    $stmt->close();
}

// 🛒 Get Cart Items
if ($cart_id > 0) {
    // Normal sale receipt
    $stmt = $connection->prepare("SELECT product_id, product_name, quantity, price, subtotal FROM cart WHERE cart_id = ?");
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $cart_items[] = $row;
        $subtotal += $row['subtotal'];
    }

    $stmt->close();
} elseif ($is_refund && $payment_id > 0) {
    // Refund flow
    $stmt = $connection->prepare("SELECT r.*, p.name AS product_name 
                                  FROM refunds r
                                  JOIN products p ON r.product_id = p.id
                                  WHERE r.payment_id = ?");
    $stmt->bind_param("i", $payment_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $cart_items[] = [
            'product_id' => $row['product_id'],
            'product_name' => $row['product_name'],
            'quantity' => $row['quantity'],
            'price' => $row['refunded_amount'] / max($row['quantity'], 1),
            'subtotal' => $row['refunded_amount']
        ];
        $subtotal += $row['refunded_amount'];
    }

    $stmt->close();
} else {
    echo "Invalid cart ID or refund context.";
    exit();
}


// 📑 Get Payment or Sales Data
$timestamp = date('Y-m-d H:i:s');
if ($payment_id > 0) {
    $stmt = $connection->prepare("SELECT customer_name, customer_mobile, total_bill, amount_received, return_amount, id, payment_date FROM payment WHERE id = ?");
    $stmt->bind_param("i", $payment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $payment_data = $result->fetch_assoc();
    $timestamp = $payment_data['payment_date'];
    $stmt->close();
} elseif ($invoice_no > 0) {
    $stmt = $connection->prepare("SELECT customer_name, customer_mobile, total_amount, paid_amount, due_amount, sale_date FROM sales_report WHERE invoice_no = ?");
    $stmt->bind_param("i", $invoice_no);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($payment_data = $result->fetch_assoc()) {
        $payment_data['total_bill'] = $payment_data['total_amount'];
        $payment_data['amount_received'] = $payment_data['paid_amount'];
        $payment_data['return_amount'] = $payment_data['due_amount'];
        $timestamp = $payment_data['sale_date'];
    }
    $stmt->close();
} else {
    echo "Missing payment or invoice data.";
    exit();
}

// Extract values
$customer_name = $payment_data['customer_name'];
$customer_mobile = $payment_data['customer_mobile'];
$total_bill = $payment_data['total_bill'];
$amount_received = $payment_data['amount_received'];
$return_amount = $payment_data['return_amount'];

// ✅ Check if receipt already exists (based on cart_id only!)
$check_stmt = $connection->prepare("SELECT COUNT(*) AS count FROM receipts WHERE cart_id = ?");
$check_stmt->bind_param("i", $cart_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$row = $check_result->fetch_assoc();
$check_stmt->close();

$receipt_already_exists = $row['count'] > 0;

// 🔐 Only insert if not already exists
if (!$receipt_already_exists) {
    $insert_query = $connection->prepare("
        INSERT INTO receipts (cart_id, payment_id, product_id, quantity, total, receipt_date)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    foreach ($cart_items as $item) {
        $product_id = $item['product_id'];
        $quantity = $item['quantity'];
        $total = $item['subtotal'];
        $insert_query->bind_param("iiiids", $cart_id, $payment_id, $product_id, $quantity, $total, $timestamp);
        $insert_query->execute();
    }


    $insert_query->close();
}

$connection->close();

// ✅ At this point you can display the receipt or redirect
?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Receipt</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js" crossorigin="anonymous"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .receipt {
            max-width: 500px;
            margin: 40px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        #logo {
            width: 80px;            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #28a745;
        }
        .receipt-footer {
            text-align: center;
            font-size: 14px;
        }
        .icon {
            margin-right: 5px;
        }
        
    </style>
</head>
<body>
    <br>
<div class="text-center mt-3">
    <button onclick="window.location.href='dashboard.php'" class="btn btn-success">Home</button>
    <button onclick="printDiv()" class="btn btn-success">🖨️ Print Receipt</button>
</div>


<div class="receipt" id="receiptDiv">
    <div class="text-center">
        <img id="logo" src="logo.png" alt="Logo"><br><br>
        <?php if ($is_refund): ?>
            <h4>REFUND RECEIPT</h4>
        <?php else: ?>
            <h4>SALES RECEIPT</h4>
        <?php endif; ?>
    </div>


    <div class="info">
        <?php if (!$is_refund): ?>
            <p><strong>Invoice No:</strong> #<?= htmlspecialchars($invoice_no) ?></p>
        <?php else: ?>
            <p><strong>Refund Ref:</strong> #<?= htmlspecialchars($payment_id) ?></p>
        <?php endif; ?>        
        <p><strong>Date:</strong> <?php echo isset($timestamp) ? date("D M d Y H:i:s", strtotime($timestamp)) : 'N/A'; ?></p>
        <p><strong>Cashier:</strong> Abdullah Butt</p>
        <p><strong>Customer Name:</strong> <?php echo htmlspecialchars($customer_name); ?></p>
        <p><strong>Contact:</strong> <?php echo htmlspecialchars($customer_mobile); ?></p>
    </div>

    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>Item Name</th>
                <th>Price</th>
                <th>Qty</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($cart_items) > 0): ?>
                <?php foreach ($cart_items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                    <td>Rs. <?php echo number_format($item['price'], 2); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>Rs. <?php echo number_format($item['subtotal'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center">No items found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <table class="table">
        <tbody>
            <tr>
                <td>Sub Total</td>
                <td class="text-end">Rs. <?php echo number_format($subtotal, 2); ?></td>
            </tr>
            <tr class="fw-bold">
                <td>Total Bill Amount</td>
                <td class="text-end">Rs. <?php echo number_format($total_bill, 2); ?></td>
            </tr>
            <tr>
                <td><strong>Amount Received</strong></td>
                <td class="text-end"><strong>Rs. <?php echo number_format($amount_received, 2); ?></strong></td>
            </tr>
            <tr>
                <td><strong>Change</strong></td>
                <td class="text-end"><strong>Rs. -<?php echo number_format($return_amount, 2); ?></strong></td>
            </tr>
        </tbody>
    </table>


    <div class="receipt-footer">
        <p><strong>BUTT ELECTRIC & SANITARY STORE</strong></p>
        <p><i class="fa-solid fa-location-dot icon"></i> Qazafi Road, Garjakh, Gujranwala</p>
    <p><i class="fa-solid fa-phone icon mt-1 me-2"></i><strong>Phone:</strong> +92-302-7777948 , +92-302-7777948</p>
   </div>

    </div>
</div>


<script>
    sessionStorage.removeItem('cart');
    sessionStorage.removeItem('subtotal');
    
    function printDiv() {
        var printContents = document.getElementById('receiptDiv').innerHTML;
        var originalContents = document.body.innerHTML;
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
    }
    
</script>

</body>
</html> 