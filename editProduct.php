<?php
include 'connection.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Fetch product details
    $stmt = $connection->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        echo "Product not found.";
        exit();
    }

    // Update product and inventory if form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = $_POST['name'];
        $original_price = $_POST['original_price'];
        $resale_price = $_POST['resale_price'];
        $added_quantity = (int)$_POST['quantity'];
    
        // Fetch existing quantity
        $stmt_current = $connection->prepare("SELECT quantity FROM products WHERE id = ?");
        $stmt_current->bind_param("i", $id);
        $stmt_current->execute();
        $stmt_current->bind_result($current_quantity);
        $stmt_current->fetch();
        $stmt_current->close();
    
        $new_quantity = $current_quantity + $added_quantity;
    
        // Update products table
        $stmt = $connection->prepare("UPDATE products SET name = ?, original_price = ?, resale_price = ?, quantity = ? WHERE id = ?");
        $stmt->bind_param("ssddi", $name, $original_price, $resale_price, $new_quantity, $id);
    
        if ($stmt->execute()) {
            // Update inventory table
            $invStmt = $connection->prepare("UPDATE inventory SET product_name = ?, original_price = ?, resale_price = ?, in_stock = in_stock + ? WHERE product_id = ?");
            $invStmt->bind_param("sssii", $name, $original_price, $resale_price, $added_quantity, $id);
            $invStmt->execute();
            $invStmt->close();
    
            header("Location: dashboard.php?scroll=inventory");
            exit();
        } else {
            echo "Error updating product: " . $stmt->error;
        }
    
        $stmt->close();
    }    
} else {
    echo "No product id specified.";
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background: linear-gradient(to right, #ffc107, #ff4081);
            font-family: 'Arial', sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .container {
            max-width: 400px;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.15);
        }
        .form-control {
            border-radius: 8px;
            border: 1px solid #ddd;
            margin-bottom: 12px;
            padding: 10px 12px;
        }
        .btn-update {
            background: linear-gradient(to right, #ffc107, #ff4081);
            border: none;
            color: white;
            font-weight: bold;
            width: 100%;
            border-radius: 8px;
            padding: 12px;
            transition: 0.3s;
            cursor: pointer;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }
        .form-label {
            font-weight: bold;
        }
        input[type="text"], input[type="number"] {
            width: 100%;
        }
        .container > form {
            padding: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="flex-grow-1 text-center m-0">Edit Product</h3>
        <button class="btn-close" onclick="window.location.href='dashboard.php'"></button>
    </div>
    <form method="POST" action="editProduct.php?id=<?= $product['id']; ?>">
        <div>
            <label class="form-label">Product Id:</label>
            <input type="text" name="id" value="<?= $product['id']; ?>" class="form-control" readonly>
        </div>
        <div>
            <label class="form-label">Product Name:</label>
            <input type="text" name="name" value="<?= $product['name']; ?>" class="form-control" required>
        </div>
        <div>
            <label class="form-label">Original Price:</label>
            <input type="number" name="original_price" value="<?= $product['original_price']; ?>" class="form-control" step="0.01" required>
        </div>
        <div>
            <label class="form-label">Resale Price:</label>
            <input type="number" name="resale_price" value="<?= $product['resale_price']; ?>" class="form-control" step="0.01" required>
        </div>
        <div>
            <label class="form-label">Add Quantity in Stock:</label>
            <input type="number" name="quantity" placeholder="Number of items to be added" class="form-control">
        </div>
        <button type="submit" class="btn-update">Update Product</button>
    </form>
</div>

</body>
</html>
