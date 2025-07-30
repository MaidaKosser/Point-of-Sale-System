<?php
session_start(); // Start the session

include 'connection.php';
// Initialize message
$popup_message = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if email and password are set
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Query to check credentials
        $sql = "SELECT * FROM admin WHERE email = ? AND password = ?";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("ss", $email, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            // Fetch admin data
            $admin = $result->fetch_assoc();
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_email'] = $admin['email'];  // Store email in session
            $_SESSION['admin_name'] = $admin['name'];    // Store admin's name in session
            $_SESSION['admin_profile_pic'] = $admin['profile_pic']; // Store profile pic in session

            // Redirect to dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            // Login failed
            $popup_message = "Invalid email or password.";
        }

        $stmt->close();
    }
}

$connection->close();
?>
 

<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="d-flex flex-column justify-content-center align-items-center vh-100" style="background: linear-gradient(#ffc107, #ff4081); font-family: Arial, sans-serif; margin: 0;">
    <div class="container p-4 shadow rounded bg-white text-center" style="max-width: 400px;">
        <h2 class="mb-3" style="font-weight: 600; color: #272757; font-size:x-large;">Log In</h2>

        <!-- Display error message if there's one -->
        <?php if ($popup_message != "") { ?>
            <p class="text-danger"> <?php echo $popup_message; ?> </p>
        <?php } ?>

        <form action="login.php" method="post">
            <div class="mb-3 text-start">
                <label class="form-label" style="font-weight: bold; color: #272757;">Email:</label>
                <input type="email" name="email" class="form-control" placeholder="Enter Email Address" required>
            </div>
            <div class="mb-3 text-start">
                <label class="form-label" style="font-weight: bold; color: #272757;">Password:</label>
                <div class="input-group">
                    <input type="password" name="password" id="Psw" class="form-control" placeholder="Enter Password" required>
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">🔒</button>
                </div>
            </div>
            <button type="submit" class="btn btn-warning w-100" style="padding: 10px; font-size: 16px; background: linear-gradient(to right, #ffc107, #ff4081); border-radius: 5px; transition: 0.3s; color: aliceblue; font-size: medium; font-weight: 600;">Sign In</button>
        </form>
        
        <div class="mt-2" style="text-align: right;">
            <a href="passwordForgot.php" class="forgot-password" style="color: black; font-size: 14px; margin-top: 5px;">Forgot Password?</a>
        </div>
    </div>
    <div class="text-center text-white py-2 w-100" style="background: rgb(231, 87, 44); font-size: 15px; margin-top: 99px; font-weight: lighter;">
        <p class="mb-0">&copy; 2025 | POS (Point Of Sale) System. All Rights Reserved | Developed by <a href="#" class="text-white fw-bold">MK & MM</a></p>
    </div>
    <script>
        function togglePassword() {
            let passwordInput = document.getElementById("Psw");
            let button = event.target;
            passwordInput.type = passwordInput.type === "password" ? "text" : "password";
            button.textContent = passwordInput.type === "password" ? "🔒" : "👁️";
        }
    </script>
</body>
</html>
