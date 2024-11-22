<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    // Redirect to index.php if not logged in
    header("Location: index.php");
    exit();
}

include 'includes/connection.php'; // Include the database connection file

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // Prepare and execute the SQL query to delete the notification
    $delete_query = "DELETE FROM notifications WHERE notification_id = $delete_id";
    
    if (mysqli_query($conn, $delete_query)) {
        // Redirect to the same page with success message
        header("Location: notifications.php?message=Notification deleted successfully");
        exit();
    } else {
        // Redirect to the same page with error message
        header("Location: notifications.php?message=Error deleting notification: " . mysqli_error($conn));
        exit();
    }
}

// Pagination settings
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10; // Default 10 records per page
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Search filter
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Fetch notifications with search filter and pagination
$query = "
    SELECT notifications.notification_id, notifications.title, notifications.description, notifications.date_created, staff.first_name 
    FROM notifications 
    LEFT JOIN staff ON notifications.user_id = staff.staff_id
    WHERE notifications.title LIKE '%$search%' OR notifications.description LIKE '%$search%'
    ORDER BY notifications.date_created DESC
    LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $query);

// Count total records for pagination
$count_query = "
    SELECT COUNT(*) as total 
    FROM notifications 
    LEFT JOIN staff ON notifications.user_id = staff.staff_id
    WHERE notifications.title LIKE '%$search%' OR notifications.description LIKE '%$search%'";

$count_result = mysqli_query($conn, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $limit);

?>

<?php include 'includes/header.php'; ?>
<div class="d-flex" id="wrapper">
    <?php include 'includes/side-bar.php'; ?>
    <div id="page-content-wrapper">
        <div class="container-fluid">
            <h1 class="mt-4">Notifications</h1>

            <!-- Display any messages -->
            <?php if (isset($_GET['message'])): ?>
                <div class="alert alert-info">
                    <?php echo htmlspecialchars($_GET['message']); ?>
                </div>
            <?php endif; ?>

            <!-- Search Form -->
            <form method="GET" action="notifications.php">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control" placeholder="Search notifications..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="limit" class="form-control" onchange="this.form.submit()">
                            <option value="5" <?php if ($limit == 5) echo 'selected'; ?>>Show 5</option>
                            <option value="10" <?php if ($limit == 10) echo 'selected'; ?>>Show 10</option>
                            <option value="50" <?php if ($limit == 50) echo 'selected'; ?>>Show 50</option>
                            <option value="<?php echo $total_records; ?>" <?php if ($limit == $total_records) echo 'selected'; ?>>Show All</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">Search</button>
                    </div>
                </div>
            </form>

            <!-- Notifications Table -->
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Notification Title</th>
                            <th>Description</th>
                            <th>Creator</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['first_name'] ?? 'N/A') . "</td>";
                                echo "<td>" . htmlspecialchars($row['date_created']) . "</td>";
                                echo "<td>";
                                echo "<a href='edit-notification.php?id=" . $row['notification_id'] . "' class='btn btn-info btn-sm'>Edit</a> ";
                                echo "<a href='#' class='btn btn-danger btn-sm' onclick='confirmDelete(" . $row['notification_id'] . ")'>Delete</a>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5'>No notifications found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Controls -->
            <nav aria-label="Page navigation">
                <ul class="pagination">
                    <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                        <a class="page-link" href="?search=<?php echo $search; ?>&limit=<?php echo $limit; ?>&page=<?php echo $page - 1; ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                            <a class="page-link" href="?search=<?php echo $search; ?>&limit=<?php echo $limit; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php if ($page >= $total_pages) echo 'disabled'; ?>">
                        <a class="page-link" href="?search=<?php echo $search; ?>&limit=<?php echo $limit; ?>&page=<?php echo $page + 1; ?>">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<script>
function confirmDelete(notificationId) {
    if (confirm("Are you sure you want to delete this notification?")) {
        window.location.href = "notifications.php?delete_id=" + notificationId;
    }
}
</script>

<?php include 'includes/footer.php'; ?>
<?php mysqli_close($conn); // Close the database connection ?>
