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

// Pagination and Filtering
$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$lead_status_filter = isset($_GET['lead_status']) ? $_GET['lead_status'] : '';

// Count total records
$count_sql = "SELECT COUNT(*) as total FROM clients WHERE CONCAT(first_name, ' ', last_name) LIKE ? AND lead_status LIKE ?";
$count_stmt = $conn->prepare($count_sql);
$search_param = "%$search%";
$lead_status_param = "%$lead_status_filter%";
$count_stmt->bind_param('ss', $search_param, $lead_status_param);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_rows = $count_result->fetch_assoc()['total'];
$count_stmt->close();

// Fetch filtered and paginated data (order by most recently added)
$sql = "SELECT * FROM clients WHERE CONCAT(first_name, ' ', last_name) LIKE ? AND lead_status LIKE ? ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ssii', $search_param, $lead_status_param, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="d-flex" id="wrapper">
    <?php include 'includes/side-bar.php'; ?>
    <div id="page-content-wrapper">
        <div class="container-fluid">
            <h2 class="mt-4">Manage Clients</h2>

            <!-- Search and Filter -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <form class="form-inline" method="get">
                        <input type="text" class="form-control mr-2" name="search" placeholder="Search by Client Name" value="<?php echo htmlspecialchars($search); ?>">
                        <select class="form-control mr-2" name="lead_status">
                            <option value="">All Statuses</option>
                            <option value="New" <?php echo $lead_status_filter === 'New' ? 'selected' : ''; ?>>New</option>
                            <option value="Qualified" <?php echo $lead_status_filter === 'Qualified' ? 'selected' : ''; ?>>Qualified</option>
                            <option value="Won" <?php echo $lead_status_filter === 'Won' ? 'selected' : ''; ?>>Won</option>
                            <option value="Proposals Sent" <?php echo $lead_status_filter === 'Proposals Sent' ? 'selected' : ''; ?>>Proposals Sent</option>
                            <option value="Follow-Up Required" <?php echo $lead_status_filter === 'Follow-Up Required' ? 'selected' : ''; ?>>Follow-Up Required</option>
                            <option value="Refused" <?php echo $lead_status_filter === 'Refused' ? 'selected' : ''; ?>>Refused</option>
                            <option value="Dormant" <?php echo $lead_status_filter === 'Dormant' ? 'selected' : ''; ?>>Dormant</option>
                        </select>
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </form>
                </div>
                <div class="col-md-6 text-right">
                    <form class="form-inline" method="get">
                        <label for="limit" class="mr-2">Show</label>
                        <select class="form-control mr-2" name="limit" id="limit" onchange="this.form.submit()">
                            <option value="5" <?php echo $limit == '5' ? 'selected' : ''; ?>>5</option>
                            <option value="10" <?php echo $limit == '10' ? 'selected' : ''; ?>>10</option>
                            <option value="25" <?php echo $limit == '25' ? 'selected' : ''; ?>>25</option>
                            <option value="all" <?php echo $limit == 'all' ? 'selected' : ''; ?>>All</option>
                        </select>
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                        <input type="hidden" name="lead_status" value="<?php echo htmlspecialchars($lead_status_filter); ?>">
                    </form>
                </div>
            </div>

            <!-- Clients Table -->
           <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>Client Name</th>
                            <th>Phone Number</th>
                            <th>Lead Status</th>
                            <th>Next Follow-Up</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                            <td><?php echo htmlspecialchars($row['lead_status']); ?></td>
                            <td>
                                <?php 
                                $follow_up_date = $row['next_follow_up'];
                                if ($follow_up_date < date('Y-m-d')) {
                                    echo '<span class="badge badge-danger">Overdue</span>';
                                } elseif ($follow_up_date == date('Y-m-d')) {
                                    echo '<span class="badge badge-warning">Due Today</span>';
                                } else {
                                    echo '<span class="badge badge-success">Upcoming</span>';
                                }
                                ?>
                                <?php echo htmlspecialchars($follow_up_date); ?>
                            </td>
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

            <!-- Pagination Controls -->
            <nav aria-label="Page navigation">
                <ul class="pagination">
                    <?php if ($offset > 0): ?>
                        <li class="page-item">
                            <a class="page-link" href="?search=<?php echo htmlspecialchars($search); ?>&lead_status=<?php echo htmlspecialchars($lead_status_filter); ?>&limit=<?php echo $limit; ?>&offset=<?php echo max(0, $offset - $limit); ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php
                    $total_pages = ceil($total_rows / ($limit === 'all' ? $total_rows : $limit));
                    for ($i = 0; $i < $total_pages; $i++): 
                        $page_offset = $i * ($limit === 'all' ? $total_rows : $limit);
                    ?>
                        <li class="page-item <?php echo $page_offset === $offset ? 'active' : ''; ?>">
                            <a class="page-link" href="?search=<?php echo htmlspecialchars($search); ?>&lead_status=<?php echo htmlspecialchars($lead_status_filter); ?>&limit=<?php echo $limit; ?>&offset=<?php echo $page_offset; ?>"><?php echo $i + 1; ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($offset + ($limit === 'all' ? $total_rows : $limit) < $total_rows): ?>
                        <li class="page-item">
                            <a class="page-link" href="?search=<?php echo htmlspecialchars($search); ?>&lead_status=<?php echo htmlspecialchars($lead_status_filter); ?>&limit=<?php echo $limit; ?>&offset=<?php echo $offset + ($limit === 'all' ? $total_rows : $limit); ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>

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