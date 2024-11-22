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

// Handle project creation, editing, and deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    if ($action == 'create_project') {
        $projectName = $_POST['project_name'];
        $description = $_POST['description'];
        $status = $_POST['status'];
        $startDate = $_POST['start_date'];
        $endDate = $_POST['end_date'];
        $createdBy = $_SESSION['staff_id'];
        
        $createQuery = $conn->prepare("INSERT INTO projects (project_name, description, status, start_date, end_date, created_by) VALUES (?, ?, ?, ?, ?, ?)");
        $createQuery->bind_param("sssssi", $projectName, $description, $status, $startDate, $endDate, $createdBy);
        $createQuery->execute();
        
    } elseif ($action == 'edit_project') {
        $projectId = $_POST['projectId'];
        $projectName = $_POST['project_name'];
        $description = $_POST['description'];
        $status = $_POST['status'];
        $startDate = $_POST['start_date'];
        $endDate = $_POST['end_date'];
        
        $editQuery = $conn->prepare("UPDATE projects SET project_name = ?, description = ?, status = ?, start_date = ?, end_date = ? WHERE project_id = ?");
        $editQuery->bind_param("sssssi", $projectName, $description, $status, $startDate, $endDate, $projectId);
        $editQuery->execute();
        
    } elseif ($action == 'delete_project') {
        $projectId = $_POST['project_id'];
        
        $deleteQuery = $conn->prepare("DELETE FROM projects WHERE project_id = ?");
        $deleteQuery->bind_param("i", $projectId);
        $deleteQuery->execute();
    }
}

// Pagination and filtering logic
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? $_GET['search'] : '';

// Fetch total record count for pagination
$totalQuery = $conn->prepare("SELECT COUNT(*) FROM projects WHERE project_name LIKE ?");
$searchParam = "%$search%";
$totalQuery->bind_param("s", $searchParam);
$totalQuery->execute();
$totalResult = $totalQuery->get_result();
$totalRecords = $totalResult->fetch_row()[0];
$totalPages = ceil($totalRecords / $limit);

$projectsQuery = $conn->prepare("SELECT * FROM projects WHERE project_name LIKE ? LIMIT ? OFFSET ?");
$projectsQuery->bind_param("sii", $searchParam, $limit, $offset);
$projectsQuery->execute();
$projects = $projectsQuery->get_result();

?>

