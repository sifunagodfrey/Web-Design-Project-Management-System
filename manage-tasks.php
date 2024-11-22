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
                $assigned_to = $is_admin ? $_POST['assigned_to'] : $logged_in_staff_id; // Assign to logged-in staff if not admin
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

/// Pagination and filtering logic
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
$tasksQuery = $conn->prepare("
    SELECT 
        tasks.*, 
        CONCAT(staff.first_name, ' ', staff.last_name) AS assigned_name 
    FROM 
        tasks 
    LEFT JOIN 
        staff 
    ON 
        tasks.assigned_to = staff.staff_id 
    WHERE 
        tasks.task_name LIKE ? AND $filter_condition 
    LIMIT ? OFFSET ?");
$tasksQuery->bind_param("sii", $searchParam, $limit, $offset);
$tasksQuery->execute();
$tasks = $tasksQuery->get_result();
?>


<div class="d-flex" id="wrapper">
    <?php include 'includes/side-bar.php'; ?>
    <div id="page-content-wrapper">
        <div class="container-fluid">
            <h1 class="mt-4">Tasks and Activities</h1>

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
                <div class="col-md-4 mb-2" >
                    <select name="limit" id="limit" class="form-control" name="filter" >
                        <option value="5" <?php echo $limit == 5 ? 'selected' : ''; ?>>5</option>
                        <option value="10" <?php echo $limit == 10 ? 'selected' : ''; ?>>10</option>
                        <option value="25" <?php echo $limit == 25 ? 'selected' : ''; ?>>25</option>
                        <option value="all" <?php echo $limit == 'all' ? 'selected' : ''; ?>>All</option>
                    </select>

                </div>

                    <div class="col-md-4 mb-2">
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </div>
                </div>
            </form>

            <!-- Tasks Table -->
            <table class="table table-striped">
    <thead>
        <tr>
            <th>Task Name</th>
            <th>Status</th>
            <th>Lead Staff</th>
            <th>Due Date</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $tasks->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['task_name']); ?></td>
            <td><?php echo htmlspecialchars($row['status']); ?></td>
            <td><?php echo htmlspecialchars($row['assigned_name'] ?? 'Unassigned'); ?></td>
            <td><?php echo htmlspecialchars($row['due_date']); ?></td>
            <td>
                <button class="btn btn-warning btn-edit-task" 
                        data-id="<?php echo $row['task_id']; ?>" 
                        data-name="<?php echo htmlspecialchars($row['task_name']); ?>" 
                        data-description="<?php echo htmlspecialchars($row['description']); ?>" 
                        data-status="<?php echo htmlspecialchars($row['status']); ?>" 
                        data-assigned-to="<?php echo htmlspecialchars($row['assigned_to']); ?>" 
                        data-due-date="<?php echo htmlspecialchars($row['due_date']); ?>">
                    Edit
                </button>
                <?php if ($is_admin): ?>
                <button class="btn btn-danger btn-delete-task" data-id="<?php echo $row['task_id']; ?>">Delete</button>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

            <!-- Pagination Controls -->
            <nav aria-label="Page navigation">
                <ul class="pagination">
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?search=<?php echo urlencode($search); ?>&limit=<?php echo $limit; ?>&page=<?php echo $page - 1; ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?search=<?php echo urlencode($search); ?>&limit=<?php echo $limit; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?search=<?php echo urlencode($search); ?>&limit=<?php echo $limit; ?>&page=<?php echo $page + 1; ?>">Next</a>
                    </li>
                </ul>
            </nav>

            <!-- Add Task Modal -->
            <div class="modal fade" id="addTaskModal" tabindex="-1" role="dialog" aria-labelledby="addTaskModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addTaskModalLabel">Add New Task</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form id="addTaskForm" method="POST">
                                <input type="hidden" name="action" value="add_task">
                                <div class="form-group">
                                    <label for="task_name">Task Name</label>
                                    <input type="text" class="form-control" id="task_name" name="task_name" placeholder="E.g. Client Follow-Up" required>
                                </div>
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea class="form-control" id="description" name="description" placeholder="E.g. There should be a follow-up for Isaac (0706 006 230)" rows="3"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select class="form-control" id="status" name="status" required>
                                        <option value="Not Started">Not Started</option>
                                        <option value="In Progress">In Progress</option>
                                        <option value="Completed">Completed</option>
                                    </select>
                                </div>
                                <?php if ($is_admin): ?>
                                <div class="form-group">
                                    <label for="assigned_to">Assigned To</label>
                                    <select class="form-control" id="assigned_to" name="assigned_to" required>
                                        <!-- Populate with staff names -->
                                        <?php
                                        $staffQuery = $conn->query("SELECT staff_id, CONCAT(first_name, ' ', last_name) AS name FROM staff");
                                        while ($staff = $staffQuery->fetch_assoc()): ?>
                                        <option value="<?php echo $staff['staff_id']; ?>"><?php echo htmlspecialchars($staff['name']); ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <?php else: ?>
                                <input type="hidden" id="assigned_to" name="assigned_to" value="<?php echo $logged_in_staff_id; ?>">
                                <?php endif; ?>
                                <div class="form-group">
                                    <label for="due_date">Due Date</label>
                                    <input type="datetime-local" class="form-control" id="due_date" name="due_date" value="<?php echo date('Y-m-d\TH:i', strtotime('+6 hours')); ?>" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Add Task</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Task Modal -->
            <div class="modal fade" id="editTaskModal" tabindex="-1" role="dialog" aria-labelledby="editTaskModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editTaskModalLabel">Edit Task</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form id="editTaskForm" method="POST">
                                <input type="hidden" name="action" value="edit_task">
                                <input type="hidden" id="edit_task_id" name="task_id">
                                <div class="form-group">
                                    <label for="edit_task_name">Task Name</label>
                                    <input type="text" class="form-control" id="edit_task_name" name="task_name" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="edit_description">Description</label>
                                    <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="edit_status">Status</label>
                                    <select class="form-control" id="edit_status" name="status" required>
                                        <option value="Not Started">Not Started</option>
                                        <option value="In Progress">In Progress</option>
                                        <option value="Completed">Completed</option>
                                    </select>
                                </div>
                                <?php if ($is_admin): ?>
                                <div class="form-group">
                                    <label for="edit_assigned_to">Assigned To</label>
                                    <select class="form-control" id="edit_assigned_to" name="assigned_to" required>
                                        <!-- Populate with staff names -->
                                        <?php
                                        $staffQuery = $conn->query("SELECT staff_id, CONCAT(first_name, ' ', last_name) AS name FROM staff");
                                        while ($staff = $staffQuery->fetch_assoc()): ?>
                                        <option value="<?php echo $staff['staff_id']; ?>"><?php echo htmlspecialchars($staff['name']); ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <?php else: ?>
                                <input type="hidden" id="edit_assigned_to" name="assigned_to" value="<?php echo $logged_in_staff_id; ?>">
                                <?php endif; ?>
                                <div class="form-group">
                                    <label for="edit_due_date">Due Date</label>
                                    <input type="datetime-local" class="form-control" id="edit_due_date" name="due_date" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delete Task Modal -->
            <div class="modal fade" id="deleteTaskModal" tabindex="-1" role="dialog" aria-labelledby="deleteTaskModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteTaskModalLabel">Delete Task</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to delete this task?</p>
                            <form id="deleteTaskForm" method="POST">
                                <input type="hidden" name="action" value="delete_task">
                                <input type="hidden" id="delete_task_id" name="task_id">
                                <button type="submit" class="btn btn-danger">Delete</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Edit Task Button Click Event
        document.querySelectorAll('.btn-edit-task').forEach(button => {
            button.addEventListener('click', function() {
                const taskId = this.dataset.id;
                const taskName = this.dataset.name;
                const description = this.dataset.description;
                const status = this.dataset.status;
                const assignedTo = this.dataset.assignedTo;
                const dueDate = this.dataset.dueDate;

                document.getElementById('edit_task_id').value = taskId;
                document.getElementById('edit_task_name').value = taskName;
                document.getElementById('edit_description').value = description;
                document.getElementById('edit_status').value = status;
                document.getElementById('edit_assigned_to').value = assignedTo;

                // Set due date from database
                document.getElementById('edit_due_date').value = dueDate;

                $('#editTaskModal').modal('show');
            });
        });

        // Delete Task Button Click Event
        document.querySelectorAll('.btn-delete-task').forEach(button => {
            button.addEventListener('click', function() {
                const taskId = this.dataset.id;
                document.getElementById('delete_task_id').value = taskId;
                $('#deleteTaskModal').modal('show');
            });
        });
    });
</script>
<script>
    CKEDITOR.replace('description');
</script>
<?php include 'includes/footer.php'; ?>