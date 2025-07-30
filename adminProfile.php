<?php
session_start(); // Start the session

include 'connection.php';

// Check if the admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
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
    $adminEmail = $admin['email'];  // Get the admin's email
    $adminPassword = $admin['password'];  // Get the admin's current password (hashed)
} else {
    // In case there's an error or admin data is not found
    echo "Error fetching admin data.";
    exit();
}

$stmt->close();
$connection->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="adminProfile.css">
</head>
<body>
    <div class="profile-container">
        <h2>Admin Profile</h2>

        <div class="profile-info">
            <!-- Display the fetched admin name and email -->
            <label>Name:</label>
            <div class="input-container">
                <input type="text" id="adminName" value="<?php echo $adminName; ?>" readonly>
            </div>

            <label>Email:</label>
            <div class="input-container">
                <input type="email" id="email" value="<?php echo $adminEmail; ?>" readonly>
            </div>

        </div>

        <a href="passwordChange.php" class="change-password-link">
            <button class="save-btn" id="saveBtn">Change Password</button>
        </a>

        <button class="back-btn" onclick="window.location.href='dashboard.php'">Proceed to Dashboard</button>
    </div>

    <script>
        function togglePassword() {
            let passwordInput = document.getElementById("Psw");
            let button = event.target;
            // Toggle password visibility
            passwordInput.type = passwordInput.type === "password" ? "text" : "password";
            button.textContent = passwordInput.type === "password" ? "🔒" : "👁️";
        }

        function enableEdit(field) {
            let inputField = document.getElementById(field);
            inputField.removeAttribute("readonly");
            document.getElementById("saveBtn").disabled = false;  // Enable save button
        }

        function saveChanges() {
            let name = document.getElementById("adminName").value;
            let email = document.getElementById("email").value;
            let password = document.getElementById("Psw").value;

            // Use AJAX to send the data to the backend
            let xhr = new XMLHttpRequest();
            xhr.open("POST", "updateAdmin.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    alert("Admin details updated successfully.");
                    location.reload(); // Reload the page to show the updated details
                }
            };
            xhr.send("name=" + encodeURIComponent(name) + "&email=" + encodeURIComponent(email) + "&password=" + encodeURIComponent(password));
        }
    </script>
</body>
</html>
