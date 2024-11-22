<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    // Redirect to index.php if not logged in
    header("Location: index.php");
    exit();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Redirect to the dashboard or another page if not an admin
    header("Location: dashboard.php");
    exit();
}

include 'includes/connection.php'; // Ensure connection to the database
include 'includes/header.php';

// Fetch staff members from the database
$query = "SELECT * FROM staff";
$result = $conn->query($query);

?>
<div class="d-flex" id="wrapper">
    <?php include 'includes/side-bar.php'; ?>
    <div id="page-content-wrapper">
        <div class="container-fluid">
            <h1 class="mt-4">Manage Staff</h1>

            <!-- Table for displaying staff -->
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['staff_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['role']); ?></td>
                            <td>
                                <a href="view-staff.php?id=<?php echo htmlspecialchars($row['staff_id']); ?>" class="btn btn-info btn-sm">View</a>
                                <a href="edit-staff.php?id=<?php echo htmlspecialchars($row['staff_id']); ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="delete-staff.php?id=<?php echo htmlspecialchars($row['staff_id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this staff member?');">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- Button to add new staff -->
            <a href="add-staff.php" class="btn btn-primary">Add Staff</a>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>