<?php
// Include the database connection
include 'connection.php';

// Check if 'id' is provided in the URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $product_id = intval($_GET['id']); // Sanitize the ID to ensure it's an integer

    // Start the database transaction
    $connection->begin_transaction();

    try {
        // Step 1: Soft delete from inventory
        $update_inventory_query = "UPDATE inventory SET is_active = 0 WHERE product_id = ?";
        $update_inventory_stmt = $connection->prepare($update_inventory_query);
        $update_inventory_stmt->bind_param("i", $product_id);

        if (!$update_inventory_stmt->execute()) {
            throw new Exception("❌ Failed to update inventory: " . $connection->error);
        }

        // Step 2: Soft delete from products table
        $update_product_query = "UPDATE products SET is_active = 0 WHERE id = ?";
        $update_product_stmt = $connection->prepare($update_product_query);
        $update_product_stmt->bind_param("i", $product_id);

        if (!$update_product_stmt->execute()) {
            throw new Exception("❌ Failed to update product: " . $connection->error);
        }

        // Commit the transaction after successful updates
        $connection->commit();

        // Redirect to the inventory page after soft deletion
        header("Location: dashboard.php#inventory");
        exit;

    } catch (Exception $e) {
        // Rollback transaction if any error occurs
        $connection->rollback();
        echo $e->getMessage();
    }

} else {
    echo "🚫 Invalid product ID.";
}

// Close the database connection
$connection->close();
?>
