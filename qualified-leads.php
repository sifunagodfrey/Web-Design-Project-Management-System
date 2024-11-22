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

// Set the number of entries per page
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Retrieve the total number of qualified leads
$total_stmt = $conn->prepare("SELECT COUNT(*) as total FROM clients WHERE lead_status = 'Qualified'");
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total = $total_row['total'];
$total_pages = ceil($total / $limit);

// Retrieve qualified leads with pagination
$stmt = $conn->prepare("SELECT * FROM clients WHERE lead_status = 'Qualified' LIMIT ?, ?");
$stmt->bind_param("ii", $start, $limit);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="d-flex" id="wrapper">
    <?php include 'includes/side-bar.php'; ?>
    <div id="page-content-wrapper">
        <div class="container-fluid">
            <h1 class="mt-4">Qualified Leads</h1>
            <div class="mb-3">
                <form method="GET" action="qualified-leads.php" class="form-inline">
                    <label for="limit" class="mr-2">Show</label>
                    <select name="limit" id="limit" class="form-control mr-2" onchange="this.form.submit()">
                        <option value="5" <?php if ($limit == 5) echo 'selected'; ?>>5</option>
                        <option value="10" <?php if ($limit == 10) echo 'selected'; ?>>10</option>
                        <option value="25" <?php if ($limit == 25) echo 'selected'; ?>>25</option>
                        <option value="50" <?php if ($limit == 50) echo 'selected'; ?>>50</option>
                        <option value="100" <?php if ($limit == 100) echo 'selected'; ?>>100</option>
                    </select>
                    <label class="mr-2">entries</label>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Phone Number</th>
                            <th>Address</th>
                            <th>Lead Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['client_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                            <td><?php echo htmlspecialchars($row['address']); ?></td>
                            <td><?php echo htmlspecialchars($row['lead_status']); ?></td>
                            <td>
                                <a href="viewclient.php?id=<?php echo $row['client_id']; ?>" class="btn btn-info btn-sm">View</a>
                                <a href="editclient.php?id=<?php echo $row['client_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="deleteclient.php?id=<?php echo $row['client_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this lead?');">Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between">
                <nav>
                    <ul class="pagination">
                        <li class="page-item <?php if($page <= 1){ echo 'disabled'; } ?>">
                            <a class="page-link" href="<?php if($page <= 1){ echo '#'; } else { echo "?limit=$limit&page=".($page - 1); } ?>">Previous</a>
                        </li>
                        <?php for($i = 1; $i <= $total_pages; $i++ ): ?>
                        <li class="page-item <?php if($page == $i) {echo 'active'; } ?>">
                            <a class="page-link" href="qualified-leads.php?limit=<?php echo $limit; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>
                        <li class="page-item <?php if($page >= $total_pages) { echo 'disabled'; } ?>">
                            <a class="page-link" href="<?php if($page >= $total_pages){ echo '#'; } else { echo "?limit=$limit&page=".($page + 1); } ?>">Next</a>
                        </li>
                    </ul>
                </nav>
                <div>
                    <p>Showing <?php echo ($start + 1); ?> to <?php echo min(($start + $limit), $total); ?> of <?php echo $total; ?> entries</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$stmt->close();
$conn->close();
include 'includes/footer.php'; 
?>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
