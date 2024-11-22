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

include 'includes/header.php';
?>
<div class="d-flex" id="wrapper">
    <?php include 'includes/side-bar.php'; ?>
    <div id="page-content-wrapper">
        <div class="container-fluid">
            <h1 class="mt-4">View Staff</h1>

            <!-- Staff details -->
            <dl class="row">
                <dt class="col-sm-3">ID</dt>
                <dd class="col-sm-9"><?php echo htmlspecialchars($staff['staff_id']); ?></dd>
                <dt class="col-sm-3">Username</dt>
                <dd class="col-sm-9"><?php echo htmlspecialchars($staff['username']); ?></dd>
                <dt class="col-sm-3">Email</dt>
                <dd class="col-sm-9"><?php echo htmlspecialchars($staff['email']); ?></dd>
                <dt class="col-sm-3">First Name</dt>
                <dd class="col-sm-9"><?php echo htmlspecialchars($staff['first_name']); ?></dd>
                <dt class="col-sm-3">Last Name</dt>
                <dd class="col-sm-9"><?php echo htmlspecialchars($staff['last_name']); ?></dd>
                <dt class="col-sm-3">Role</dt>
                <dd class="col-sm-9"><?php echo htmlspecialchars($staff['role']); ?></dd>
            </dl>

            <!-- Back button -->
            <a href="manage-staff.php" class="btn btn-secondary">Back to Staff List</a>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>