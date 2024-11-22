<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    // Redirect to index.php if not logged in
    header("Location: index.php");
    exit();
}

include 'includes/connection.php'; // Ensure connection to the database

// Get staff ID from URL
$staff_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch staff details from the database
$query = "SELECT * FROM staff WHERE staff_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$result = $stmt->get_result();
$staff = $result->fetch_assoc();
$stmt->close();

if (!$staff) {
    echo "Staff member not found.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update staff details
    $username = $_POST['username'];
    $email = $_POST['email'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $role = $_POST['role'];

    $update_query = "UPDATE staff SET username = ?, email = ?, first_name = ?, last_name = ?, role = ? WHERE staff_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sssssi", $username, $email, $first_name, $last_name, $role, $staff_id);
    $stmt->execute();
    $stmt->close();

    header("Location: manage-staff.php");
    exit();
}

include 'includes/header.php';
?>
<div class="d-flex" id="wrapper">
    <?php include 'includes/side-bar.php'; ?>
    <div id="page-content-wrapper">
        <div class="container-fluid">
            <h1 class="mt-4">Edit Staff</h1>

            <!-- Edit staff form -->
            <form method="post" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($staff['username']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($staff['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" class="form-control" value="<?php echo htmlspecialchars($staff['first_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" class="form-control" value="<?php echo htmlspecialchars($staff['last_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="role">Role</label>
                    <input type="text" id="role" name="role" class="form-control" value="<?php echo htmlspecialchars($staff['role']); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Update Staff</button>
                <a href="manage-staff.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>