<?php
session_start(); // Start the session

include 'connection.php';

// Check if the admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Get admin's email from the session
$admin_email = $_SESSION['admin_email'];

// Query the database to fetch admin details using the email
$sql = "SELECT * FROM admin WHERE email = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("s", $admin_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    // Fetch admin data
    $admin = $result->fetch_assoc();
    $adminName = $admin['name'];  // Get the admin's name
    // Check if the profile picture exists or is empty
    $profilePicture = !empty($admin['profile_pic']) ? $admin['profile_pic'] : 'default-profile-pic.jpg';  // Default if no picture
} else {
    // In case there's an error or admin data is not found
    echo "Error fetching admin data.";
    exit();
}

    // Close the statement
    $stmt->close();
    
    // Check for low-stock alerts
    $lowStockQuery = "SELECT COUNT(*) AS low_stock_count FROM products WHERE quantity <= 2 AND is_active = 1";
    $lowStockResult = mysqli_query($connection, $lowStockQuery);
    $lowStockData = mysqli_fetch_assoc($lowStockResult);
    $lowStockCount = $lowStockData['low_stock_count'];

?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Butt Electric and Sanitary Store</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="stylesheet" href="dashboard.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  
  <!-- Select2 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.1.1/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
  <!-- Select2 JS -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>
<body>
<header class="header">
    <div class="profile" onclick="window.location.href='adminProfile.php'" style="cursor: pointer;">
                <!-- Display admin's name dynamically -->
        <span><?php echo htmlspecialchars($adminName); ?></span>
    </div>
    <div class="header-title">Butt Electric & Sanitary Store</div>
    <button class="btn btn-danger" onclick="window.location.href='login.php'">Logout</button>
</header>
    
    <div class="d-flex">
        <div class="sidebar">
            <div class="menu-item" data-section="products">
                <i class="fas fa-shopping-cart"></i> Products
            </div>
            <div class="menu-item" data-section="inventory">
                <i class="fas fa-boxes"></i> Inventory
            </div>
            <div class="menu-item position-relative" data-section="alerts">
            <i class="fas fa-bell<?= $lowStockCount > 0 ? ' fa-shake text-danger' : '' ?>"></i> Alerts
                <?php if ($lowStockCount > 0): ?>
                    <span class="badge bg-danger text-white rounded-circle position-absolute top-0 start-80 translate-middle p-1.5 small">
                        <?= $lowStockCount ?>
                    </span>
                <?php endif; ?>
            </div>

            <div class="menu-item" data-section="sales">
                <i class="fas fa-chart-line"></i> Sales
            </div>
            <div class="menu-item" data-section="refund">
                <i class="fas fa-hand-holding-dollar"></i> Process Refund
            </div>
            <div class="menu-item" data-section="refund-history">
                <i class="fas fa-history"></i> Refund History
            </div>
            
        </div>
        
        <main class="content">

<!-- Products Display Div -->
<div id="products" class="content-section">
    <div class="search-container">
        <form method="GET" action="#products">
            <div class="search-wrapper">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="search" name="search" class="form-control" placeholder="Search the Products..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            </div>
        </form>
    </div>
    <br><br>
  
    <h3>Products</h3>
    <br>
    <div class="product-container">
        <?php
            $query = "SELECT * FROM products WHERE is_active = 1";
            $result = mysqli_query($connection, $query);

            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $product_id = $row['id'];
                    $product_image = $row['image'];
                    $product_name = $row['name'];
                    $product_resale_price = $row['resale_price'];
                    $product_quantity = $row['quantity'];
        ?>
        <div class="product-card">
            <img src="<?= $product_image ?>" alt="<?= $product_name ?>">
            <h3 class="product-name"><?= $product_name ?></h3>
            <p class="product-price">Rs <?= $product_resale_price ?></p>
            <button class="add-to-cart"
                data-stock="<?= $product_quantity?>"
                <?= $product_quantity < 0 ? 'disabled' : '' ?>>
                Add to Cart
            </button>

        </div>
        <?php
                }
            } else {
                echo "<p>No products available.</p>";
            }
            mysqli_close($connection);
        ?>
    </div>

    <!-- Cart -->
    <div class="cart">
        <h3>Cart</h3>
        <ul id="cart-items"></ul>
        <hr>
        <p><strong>Total: Rs. <span id="cart-total">0</span></strong></p>
        <button class="btn btn-danger" id="clear-cart" href="clear_cart.php?cart_id=<?php echo $cart_id; ?>">Clear Cart</button>
        <button class="btn btn-success" id="checkout">Proceed to Payment</button>
    </div>

         
