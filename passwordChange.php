<?php
include 'connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['mail'];
    $old_password = $_POST['password'];
    $new_password = $_POST['create_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        echo "<script>alert('New password and confirm password do not match.'); window.history.back();</script>";
        exit;
    }

    $check_query = "SELECT * FROM admin WHERE email = ?";
    $stmt = $connection->prepare($check_query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        if ($row['password'] !== $old_password) {
            echo "<script>alert('Old password is incorrect.'); window.history.back();</script>";
            exit;
        }

        $update_query = "UPDATE admin SET password = ? WHERE email = ?";
        $update_stmt = $connection->prepare($update_query);
        $update_stmt->bind_param("ss", $new_password, $email);

        if ($update_stmt->execute()) {
            echo "<script>alert('Password updated successfully. You can now log in.'); window.location.href='login.php';</script>";
        } else {
            echo "<script>alert('Error updating password.'); window.history.back();</script>";
        }

        $update_stmt->close();
    } else {
        echo "<script>alert('Email not found.'); window.history.back();</script>";
    }

    $stmt->close();
    $connection->close();
}
?>


<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Change</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex flex-column justify-content-center align-items-center vh-100" style="background: linear-gradient(#ffc107, #ff4081); font-family: Arial, sans-serif; margin: 0;">

    <div class="container text-center bg-white p-4 rounded-3 shadow-lg" style="max-width: 400px;">
        <h2 class="mb-3" style="font-weight: 600; color: #272757; font-size: x-large;">Change Password</h2>
        
        <form action="passwordChange.php" method="post">
            <div class="mb-3 text-start">
                <label class="form-label" style="font-weight: bold; color: #272757;">Enter Email Address:</label>
                <input type="email" name="mail" class="form-control" style="padding: 8.5px; font-size: 14px; border: 1px solid #ccc; border-radius: 5px;" placeholder="Enter your email" required>
            </div>

            <div class="mb-3 text-start">
                <label class="form-label" style="font-weight: bold; color: #272757;">Enter Old Password:</label>
                <input type="password" name="password" class="form-control" style="padding: 8.5px; font-size: 14px; border: 1px solid #ccc; border-radius: 5px;" placeholder="Enter Old Password" required>
            </div>
            <div class="mb-3 text-start">
                <label class="form-label" style="font-weight: bold; color: #272757;">Create New Password:</label>
                <input type="password" name="create_password" class="form-control" style="padding: 8.5px; font-size: 14px; border: 1px solid #ccc; border-radius: 5px;" placeholder="Enter New Password" required>
            </div>
            <div class="mb-3 text-start">
                <label class="form-label" style="font-weight: bold; color: #272757;">Confirm Password:</label>
                <input type="password" name="confirm_password" class="form-control" style="padding: 8.5px; font-size: 14px; border: 1px solid #ccc; border-radius: 5px;" placeholder="Enter Confirm Password" required>
            </div>
            
            <button type="submit" class="btn w-100 text-white fw-bold" style="padding: 10px; font-size: 16px; background: linear-gradient(to right, #ffc107, #ff4081); border-radius: 5px; transition: 0.3s;" >Login</button>
        </form>
    </div>
    
    <div class="text-center text-white py-2 w-100" style="background: rgb(231, 87, 44); font-size: 15px; margin-top: 99px; font-weight: lighter;">
        <p class="mb-0">&copy; 2025 | POS (Point Of Sale) System. All Rights Reserved | Developed by <a href="#" class="text-white fw-bold">MK</a></p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>