<div class="d-flex" id="wrapper">
    <?php include 'includes/side-bar.php'; ?>
    <div id="page-content-wrapper">
        <div class="container-fluid">
            <h1 class="mt-4">Manage Projects</h1>

            <!-- Filter Form -->
            <form method="GET" class="mb-4">
                <div class="form-row">
                    <div class="col-md-4 mb-2">
                        <input type="text" class="form-control" name="search" placeholder="Search projects" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-4 mb-2">
                        <select class="form-control" name="limit">
                            <option value="5" <?php echo $limit == 5 ? 'selected' : ''; ?>>5</option>
                            <option value="10" <?php echo $limit == 10 ? 'selected' : ''; ?>>10</option>
                            <option value="25" <?php echo $limit == 25 ? 'selected' : ''; ?>>25</option>
                            <option value="50" <?php echo $limit == 50 ? 'selected' : ''; ?>>50</option>
                            <option value="100" <?php echo $limit == 100 ? 'selected' : ''; ?>>100</option>
                            <option value="all" <?php echo $limit == 'all' ? 'selected' : ''; ?>>All</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </div>
                </div>
            </form>

            <!-- Projects Table -->
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Project Name</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $projects->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['project_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td><?php echo htmlspecialchars($row['start_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['end_date']); ?></td>
                        <td>
                            <button class="btn btn-warning btn-edit-project" data-id="<?php echo $row['project_id']; ?>" data-name="<?php echo htmlspecialchars($row['project_name']); ?>" data-description="<?php echo htmlspecialchars($row['description']); ?>" data-status="<?php echo htmlspecialchars($row['status']); ?>" data-start-date="<?php echo htmlspecialchars($row['start_date']); ?>" data-end-date="<?php echo htmlspecialchars($row['end_date']); ?>">Edit</button>
                            <button class="btn btn-danger btn-delete-project" data-id="<?php echo $row['project_id']; ?>">Delete</button>
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

            <!-- Create Project Button -->
            <button class="btn btn-primary" data-toggle="modal" data-target="#createProjectModal">Create New Project</button>
            
            <!-- Create Project Modal -->
            <div class="modal fade" id="createProjectModal" tabindex="-1" role="dialog" aria-labelledby="createProjectModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="createProjectModalLabel">Create Project</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form method="POST">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="projectName">Project Name</label>
                                    <input type="text" class="form-control" id="projectName" name="project_name" required>
                                </div>
                                <div class="form-group">
                                    <label for="projectDescription">Description</label>
                                    <textarea class="form-control" id="projectDescription" name="description"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="projectStatus">Status</label>
                                    <select class="form-control" id="projectStatus" name="status" required>
                                        <option value="Not Started">Not Started</option>
                                        <option value="In Progress">In Progress</option>
                                        <option value="Completed">Completed</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="startDate">Start Date</label>
                                    <input type="date" class="form-control" id="startDate" name="start_date" required>
                                </div>
                                <div class="form-group">
                                    <label for="endDate">End Date</label>
                                    <input type="date" class="form-control" id="endDate" name="end_date" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary" name="action" value="create_project">Create</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Edit Project Modal -->
            <div class="modal fade" id="editProjectModal" tabindex="-1" role="dialog" aria-labelledby="editProjectModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editProjectModalLabel">Edit Project</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form method="POST">
                            <input type="hidden" id="editProjectId" name="projectId">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="editProjectName">Project Name</label>
                                    <input type="text" class="form-control" id="editProjectName" name="project_name" required>
                                </div>
                                <div class="form-group">
                                    <label for="editProjectDescription">Description</label>
                                    <textarea class="form-control" id="editProjectDescription" name="description"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="editProjectStatus">Status</label>
                                    <select class="form-control" id="editProjectStatus" name="status" required>
                                        <option value="Not Started">Not Started</option>
                                        <option value="In Progress">In Progress</option>
                                        <option value="Completed">Completed</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="editStartDate">Start Date</label>
                                    <input type="date" class="form-control" id="editStartDate" name="start_date" required>
                                </div>
                                <div class="form-group">
                                    <label for="editEndDate">End Date</label>
                                    <input type="date" class="form-control" id="editEndDate" name="end_date" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary" name="action" value="edit_project">Save changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Delete Project Modal -->
            <div class="modal fade" id="deleteProjectModal" tabindex="-1" role="dialog" aria-labelledby="deleteProjectModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteProjectModalLabel">Delete Project</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form method="POST">
                            <input type="hidden" id="deleteProjectId" name="project_id">
                            <div class="modal-body">
                                <p>Are you sure you want to delete this project?</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger" name="action" value="delete_project">Delete</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.btn-edit-project').forEach(button => {
        button.addEventListener('click', function() {
            const projectId = this.getAttribute('data-id');
            const projectName = this.getAttribute('data-name');
            const description = this.getAttribute('data-description');
            const status = this.getAttribute('data-status');
            const startDate = this.getAttribute('data-start-date');
            const endDate = this.getAttribute('data-end-date');

            document.getElementById('editProjectId').value = projectId;
            document.getElementById('editProjectName').value = projectName;
            document.getElementById('editProjectDescription').value = description;
            document.getElementById('editProjectStatus').value = status;
            document.getElementById('editStartDate').value = startDate;
            document.getElementById('editEndDate').value = endDate;

            $('#editProjectModal').modal('show');
        });
    });

    document.querySelectorAll('.btn-delete-project').forEach(button => {
        button.addEventListener('click', function() {
            const projectId = this.getAttribute('data-id');
            document.getElementById('deleteProjectId').value = projectId;
            $('#deleteProjectModal').modal('show');
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
</div>