</div>


<!-- Inventory Section  -->
<?php
include 'connection.php';  // Include the database connection

// Get the inventory data after product deletion
$query = "SELECT * FROM inventory WHERE is_active = 1";
$result = mysqli_query($connection, $query);
?>

<!-- Inventory Section -->
<div id="inventory" class="content-section">
    <div class="header-section">
        <div class="search-container">
            <div class="search-wrapper">
              <i class="fas fa-search search-icon"></i>
              <input type="text" id="search-inventory-product" class="form-control" placeholder="Search the Products...">
            </div>
        </div>
        <br><br>

        <h4 id="toggleInventory" style="cursor: pointer; color: rgb(175, 36, 11); text-align: left; font-size: xx-large; font-weight: bold;">
            Inventory
        </h4>

    <!-- Hidden Total Inventory Info -->
    <div id="inventoryInfo" style="display: none; margin-top: 10px; text-align: left;">
        <h4>Total Stock: <span id="totalStock">0</span> items</h4>
        <h4>Total Original Price: Rs. <span id="totalOriginalPrice">0.00</span></h4>
        <h4>Total Resale Price: Rs. <span id="totalResalePrice">0.00</span></h4>
    </div>




        <div class="button-group">
            <button id="resetInventorySearch" class="btn btn-warning">All Inventory</button>
            <button class="btn btn-danger" onclick="window.location.href='addProduct.php'">Add Product</button>
        </div><br><br>

        <div class="table-responsive">
            <table class="table table-bordered text-center" id="inventoryTable">
                <thead class="table-light">
                    <tr>
                        <th>Product Name</th>
                        <th>Original Price</th>
                        <th>Resale Price</th>
                        <th>Sold Items</th>
                        <th>Earnings</th>
                        <th>In Stock</th>
                        <th>Profit</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
<?php while ($row = mysqli_fetch_assoc($result)) { ?>
    <tr>
        <td><?= $row['product_name'] ?></td>
        <td><?= number_format($row['original_price'], 2) ?></td>
        <td><?= number_format($row['resale_price'], 2) ?></td>
        <td><?= $row['sold_items'] ?></td>
        <td><?= number_format($row['earnings'], 2) ?></td>
        <td><?= $row['in_stock'] ?></td>
        <td class="profit"><?= number_format($row['profit'], 2) ?></td>
        <td>
            <button class="btn btn-success edit-btn" onclick="window.location.href='editProduct.php?id=<?= $row['product_id'] ?>'">Edit Product</button>
            <button class="btn btn-danger" onclick="window.location.href='deleteProduct.php?id=<?= $row['product_id'] ?>'">Delete Product</button>
        </td>
    </tr>
<?php } ?>
</tbody>

            </table>
        </div>
    </div>
</div>

<!-- Alerts Div  -->
<div id="alerts" class="content-section">
    <h3>Alerts</h3><br>
    <div class="product-container">
        <?php
            $sql = "SELECT * FROM products WHERE quantity <= 2 AND is_active = 1";
            $result = $connection->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $name = htmlspecialchars($row['name']);
                    $price = $row['resale_price'];
                    $image = !empty($row['image']) ? $row['image'] : 'default-image.jpg';
                    $id = $row['id']; // Fetch the product ID
                    ?>
                    <div class="product-card">
                        <img src="<?php echo $image; ?>" alt="<?php echo $name; ?>">
                        <h3><?php echo $name; ?></h3>
                        <p>Rs. <?php echo $price; ?></p>
                        <!-- Update the onclick to include the product ID -->
                        <button class="restock" onclick="window.location.href='editProduct.php?id=<?php echo $id; ?>'">Restock</button>
                    </div>
                    <?php
                }
            } else {
                echo "<p>No products with low stock 🌿</p>";
            }

            $connection->close();
        ?>
    </div>
</div>

