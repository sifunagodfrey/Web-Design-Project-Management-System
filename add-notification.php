<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    // Redirect to index.php if not logged in
    header("Location: index.php");
    exit();
}

include 'includes/connection.php'; // Include the database connection file

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    
    // Prepare and execute the SQL query to insert data into the notifications table
    $query = "INSERT INTO notifications (title, description) VALUES ('$title', '$description')";
    
    if (mysqli_query($conn, $query)) {
        // Redirect to notifications page with success message
        header("Location: notifications.php?message=Notification added successfully");
        exit();
    } else {
        // Redirect to notifications page with error message
        header("Location: notifications.php?message=Error adding notification: " . mysqli_error($conn));
        exit();
    }
    
    mysqli_close($conn); // Close the database connection
}
?>

<?php include 'includes/header.php'; ?>
<div class="d-flex" id="wrapper">
    <?php include 'includes/side-bar.php'; ?>
    <div id="page-content-wrapper">
        <div class="container-fluid">
            <h1 class="mt-4">Add New Notification</h1>
            <form action="add-notification.php" method="POST">
                <div class="form-group">
                    <label for="title">Notification Title</label>
                    <input type="text" id="title" name="title" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="4" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Save Notification</button>
            </form>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
