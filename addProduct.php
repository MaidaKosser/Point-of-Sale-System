<?php
include 'connection.php'; // Make sure this connects to your DB    

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Remove $id since it's auto-increment
    $name = $_POST['name'];
    $original_price = $_POST['original_price'];
    $resale_price = $_POST['resale_price'];
    $quantity = $_POST['quantity'];

    // Default image path (use a default image if none is uploaded)
    $default_image = 'images/default_image.jpg'; // Put your default image path here

    // Handle image upload
    if ($_FILES['image']['error'] === 0) {
        $image_name = $_FILES['image']['name'];
        $image_tmp = $_FILES['image']['tmp_name'];
        $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

        // Check if image format is valid
        if (in_array(strtolower($image_ext), $allowed_ext)) {
            // Generate a unique name for the image to avoid overwriting
            $image_new_name = uniqid('', true) . '.' . $image_ext;
            $image_path = 'images/' . $image_new_name;

            // Move the uploaded image to the "uploads" directory
            if (move_uploaded_file($image_tmp, $image_path)) {
                $image_to_save = $image_path; // Image path to store in DB
            } else {
                echo "<script>alert('Failed to upload image');</script>";
                $image_to_save = $default_image; // Fallback to default image
            }
        } else {
            echo "<script>alert('Invalid image format. Only JPG, PNG, and GIF allowed.');</script>";
            $image_to_save = $default_image; // Fallback to default image
        }
    } else {
        // No image uploaded, use default image
        $image_to_save = $default_image;
    }

    // Insert into products table
    $stmt = $connection->prepare("INSERT INTO products (image, name, original_price, resale_price, quantity) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssddi", $image_to_save, $name, $original_price, $resale_price, $quantity);

    if ($stmt->execute()) {
        $product_id = $stmt->insert_id;  // Get the last inserted product ID

        // Insert into inventory table
        $inventory_stmt = $connection->prepare("INSERT INTO inventory (product_id, product_name, original_price, resale_price, in_stock) VALUES (?, ?, ?, ?, ?)");
        $inventory_stmt->bind_param("isddi", $product_id, $name, $original_price, $resale_price, $quantity);

        if ($inventory_stmt->execute()) {
            header("Location: dashboard.php#inventory"); // Redirect to inventory
            exit();
        } else {
            echo "<script>alert('Failed to add product to inventory');</script>";
        }

        $inventory_stmt->close();
    } else {
        echo "<script>alert('Failed to add product');</script>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <style>
        .btn-primary {
            width: 100%;
            border-radius: 8px;
            background: linear-gradient(to right, #ff4081, #ffc107);
            border: none;
            color: white;
            font-weight: bold;
            transition: 0.3s ease-in-out;
        }

        .btn-primary:hover {
            background: linear-gradient(to right, #ffc107, #ff4081);
            transform: scale(1.05);
        }
    </style>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background: linear-gradient(to right, #ffc107, #ff4081);
        }
        .container {
            max-width: 500px;
            margin-top: 50px;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .form-control {
            border-radius: 8px;
        }
        .btn-primary {
            width: 100%;
            border-radius: 8px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="flex-grow-1 text-center m-0">Add New Product</h3>
        <button class="btn-close" onclick="window.location.href='dashboard.php'"></button>
    </div>

    
    <form method="POST" action="addProduct.php" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Product Image</label>
            <input type="file" name="image" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Product Name</label>
            <input type="text" name="name" class="form-control" placeholder="Enter product name" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Original Price</label>
            <input type="number" name="original_price" class="form-control" placeholder="Enter original price" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Resale Price</label>
            <input type="number" name="resale_price" class="form-control" placeholder="Enter resale price" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Quantity</label>
            <input type="number" name="quantity" class="form-control" placeholder="Enter quantity" required>
        </div>

        <button type="submit" class="btn btn-primary mt-3">Add Product</button>
    </form>
</div>

</body>
</html>
