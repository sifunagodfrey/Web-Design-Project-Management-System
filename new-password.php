<?php
session_start();
require 'includes/connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and sanitize user input
    $reset_code = $_POST['reset_code'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if reset code is valid
    $stmt = $conn->prepare("SELECT email FROM staff WHERE reset_code = ?");
    $stmt->bind_param("s", $reset_code);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($email);
        $stmt->fetch();

        // Validate passwords
        if ($new_password === $confirm_password) {
            // Hash the new password
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

            // Update the staff table with the new password and clear the reset code
            $update_stmt = $conn->prepare("UPDATE staff SET password = ?, reset_code = NULL WHERE email = ?");
            $update_stmt->bind_param("ss", $hashed_password, $email);
            $update_stmt->execute();
            $update_stmt->close();

            $response = array('status' => 'success', 'message' => 'Password updated successfully.');
        } else {
            $response = array('status' => 'error', 'message' => 'Passwords do not match.');
        }
    } else {
        $response = array('status' => 'error', 'message' => 'Invalid reset code.');
    }

    $stmt->close();
    $conn->close();
    echo json_encode($response);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>New Password</title>
    <style>
        .error-message {
            color: #d9534f; /* Red color for errors */
            background-color: #f2dede; /* Light red background */
            border: 1px solid #d9534f; /* Red border */
            border-radius: 4px;
            padding: 10px;
            margin-top: 10px;
            display: none; /* Initially hidden */
        }
        .success-message {
            color: #5bc0de; /* Light blue color for success */
            background-color: #d9edf7; /* Light blue background */
            border: 1px solid #5bc0de; /* Blue border */
            border-radius: 4px;
            padding: 10px;
            margin-top: 10px;
            display: none; /* Initially hidden */
        }
        .form-group {
            margin-bottom: 15px;
        }
    </style>
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-header">
            <h1>Wenamax Admin</h1>
        </div>
        <div class="login-content">
            <form id="new-password-form" method="POST">
                <div class="form-group">
                    <input type="text" name="reset_code" id="reset_code" placeholder="Enter reset code" required>
                </div>
                <div class="form-group">
                    <input type="password" name="new_password" id="new_password" placeholder="Enter new password" required>
                </div>
                <div class="form-group">
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm new password" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="login-btn">Update Password</button>
                </div>
                <div id="error-message" class="error-message"></div>
                <div id="success-message" class="success-message"></div>
                <div class="form-group">
                    <a href="index.php" class="forgot-password">Back to Login</a>
                </div>
            </form>
        </div>
    </div>
    <script>
        document.getElementById('new-password-form').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent default form submission

            var formData = new FormData(this);

            fetch('new-password.php', { // Point to the same PHP file
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                var errorMessageDiv = document.getElementById('error-message');
                var successMessageDiv = document.getElementById('success-message');

                if (data.status === 'error') {
                    errorMessageDiv.textContent = data.message;
                    errorMessageDiv.style.display = 'block'; // Show the error message
                    successMessageDiv.style.display = 'none'; // Hide the success message
                } else if (data.status === 'success') {
                    successMessageDiv.textContent = data.message;
                    successMessageDiv.style.display = 'block'; // Show the success message
                    errorMessageDiv.style.display = 'none'; // Hide the error message
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    </script>
</body>
</html>