<!-- Sales Record Div -->
<div id="sales" class="content-section">
    <div class="search-container">
        <form method="GET" action="#sales">
            <div class="search-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="search" class="form-control" placeholder="Search by Customer Name..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            </div>
        </form>
    </div>
    
    <br><br>

    <h3>Sales Record</h3>
    <div class="container mt-5">
        <div class="table-responsive">
            <table class="table table-bordered text-center">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Customer Name</th>
                        <th>Contact</th>
                        <th>Total Amount</th>
                        <th>Paid Amount</th>
                        <th>Due Amount</th>
                        <th>Profit</th>
                        <th>View Receipt</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    include 'connection.php'; // include your DB connection

                    $search = isset($_GET['search']) ? mysqli_real_escape_string($connection, $_GET['search']) : '';

                    // If search value is provided, filter the results
                    if (!empty($search)) {
                        $query = "SELECT * FROM sales_report WHERE customer_name LIKE '%$search%' OR invoice_no LIKE '%$search%'";
                    } else {
                        $query = "SELECT * FROM sales_report";
                    }

                    
                    $result = mysqli_query($connection, $query);

                    if ($result && mysqli_num_rows($result) > 0) {
                        $i = 1;
                        while ($row = mysqli_fetch_assoc($result)) {
                            $invoice_no = $row['invoice_no'];
                            $sale_date = date("M d, Y", strtotime($row['sale_date']));
                            $customer_name = $row['customer_name'];
                            $customer_mobile = $row['customer_mobile'];
                            $total_amount = $row['total_amount'];
                            $paid_amount = $row['paid_amount'];
                            $due_amount = $row['due_amount'];
                            $profit = $row['profit'];
                            ?>
                            <tr>
                                <td><?= $i++ ?></td> <!-- ✅ show 1, 2, 3... -->
                                <td><?= $sale_date ?></td>
                                <td><?= $customer_name ?></td>
                                <td><?= $customer_mobile ?></td>
                                <td><?= number_format($total_amount, 2) ?></td>
                                <td><?= number_format($paid_amount, 2) ?></td>
                                <td><?= number_format($due_amount, 2) ?></td>
                                <td><?= number_format($profit, 2) ?></td>
                                <td>
                                    <a href="receipt.php?invoice_no=<?= $invoice_no ?>" class="btn btn-danger">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo "<tr><td colspan='9'>No sales records found.</td></tr>";
                    }

                    mysqli_close($connection);
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


                    <!-- Refund Div -->
<div id="refund" class="content-section">
    <div class="card shadow-lg p-4">
            <div class="header-section">
            <div class="d-flex justify-content-between">
            <h5>Refund Products</h5>
            </div>
            <hr>
    

            <?php
            include 'connection.php';

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $customer_name = $_POST['customer_name'];
                $customer_mobile = $_POST['customer_mobile'];
                $product_names = $_POST['product_name'];
                $quantities = $_POST['quantity'];
                $refunded_amounts = $_POST['refunded_amount'];

                foreach ($product_names as $index => $product_name) {
                    $quantity = (int)$quantities[$index];
                    $refunded_amount = (float)$refunded_amounts[$index];

                    // Get product ID from name
                    $productQuery = mysqli_query($connection, "SELECT id FROM products WHERE name = '$product_name'");
                    $productData = mysqli_fetch_assoc($productQuery);
                    $product_id = $productData['id'];

                    // Create payment entry
                    $insertPayment = "INSERT INTO payment (customer_name, customer_mobile, refund_amount, total_bill, amount_received, return_amount, subtotal) 
                                    VALUES ('$customer_name', '$customer_mobile', $refunded_amount, 0, 0, 0, 0)";
                    mysqli_query($connection, $insertPayment);
                    $payment_id = mysqli_insert_id($connection);

                    // Insert into refunds
                    $insertRefund = "INSERT INTO refunds (customer_name, customer_mobile, product_id, quantity, refunded_amount, payment_id)
                                    VALUES ('$customer_name', '$customer_mobile', $product_id, $quantity, $refunded_amount, $payment_id)";
                    mysqli_query($connection, $insertRefund);

                    // Update products table (increase stock)
                    $updateProduct = "UPDATE products SET quantity = quantity + $quantity WHERE id = $product_id";
                    mysqli_query($connection, $updateProduct);

                    // Update inventory table
                    $updateInventory = "UPDATE inventory 
                                        SET in_stock = in_stock + $quantity, 
                                            sold_items = GREATEST(sold_items - $quantity, 0),
                                            earnings = GREATEST(earnings - $refunded_amount, 0),
                                            profit = GREATEST(profit - $refunded_amount, 0)
                                        WHERE product_id = $product_id";
                    mysqli_query($connection, $updateInventory);
                }

                echo "<script>alert('Refund processed successfully!');</script>";
            }
            ?>
        </div>

    <!-- Refund Form -->
    <form method="POST">
    <div class="mb-3">
        <label class="form-label fw-bold d-block text-start">Customer Name</label>
        <input type="text" name="customer_name" class="form-control" placeholder="Enter customer name" required>
    </div>

    <div class="mb-3">
        <label class="form-label fw-bold d-block text-start">Mobile Number</label>
        <input type="text" name="customer_mobile" class="form-control" placeholder="Enter mobile number" required>
    </div>

        <!-- Product Section -->
        <div id="productSection" class="d-flex flex-wrap gap-3 justify-content-center">
            <div class="product-item bg-light border rounded shadow-sm p-3" style="flex: 1 1 300px; max-width: 400px; min-width: 280px;">
                <label class="form-label fw-bold text-start mt-2">Product Name</label>
                <select name="product_name[]" class="form-control product-select" required>
                    <option value="" disabled selected>Select Product</option>
                    <?php
                    $result = mysqli_query($connection, "SELECT name, resale_price FROM products");
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<option value='{$row['name']}' data-price='{$row['resale_price']}'>{$row['name']}</option>";
                    }
                    ?>
                </select>

                <label class="form-label fw-bold text-start mt-3">Product Quantity</label>
                <input type="number" name="quantity[]" class="form-control quantity" min="1" value="1" required>

                <label class="form-label fw-bold text-start mt-3">Refunded Amount</label>
                <input type="text" name="refunded_amount[]" class="form-control refunded-amount" placeholder="Amount to be Refunded" readonly>

                <div class="text-end">
                <button type="button" class="btn btn-outline-danger btn-sm mt-3 remove-product">Remove Product</button>
                </div>
            </div>
        </div>

        </div>


    <div class="d-flex justify-content-between gap-3 mt-3">
        <button type="button" class="btn custom-gradient" id="addProduct">Add Another Product</button>
        <button type="submit" class="btn custom-gradient">Submit Refund</button>
    </div>
    </form>


