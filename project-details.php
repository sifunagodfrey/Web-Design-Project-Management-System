<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    // Redirect to index.php if not logged in
    header("Location: index.php");
    exit();
}

include 'includes/header.php'; 
include 'includes/connection.php'; // Include the database connection file

// Get the project ID from the URL
$project_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch project details from the database
$project_query = "SELECT p.project_id, p.project_name, p.description, p.start_date, p.end_date, p.status, p.created_by, s.first_name AS created_by_name
                  FROM projects p
                  JOIN staff s ON p.created_by = s.staff_id
                  WHERE p.project_id = ?";
$stmt = $conn->prepare($project_query);
$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if project exists
if ($result->num_rows > 0) {
    $project = $result->fetch_assoc();
} else {
    echo "<div class='alert alert-danger'>Project not found.</div>";
    exit();
}

$stmt->close();
mysqli_close($conn); // Close the database connection
?>

<div class="d-flex" id="wrapper">
    <?php include 'includes/side-bar.php'; ?>
    <div id="page-content-wrapper">
        <div class="container-fluid">
            <h2 class="mt-4">Project Details</h2>
            <div class="card">
                <div class="card-header">
                    <h4><?php echo htmlspecialchars($project['project_name']); ?></h4>
                </div>
                <div class="card-body">
                    <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($project['description'])); ?></p>
                    <p><strong>Start Date:</strong> <?php echo htmlspecialchars(date('Y-m-d', strtotime($project['start_date']))); ?></p>
                    <p><strong>End Date:</strong> <?php echo htmlspecialchars(date('Y-m-d', strtotime($project['end_date']))); ?></p>
                    <p><strong>Status:</strong> <?php echo htmlspecialchars($project['status']); ?></p>
                    <p><strong>Created By:</strong> <?php echo htmlspecialchars($project['created_by_name']); ?></p>
                    <a href="manage-projects.php" class="btn btn-secondary">Back to Project List</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>