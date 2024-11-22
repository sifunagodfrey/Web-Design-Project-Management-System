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

// Get the task ID from the URL
$task_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch task details from the database
$task_query = "SELECT t.task_id, t.task_name, t.description, t.status, t.due_date, t.created_by, t.assigned_to, s1.first_name AS created_by_name, s2.first_name AS assigned_to_name
               FROM tasks t
               JOIN staff s1 ON t.created_by = s1.staff_id
               JOIN staff s2 ON t.assigned_to = s2.staff_id
               WHERE t.task_id = ?";
$stmt = $conn->prepare($task_query);
$stmt->bind_param("i", $task_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if task exists
if ($result->num_rows > 0) {
    $task = $result->fetch_assoc();
} else {
    echo "<div class='alert alert-danger'>Task not found.</div>";
    exit();
}

$stmt->close();
mysqli_close($conn); // Close the database connection
?>

<div class="d-flex" id="wrapper">
    <?php include 'includes/side-bar.php'; ?>
    <div id="page-content-wrapper">
        <div class="container-fluid">
            <h2 class="mt-4">Task Details</h2>
            <div class="card">
                <div class="card-header">
                    <h4><?php echo htmlspecialchars($task['task_name']); ?></h4>
                </div>
                <div class="card-body">
                    <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($task['description'])); ?></p>
                    <p><strong>Status:</strong> <?php echo htmlspecialchars($task['status']); ?></p>
                    <p><strong>Due Date:</strong> <?php echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($task['due_date']))); ?></p>
                    <p><strong>Created By:</strong> <?php echo htmlspecialchars($task['created_by_name']); ?></p>
                    <p><strong>Assigned To:</strong> <?php echo htmlspecialchars($task['assigned_to_name']); ?></p>
                    <a href="manage-tasks.php" class="btn btn-secondary">Back to Task List</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