</div>

<!-- Refund History -->
<div id="refund-history" class="content-section">
    <div class="search-container">
        <form method="GET" action="#refund-history">
            <div class="search-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="search" class="form-control" placeholder="Search by Customer Name..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            </div>
        </form>
    </div>
    <br>
    <div class="card shadow-lg p-4">
        <div class="header-section">
            <div class="d-flex justify-content-between">
                <h5>Refund History</h5>
            </div>
            <hr class="mt-5">
        </div>

        <div class="table-responsive">
        <table class="table table-bordered text-center" id="refundHistoryTable">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Customer Name</th>
                    <th>Mobile</th>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Refunded Amount</th>
                    <th>Refund Date</th>
                    <th>View Receipt</th> <!-- ✅ New Column -->
                </tr>
            </thead>
            <tbody>
                <?php
                include 'connection.php';
                $search = isset($_GET['search']) ? mysqli_real_escape_string($connection, $_GET['search']) : '';

                $query = "SELECT r.*, p.name AS product_name, r.refund_date 
                          FROM refunds r
                          JOIN products p ON r.product_id = p.id";

                if (!empty($search)) {
                    $query .= " WHERE r.customer_name LIKE '%$search%'";
                }

                $query .= " ORDER BY r.id ASC";

                $result = mysqli_query($connection, $query);
                $i = 1;

                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>" . $i++ . "</td>";
                    echo "<td>" . htmlspecialchars($row['customer_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['customer_mobile']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['product_name']) . "</td>";
                    echo "<td>" . $row['quantity'] . "</td>";
                    echo "<td>Rs. " . number_format($row['refunded_amount'], 2) . "</td>";
                    echo "<td>" . ($row['refund_date'] ?? '-') . "</td>";

                    // ✅ View Receipt button using payment_id
                    $paymentId = $row['payment_id'];
                    echo "<td>";
                    echo "<a href='receipt.php?payment_id=" . $row['payment_id'] . "&refund=1' class='btn btn-danger'>";
                    echo "<i class='fa-solid fa-eye'></i></a>";
                    echo "</td>";

                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
        </div>
    </div>
</div>






    <script src="dashboard.js">
        $(document).ready(function() {
            $('.product-select').select2({
                placeholder: "Search or select a product...",
                allowClear: true
            });
        });
        $('.product-select').select2({
            theme: 'bootstrap-5',
            placeholder: "Search or select a product...",
            allowClear: true
        });


    </script>

</body>
</html>     