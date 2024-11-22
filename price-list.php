<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    // Redirect to index.php if not logged in
    header("Location: index.php");
    exit();
}

$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

include 'includes/header.php';
include 'includes/connection.php'; // Include the database connection

// Handle form submission for updating service details
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    // Check if the user is an admin
    if ($is_admin) {
        $id = $_POST['id'];
        $service_name = $_POST['service_name'];
        $min_price = round($_POST['min_price']);
        $max_price = round($_POST['max_price']);
        $discount = $_POST['discount'];

        $sql = "UPDATE price_list SET service_name = ?, min_price = ?, max_price = ?, discount = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('siiii', $service_name, $min_price, $max_price, $discount, $id);

        if ($stmt->execute()) {
            $success_message = "Service updated successfully!";
        } else {
            $error_message = "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $error_message = "You cannot edit this.";
    }
}

// Handle search and filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';

// Pagination settings
$limit = 10; // Number of records per page
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch filtered services
$sql = "SELECT * FROM price_list WHERE service_name LIKE ? LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$search_param = "%" . $search . "%";
$stmt->bind_param('sii', $search_param, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Fetch total number of records for pagination
$total_sql = "SELECT COUNT(*) AS total FROM price_list WHERE service_name LIKE ?";
$total_stmt = $conn->prepare($total_sql);
$total_stmt->bind_param('s', $search_param);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $limit);

?>

<div class="d-flex" id="wrapper">
    <?php include 'includes/side-bar.php'; ?>
    <div id="page-content-wrapper">
        <div class="container-fluid">
            <h2 class="mt-4">Price List</h2>

            <!-- Display success or error messages -->
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <!-- Search and Filter Form -->
            <form method="GET" action="">
                <div class="form-row align-items-center">
                    <div class="col-auto">
                        <input type="text" class="form-control mb-2" name="search" placeholder="Search by service name" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-auto">
                        <select class="form-control mb-2" name="filter">
                            <option value="">All</option>
                            <option value="10" <?php if ($filter === '10') echo 'selected'; ?>>10% Discount</option>
                            <option value="20" <?php if ($filter === '20') echo 'selected'; ?>>20% Discount</option>
                            <option value="50" <?php if ($filter === '50') echo 'selected'; ?>>50% Discount</option>
                            <option value="75" <?php if ($filter === '75') echo 'selected'; ?>>75% Discount</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary mb-2">Search</button>
                    </div>
                </div>
            </form>

            <!-- Services Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>Service Name</th>
                            <th>Price Range</th>
                            <th>Discount</th>
                            <?php if ($is_admin): ?>
                                <th>Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['service_name']); ?></td>
                            <td><?php echo 'Ksh ' . number_format($row['min_price']) . ' - ' . number_format($row['max_price']); ?></td>
                            <td><?php echo htmlspecialchars($row['discount']); ?>%</td>
                            <?php if ($is_admin): ?>
                                <td>
                                    <button class="btn btn-warning btn-sm" onclick="openEditModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['service_name']); ?>', <?php echo $row['min_price']; ?>, <?php echo $row['max_price']; ?>, '<?php echo $row['discount']; ?>')">Edit</button>
                                </td>
                            <?php endif; ?>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Links -->
            <nav>
                <ul class="pagination">
                    <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo htmlspecialchars($search); ?>&filter=<?php echo htmlspecialchars($filter); ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($search); ?>&filter=<?php echo htmlspecialchars($filter); ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php if ($page >= $total_pages) echo 'disabled'; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo htmlspecialchars($search); ?>&filter=<?php echo htmlspecialchars($filter); ?>">Next</a>
                    </li>
                </ul>
            </nav>

        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editForm" method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Service</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editId" name="id">
                    <div class="form-group">
                        <label for="service_name">Service Name</label>
                        <input type="text" class="form-control" id="service_name" name="service_name" required>
                    </div>
                    <div class="form-group">
                        <label for="min_price">Minimum Price</label>
                        <input type="number" class="form-control" id="min_price" name="min_price" required>
                    </div>
                    <div class="form-group">
                        <label for="max_price">Maximum Price</label>
                        <input type="number" class="form-control" id="max_price" name="max_price" required>
                    </div>
                    <div class="form-group">
                        <label for="discount">Discount</label>
                        <select class="form-control" id="discount" name="discount">
                            <option value="">None</option>
                            <option value="10">10%</option>
                            <option value="20">20%</option>
                            <option value="50">50%</option>
                            <option value="75">75%</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openEditModal(id, serviceName, minPrice, maxPrice, discount) {
        document.getElementById('editId').value = id;
        document.getElementById('service_name').value = serviceName;
        document.getElementById('min_price').value = minPrice;
        document.getElementById('max_price').value = maxPrice;
        document.getElementById('discount').value = discount;

        $('#editModal').modal('show');
    }
</script>

<?php
include 'includes/footer.php';
?>