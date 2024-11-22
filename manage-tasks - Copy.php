<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    // Redirect to index.php if not logged in
    header("Location: index.php");
    exit();
}

include 'includes/header.php'; 
include 'includes/connection.php'; // Include the database connection

// Check if the user is admin
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$logged_in_staff_id = $_SESSION['staff_id'];

// Handle form submissions for adding, editing, and deleting tasks
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_task':
                $task_name = $_POST['task_name'];
                $description = $_POST['description'];
                $status = $_POST['status'];
                $assigned_to = $_POST['assigned_to'];
                $due_date = $_POST['due_date'];
                $created_by = $logged_in_staff_id; 

                $stmt = $conn->prepare("INSERT INTO tasks (task_name, description, status, assigned_to, due_date, created_by) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssi", $task_name, $description, $status, $assigned_to, $due_date, $created_by);
                $stmt->execute();
                $stmt->close();
                break;

            case 'edit_task':
                $task_id = $_POST['task_id'];
                $task_name = $_POST['task_name'];
                $description = $_POST['description'];
                $status = $_POST['status'];
                $assigned_to = $is_admin ? $_POST['assigned_to'] : $logged_in_staff_id; 
                $due_date = $_POST['due_date'];

                $stmt = $conn->prepare("UPDATE tasks SET task_name = ?, description = ?, status = ?, assigned_to = ?, due_date = ? WHERE task_id = ?");
                $stmt->bind_param("sssssi", $task_name, $description, $status, $assigned_to, $due_date, $task_id);
                $stmt->execute();
                $stmt->close();
                break;

            case 'delete_task':
                $task_id = $_POST['task_id'];

                $stmt = $conn->prepare("DELETE FROM tasks WHERE task_id = ?");
                $stmt->bind_param("i", $task_id);
                $stmt->execute();
                $stmt->close();
                break;
        }
    }
}

// Pagination and filtering logic
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1; // Ensure the page is at least 1
$offset = ($page - 1) * $limit;

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'today'; // Default filter is 'today'
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Adjust query based on filter
$filter_condition = '';
$current_date = date('Y-m-d');
switch ($filter) {
    case 'today':
        $filter_condition = "DATE(due_date) = '$current_date'";
        break;
    case 'tomorrow':
        $filter_condition = "DATE(due_date) = DATE_ADD('$current_date', INTERVAL 1 DAY)";
        break;
    case 'yesterday':
        $filter_condition = "DATE(due_date) = DATE_SUB('$current_date', INTERVAL 1 DAY)";
        break;
    case 'upcoming':
        $filter_condition = "DATE(due_date) > DATE_ADD('$current_date', INTERVAL 1 DAY)";
        break;
    case 'previous':
        $filter_condition = "DATE(due_date) < DATE_SUB('$current_date', INTERVAL 1 DAY)";
        break;
    default:
        $filter_condition = "1"; // No filter
}

// Fetch total record count for pagination
$totalQuery = $conn->prepare("SELECT COUNT(*) FROM tasks WHERE task_name LIKE ? AND $filter_condition");
$searchParam = "%$search%";
$totalQuery->bind_param("s", $searchParam);
$totalQuery->execute();
$totalResult = $totalQuery->get_result();
$totalRecords = $totalResult->fetch_row()[0];
$totalPages = $totalRecords > 0 ? ceil($totalRecords / $limit) : 1;

// Fetch tasks
$tasksQuery = $conn->prepare("SELECT * FROM tasks WHERE task_name LIKE ? AND $filter_condition LIMIT ? OFFSET ?");
$tasksQuery->bind_param("sii", $searchParam, $limit, $offset);
$tasksQuery->execute();
$tasks = $tasksQuery->get_result();
?>

