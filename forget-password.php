<?php
session_start();
include 'includes/connection.php'; // Include the database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    // Check if the email exists in the database
    $stmt = $conn->prepare("SELECT staff_id FROM staff WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($staff_id);
        $stmt->fetch();

        // Generate a 6-digit reset code
        $reset_code = sprintf('%06d', mt_rand(0, 999999));

        // Update the reset code in the database
        $update_stmt = $conn->prepare("UPDATE staff SET reset_code = ? WHERE email = ?");
        $update_stmt->bind_param("ss", $reset_code, $email);
        $update_stmt->execute();

        // Send the reset code to the user's email
        $to = $email;
        $subject = "Password Reset Code";
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: no-reply@yourdomain.com' . "\r\n" . 'Reply-To: no-reply@yourdomain.com' . "\r\n" . 'X-Mailer: PHP/' . phpversion();
        $message = '<!doctype html>
        <html lang="en">
        <head>
          <meta charset="UTF-8">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <title>Password Reset</title>
        </head>
        <body>
        <span class="preheader" style="color: transparent; display: none; height: 0; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; mso-hide: all; visibility: hidden; width: 0;">Your password reset code is: ' . $reset_code . '</span>
          <div class="container">
            Your password reset code is: <h1 style="color: blue">' . $reset_code . '</h1>
            <br>
            Warm regards,<br>
            no-reply@yourdomain.com
            <br><br>
            <font color="blue"><h5>Disclaimer</h5></font>
            <hr>
            This email was sent to you because you requested a password reset from <a href="https://yourdomain.com">our website</a>. Please disregard this email if you did not request this action. You can also contact us at <a href="mailto:info@yourdomain.com">info@yourdomain.com</a> for immediate assistance.<br>
          </div>
        </body>
        </html>';

        if (mail($to, $subject, $message, $headers)) {
            $response = array('status' => 'success', 'message' => 'Reset code sent to your email.');
        } else {
            $response = array('status' => 'error', 'message' => 'Failed to send reset code.');
        }
    } else {
        $response = array('status' => 'error', 'message' => 'Email not found.');
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
    <title>Forgot Password</title>
    <style>
        .error-message, .success-message {
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
        }
    </style>
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-header">
            <h1>Forgot Password</h1>
        </div>
        <div class="login-content">
            <form id="forgot-password-form" method="POST">
                <div class="form-group">
                    <input type="email" name="email" id="email" placeholder="Enter your email" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="login-btn">Send Reset Code</button>
                </div>
                <div id="message" class="error-message"></div>
                <div class="divider"></div>
                <div class="form-group">
                    <a href="index.php" class="forgot-password">Back to Login</a>
                </div>
            </form>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var form = document.getElementById('forgot-password-form');

            form.addEventListener('submit', function(event) {
                event.preventDefault(); // Prevent default form submission

                var formData = new FormData(form);

                fetch('forgot-password.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    var messageDiv = document.getElementById('message');
                    
                    if (data.status === 'error') {
                        messageDiv.textContent = data.message;
                        messageDiv.classList.add('error-message');
                        messageDiv.classList.remove('success-message');
                    } else if (data.status === 'success') {
                        messageDiv.textContent = data.message;
                        messageDiv.classList.add('success-message');
                        messageDiv.classList.remove('error-message');
                    }
                    messageDiv.style.display = 'block'; // Show the message
                })
                .catch(error => {
                    console.error('Error:', error);
                    var messageDiv = document.getElementById('message');
                    messageDiv.textContent = 'An unexpected error occurred. Please try again.';
                    messageDiv.classList.add('error-message');
                    messageDiv.classList.remove('success-message');
                    messageDiv.style.display = 'block'; // Show the message
                });
            });
        });
    </script>
</body>
</html>
