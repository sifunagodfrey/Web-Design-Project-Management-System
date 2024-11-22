<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    // Redirect to index.php if not logged in
    header("Location: index.php");
    exit();
}

include 'includes/connection.php'; // Ensure connection to the database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $username = $_POST['username'];
    $email = $_POST['email'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $role = $_POST['role']; // Role selected from dropdown
    $phone = $_POST['phone']; // Phone number for password

    // Use phone number as the initial password (encrypted)
    $password = password_hash($phone, PASSWORD_BCRYPT);

    // Prepare and execute insert query
    $insert_query = "INSERT INTO staff (username, email, first_name, last_name, phone, role, password, created_at, updated_at) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("sssssss", $username, $email, $first_name, $last_name, $phone, $role, $password);
    $stmt->execute();
    $stmt->close();

    // Redirect to manage staff page
    header("Location: manage-staff.php");
    exit();
}

include 'includes/header.php';
?>
<div class="d-flex" id="wrapper">
    <?php include 'includes/side-bar.php'; ?>
    <div id="page-content-wrapper">
        <div class="container-fluid">
            <h1 class="mt-4">Add Staff</h1>

            <!-- Add staff form -->
            <form method="post" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="role">Role</label>
                    <select id="role" name="role" class="form-control" required>
                        <option value="">Select Role</option>
                        <option value="admin">Admin</option>
                        <option value="manager">Manager</option>
                        <option value="staff">Staff</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="text" id="phone" name="phone" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Add Staff</button>
                <a href="manage-staff.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>