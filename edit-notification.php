<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    // Redirect to index.php if not logged in
    header("Location: index.php");
    exit();
}
include 'includes/header.php'; 
?>
<div class="d-flex" id="wrapper">
    <?php include 'includes/side-bar.php'; ?>
    <div id="page-content-wrapper">
        <div class="container-fluid">
            <h1 class="mt-4">Edit Notification</h1>
            <?php
            // Fetch notification data from the database based on the ID
            $id = $_GET['id'];
            // Example fetch operation (replace with actual DB query)
            $notification = [
                'title' => 'Sample Title',
                'description' => 'Sample Description',
                'date' => '2024-08-10'
            ];
            ?>
            <form action="update-notification.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <div class="form-group">
                    <label for="title">Notification Title</label>
                    <input type="text" id="title" name="title" class="form-control" value="<?php echo $notification['title']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="4" required><?php echo $notification['description']; ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Update Notification</button>
            </form>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