<div class="d-flex" id="wrapper">
    <?php include 'includes/side-bar.php'; ?>
    <div id="page-content-wrapper">
        <div class="container-fluid">
            <h1 class="mt-4">Manage Tasks</h1>

            <!-- Add New Task Button -->
            <button class="btn btn-primary mb-4" data-toggle="modal" data-target="#addTaskModal">Add New Task</button>

            <!-- Filter Form -->
            <form method="GET" class="mb-4">
                <div class="form-row">
                    <div class="col-md-4 mb-2">
                        <input type="text" class="form-control" name="search" placeholder="Search task name" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-4 mb-2">
                        <select class="form-control" name="filter">
                            <option value="today" <?php echo $filter == 'today' ? 'selected' : ''; ?>>Today's Tasks</option>
                            <option value="tomorrow" <?php echo $filter == 'tomorrow' ? 'selected' : ''; ?>>Tomorrow's Tasks</option>
                            <option value="yesterday" <?php echo $filter == 'yesterday' ? 'selected' : ''; ?>>Yesterday's Tasks</option>
                            <option value="upcoming" <?php echo $filter == 'upcoming' ? 'selected' : ''; ?>>Upcoming Tasks</option>
                            <option value="previous" <?php echo $filter == 'previous' ? 'selected' : ''; ?>>Previous Tasks</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </div>
                </div>
            </form>

            <!-- Items per page selection -->
            <form method="GET" class="mb-4">
                <label for="limit">Items per page:</label>
                <select name="limit" id="limit" class="form-control d-inline-block w-auto">
                    <option value="5" <?php echo $limit == 5 ? 'selected' : ''; ?>>5</option>
                    <option value="10" <?php echo $limit == 10 ? 'selected' : ''; ?>>10</option>
                    <option value="25" <?php echo $limit == 25 ? 'selected' : ''; ?>>25</option>
                    <option value="all" <?php echo $limit == 'all' ? 'selected' : ''; ?>>All</option>
                </select>
                <button type="submit" class="btn btn-primary">Change</button>
            </form>

            <!-- Tasks Table -->
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Task Name</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Due Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $tasks->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['task_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td><?php echo htmlspecialchars($row['due_date']); ?></td>
                        <td>
                            <button class="btn btn-warning btn-edit-task" 
                                data-id="<?php echo $row['task_id']; ?>" 
                                data-name="<?php echo htmlspecialchars($row['task_name']); ?>" 
                                data-description="<?php echo htmlspecialchars($row['description']); ?>" 
                                data-status="<?php echo htmlspecialchars($row['status']); ?>" 
                                data-due-date="<?php echo htmlspecialchars($row['due_date']); ?>" 
                                data-toggle="modal" data-target="#editTaskModal">Edit</button>
                            <?php if ($is_admin): ?>
                                <button class="btn btn-danger btn-delete-task" 
                                    data-id="<?php echo $row['task_id']; ?>" 
                                    data-toggle="modal" data-target="#deleteTaskModal">Delete</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <nav aria-label="Page navigation">
                <ul class="pagination">
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?search=<?php echo urlencode($search); ?>&filter=<?php echo $filter; ?>&limit=<?php echo $limit; ?>&page=<?php echo $page - 1; ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?search=<?php echo urlencode($search); ?>&filter=<?php echo $filter; ?>&limit=<?php echo $limit; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?search=<?php echo urlencode($search); ?>&filter=<?php echo $filter; ?>&limit=<?php echo $limit; ?>&page=<?php echo $page + 1; ?>">Next</a>
                    </li>
                </ul>
            </nav>

            <!-- Modals -->
            <!-- Add Task Modal -->
            <div class="modal fade" id="addTaskModal" tabindex="-1" role="dialog" aria-labelledby="addTaskModalLabel" aria-hidden="true">
                <!-- Modal content -->
            </div>

            <!-- Edit Task Modal -->
            <div class="modal fade" id="editTaskModal" tabindex="-1" role="dialog" aria-labelledby="editTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="POST">
            <input type="hidden" name="action" value="edit_task">
            <input type="hidden" name="task_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Task</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Similar to Add Modal -->
                </div>
                <div class="modal-footer">
                    <!-- Buttons -->
                </div>
            </div>
        </form>
    </div>
</div>

            <!-- Delete Task Modal -->
            <div class="modal fade" id="deleteTaskModal" tabindex="-1" role="dialog" aria-labelledby="deleteTaskModalLabel" aria-hidden="true">
                <!-- Modal content -->
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle Edit Task button
        document.querySelectorAll('.btn-edit-task').forEach(button => {
            button.addEventListener('click', function() {
                const modal = document.getElementById('editTaskModal');
                modal.querySelector('input[name="task_id"]').value = this.dataset.id;
                modal.querySelector('input[name="task_name"]').value = this.dataset.name;
                modal.querySelector('textarea[name="description"]').value = this.dataset.description;
                modal.querySelector('input[name="status"]').value = this.dataset.status;
                modal.querySelector('input[name="due_date"]').value = this.dataset.dueDate;
            });
        });

        // Handle Delete Task button
        document.querySelectorAll('.btn-delete-task').forEach(button => {
            button.addEventListener('click', function() {
                const modal = document.getElementById('deleteTaskModal');
                modal.querySelector('input[name="task_id"]').value = this.dataset.id;
            });
        });
    });
</script>

<?php include 'footer.php'; ?>