<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    // Redirect to index.php if not logged in
    header("Location: index.php");
    exit();
}

include 'includes/connection.php';

// Initialize error message variable
$error_message = "";

// Fetch logged-in staff profile details
$email = $_SESSION['email'];
$sql = "SELECT * FROM staff WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$staff = $result->fetch_assoc();
$staff_id = $staff['staff_id'];
$is_admin = $staff['role'] === 'admin'; // Assuming 'role' field indicates if the user is an admin

// Fetch earnings details
$sql = "SELECT * FROM earnings WHERE staff_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$result = $stmt->get_result();
$earnings = $result->fetch_assoc();
$total_earnings = $earnings['total_earnings'] ?? 0;
$bonus = $earnings['bonus'] ?? 0;
$total_income = $total_earnings + $bonus;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['change_password'])) {
        // Handle password change
        $currentPassword = $_POST['currentPassword'];
        $newPassword = $_POST['newPassword'];
        $confirmPassword = $_POST['confirmPassword'];

        // Verify current password
        $sql = "SELECT password FROM staff WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $hashedPassword = $row['password'];

        if (password_verify($currentPassword, $hashedPassword)) {
            if ($newPassword === $confirmPassword) {
                // Hash new password
                $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $sql = "UPDATE staff SET password = ? WHERE email = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ss", $newHashedPassword, $email);
                if ($stmt->execute()) {
                    $error_message = "<p style='color: green;'>Password changed successfully.</p>";
                } else {
                    $error_message = "<p style='color: red;'>Error updating password.</p>";
                }
            } else {
                $error_message = "<p style='color: red;'>New password and confirm password do not match.</p>";
            }
        } else {
            $error_message = "<p style='color: red;'>Current password is incorrect.</p>";
        }
    } elseif ($is_admin && isset($_POST['add_revenue'])) {
        // Handle revenue addition
        $staff_id_revenue = $_POST['staff_id'];
        $new_earnings = $_POST['new_earnings'];
        $new_bonus = $_POST['new_bonus'];

        // Update earnings table
        $sql = "UPDATE earnings SET total_earnings = total_earnings + ?, bonus = bonus + ? WHERE staff_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ddi", $new_earnings, $new_bonus, $staff_id_revenue);
        if ($stmt->execute()) {
            $error_message = "<p style='color: green;'>Revenue added successfully.</p>";
        } else {
            $error_message = "<p style='color: red;'>Error adding revenue.</p>";
        }
    }
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="path/to/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/style.css">
    <title>Account Settings</title>
    <script>
        function validatePassword() {
            var currentPassword = document.getElementById("currentPassword").value;
            var newPassword = document.getElementById("newPassword").value;
            var confirmPassword = document.getElementById("confirmPassword").value;

            if (newPassword !== confirmPassword) {
                alert("New password and confirm password do not match.");
                return false;
            }

            return true;
        }

        function openRevenueModal() {
            document.getElementById('revenueModal').style.display = 'block';
        }

        function closeRevenueModal() {
            document.getElementById('revenueModal').style.display = 'none';
        }
    </script>
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0, 0, 0);
            background-color: rgba(0, 0, 0, 0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="d-flex" id="wrapper">
        <?php include 'includes/side-bar.php'; ?>
        <div id="page-content-wrapper">
            <div class="container-fluid">
                <h1 class="mt-4">Account Details</h1>

                <div class="row">
                    <!-- Profile Details Section -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">Profile Details</div>
                            <div class="card-body">
                                <form>
                                    <div class="form-group row">
                                        <label for="profileName" class="col-sm-4 col-form-label">Name</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="profileName" value="<?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']); ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="profileEmail" class="col-sm-4 col-form-label">Email</label>
                                        <div class="col-sm-8">
                                            <input type="email" class="form-control" id="profileEmail" value="<?php echo htmlspecialchars($staff['email']); ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="profilePhone" class="col-sm-4 col-form-label">Phone</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="profilePhone" value="<?php echo htmlspecialchars($staff['phone']); ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="profileAddress" class="col-sm-4 col-form-label">Address</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="profileAddress" value="<?php echo htmlspecialchars($staff['address']); ?>" readonly>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Earnings Section -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">Earnings</div>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <tr>
                                        <th>Total Earnings</th>
                                        <td>Ksh <?php echo number_format($total_earnings, 2); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Welcome Bonus</th>
                                        <td>Ksh <?php echo number_format($bonus, 2); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Total Income</th>
                                        <td>Ksh <?php echo number_format($total_income, 2); ?></td>
                                    </tr>
                                </table>
                                <?php if ($is_admin): ?>
                                    <button class="btn btn-primary" onclick="openRevenueModal()">Add Revenue</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Change Password Section -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">Change Password</div>
                            <div class="card-body">
                                <form method="POST" onsubmit="return validatePassword();">
                                    <div class="form-group row">
                                        <label for="currentPassword" class="col-sm-4 col-form-label">Current Password</label>
                                        <div class="col-sm-8">
                                            <input type="password" class="form-control" id="currentPassword" name="currentPassword" required>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="newPassword" class="col-sm-4 col-form-label">New Password</label>
                                        <div class="col-sm-8">
                                            <input type="password" class="form-control" id="newPassword" name="newPassword" required>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="confirmPassword" class="col-sm-4 col-form-label">Confirm Password</label>
                                        <div class="col-sm-8">
                                            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                                        </div>
                                    </div>
                                    <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                                    <div class="form-group row">
                                        <div class="col-sm-12">
                                            <?php echo $error_message; ?>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Additional content can go here -->

            </div>
        </div>
    </div>

    <!-- Revenue Modal -->
    <div id="revenueModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeRevenueModal()">&times;</span>
            <h2>Add Revenue</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="staff_id">Staff ID</label>
                    <input type="number" class="form-control" id="staff_id" name="staff_id" required>
                </div>
                <div class="form-group">
                    <label for="new_earnings">New Earnings</label>
                    <input type="number" step="0.01" class="form-control" id="new_earnings" name="new_earnings" required>
                </div>
                <div class="form-group">
                    <label for="new_bonus">New Bonus</label>
                    <input type="number" step="0.01" class="form-control" id="new_bonus" name="new_bonus" required>
                </div>
                <button type="submit" name="add_revenue" class="btn btn-primary">Add Revenue</button>
            </form>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>