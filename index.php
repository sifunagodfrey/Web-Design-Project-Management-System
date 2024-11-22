<?php
session_start();
require 'includes/connection.php'; // Include the database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and sanitize user input
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare and execute query to check login credentials and fetch user details
    $stmt = $conn->prepare("SELECT staff_id, first_name, role, password FROM staff WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($staff_id, $first_name, $role, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            // Login successful
            $_SESSION['email'] = $email;
            $_SESSION['staff_id'] = $staff_id;
            $_SESSION['first_name'] = $first_name;
            $_SESSION['role'] = $role;

            $response = array('status' => 'success', 'redirect' => 'dashboard.php');
            echo json_encode($response);

            // Add new client notification
            $notification_title = "New User Login";
            $notification_description = "$first_name logged in successfully";
            $created_at = date('Y-m-d H:i:s');

            $stmt_notification = $conn->prepare("INSERT INTO notifications (title, description, user_id, date_created) VALUES (?, ?, ?, ?)");
            $stmt_notification->bind_param("ssis", $notification_title, $notification_description, $staff_id, $created_at);
            $stmt_notification->execute();
            $stmt_notification->close();
            exit();
            // End add new client notification
            
        } else {
            // Incorrect password
            $response = array('status' => 'error', 'message' => 'Incorrect password.');
            echo json_encode($response);
            exit();
        }
    } else {
        // Email not found
        $response = array('status' => 'error', 'message' => 'Email not found.');
        echo json_encode($response);
        exit();
    }

    $stmt->close();
    $conn->close();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Login</title>
    <style>
        /* Inline CSS for error message formatting */
        .error-message {
            color: #d9534f; /* Red color for errors */
            background-color: #f2dede; /* Light red background */
            border: 1px solid #d9534f; /* Red border */
            border-radius: 4px;
            padding: 10px;
            margin-top: 10px;
            display: none; /* Initially hidden */
        }
    </style>
    <link rel="icon" href="images/wenamax-icon.png" type="image/png">
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-header">
            <h1>Wenamax Admin</h1>
        </div>
        <div class="login-content">
            <form id="login-form" method="POST">
                <div class="form-group">
                    <input type="email" name="email" id="email" placeholder="Email" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" id="password" placeholder="Password" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="login-btn">Log In</button>
                </div>
                <div id="error-message" class="error-message"></div>
                <div class="form-group">
                    <a href="forget-password.php" class="forgot-password">Forgot Password?</a>
                </div>
                <div class="divider"></div>
            </form>
        </div>
    </div>
    <script>
        document.getElementById('login-form').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent default form submission

            var formData = new FormData(this);

            fetch('index.php', { // Point to the same PHP file
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                var errorMessageDiv = document.getElementById('error-message');
                if (data.status === 'error') {
                    errorMessageDiv.textContent = data.message;
                    errorMessageDiv.style.display = 'block'; // Show the error message
                } else if (data.status === 'success') {
                    // Redirect to dashboard on successful login
                    window.location.href = data.redirect;
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    </script>
</body>
</html>