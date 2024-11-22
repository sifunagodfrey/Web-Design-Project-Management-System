<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

include 'includes/header.php'; 
include 'includes/connection.php'; // Include the database connection

// Check if the user is admin
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$logged_in_staff_id = $_SESSION['staff_id'];

// Handle form submissions for adding, editing, and deleting expenses
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_expense':
                $expense_name = $_POST['expense_name'];
                $amount = $_POST['amount'];
                $date = $_POST['date'];
                $description = $_POST['description'];
                $created_by = $logged_in_staff_id; // Assuming the logged-in user's staff_id is stored in the session

                $stmt = $conn->prepare("INSERT INTO expenses (expense_name, amount, date, description, created_by) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sdsss", $expense_name, $amount, $date, $description, $created_by);
                $stmt->execute();
                $stmt->close();
                break;

            case 'edit_expense':
                $expense_id = $_POST['expense_id'];
                $expense_name = $_POST['expense_name'];
                $amount = $_POST['amount'];
                $date = $_POST['date'];
                $description = $_POST['description'];

                $stmt = $conn->prepare("UPDATE expenses SET expense_name = ?, amount = ?, date = ?, description = ? WHERE expense_id = ?");
                $stmt->bind_param("sdssi", $expense_name, $amount, $date, $description, $expense_id);
                $stmt->execute();
                $stmt->close();
                break;

            case 'delete_expense':
                $expense_id = $_POST['expense_id'];

                $stmt = $conn->prepare("DELETE FROM expenses WHERE expense_id = ?");
                $stmt->bind_param("i", $expense_id);
                $stmt->execute();
                $stmt->close();
                break;
        }
    }
}

// Pagination and filtering logic
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? $_GET['search'] : '';
$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : 'all';

// Define the date condition based on the selected filter
$date_condition = '';
if ($date_filter === 'this_week') {
    $date_condition = "AND date >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)";
} elseif ($date_filter === 'last_two_weeks') {
    $date_condition = "AND date >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)";
} elseif ($date_filter === 'last_month') {
    $date_condition = "AND date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
}

// Fetch total record count for pagination with date filter
$totalQuery = $conn->prepare("SELECT COUNT(*) FROM expenses WHERE expense_name LIKE ? $date_condition");
$searchParam = "%$search%";
$totalQuery->bind_param("s", $searchParam);
$totalQuery->execute();
$totalResult = $totalQuery->get_result();
$totalRecords = $totalResult->fetch_row()[0];
$totalPages = ceil($totalRecords / $limit);

// Fetch expenses with date filter and order by date descending
$expensesQuery = $conn->prepare("SELECT * FROM expenses WHERE expense_name LIKE ? $date_condition ORDER BY date DESC LIMIT ? OFFSET ?");
$expensesQuery->bind_param("sii", $searchParam, $limit, $offset);
$expensesQuery->execute();
$expenses = $expensesQuery->get_result();
?>

<div class="d-flex" id="wrapper">
    <?php include 'includes/side-bar.php'; ?>
    <div id="page-content-wrapper">
        <div class="container-fluid">
            <h1 class="mt-4">Manage Expenses</h1>

            <!-- Add New Expense Button -->
            <button class="btn btn-primary mb-4" data-toggle="modal" data-target="#addExpenseModal">Add New Expense</button>

            <!-- Filter Form -->
            <form method="GET" class="mb-4">
                <div class="form-row">
                    <div class="col-md-4 mb-2">
                        <input type="text" class="form-control" name="search" placeholder="Search expense name" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-4 mb-2">
                        <select class="form-control" name="date_filter">
                            <option value="all" <?php echo $date_filter == 'all' ? 'selected' : ''; ?>>All Dates</option>
                            <option value="this_week" <?php echo $date_filter == 'this_week' ? 'selected' : ''; ?>>This Week</option>
                            <option value="last_two_weeks" <?php echo $date_filter == 'last_two_weeks' ? 'selected' : ''; ?>>Last Two Weeks</option>
                            <option value="last_month" <?php echo $date_filter == 'last_month' ? 'selected' : ''; ?>>Last Month</option>
                        </select>
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

            <!-- Expenses Table -->
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Expense Name</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $expenses->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['expense_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['amount']); ?></td>
                        <td><?php echo htmlspecialchars($row['date']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td>
                            <button class="btn btn-warning btn-edit-expense" data-id="<?php echo $row['expense_id']; ?>" data-name="<?php echo htmlspecialchars($row['expense_name']); ?>" data-amount="<?php echo htmlspecialchars($row['amount']); ?>" data-date="<?php echo htmlspecialchars($row['date']); ?>" data-description="<?php echo htmlspecialchars($row['description']); ?>">Edit</button>
                            
                            <?php if ($is_admin): ?> 
                                <button class="btn btn-danger btn-delete-expense" data-id="<?php echo $row['expense_id']; ?>">Delete</button> 
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
                        <a class="page-link" href="?search=<?php echo urlencode($search); ?>&date_filter=<?php echo $date_filter; ?>&limit=<?php echo $limit; ?>&page=<?php echo $page - 1; ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?search=<?php echo urlencode($search); ?>&date_filter=<?php echo $date_filter; ?>&limit=<?php echo $limit; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?search=<?php echo urlencode($search); ?>&date_filter=<?php echo $date_filter; ?>&limit=<?php echo $limit; ?>&page=<?php echo $page + 1; ?>">Next</a>
                    </li>
                </ul>
            </nav>

            <!-- Add Expense Modal -->
            <div class="modal fade" id="addExpenseModal" tabindex="-1" role="dialog" aria-labelledby="addExpenseModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addExpenseModalLabel">Add New Expense</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form id="addExpenseForm" method="POST">
                                <div class="form-group">
                                    <label for="expense_name">Expense Name</label>
                                    <input type="text" class="form-control" id="expense_name" name="expense_name" required>
                                </div>
                                <div class="form-group">
                                    <label for="amount">Amount</label>
                                    <input type="number" class="form-control" id="amount" name="amount" required>
                                </div>
                                <div class="form-group">
                                    <label for="date">Date</label>
                                    <input type="date" class="form-control" id="date" name="date" required>
                                </div>
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea class="form-control" id="description" name="description"></textarea>
                                </div>
                                <input type="hidden" name="action" value="add_expense">
                                <button type="submit" class="btn btn-primary">Add Expense</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Expense Modal -->
            <div class="modal fade" id="editExpenseModal" tabindex="-1" role="dialog" aria-labelledby="editExpenseModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editExpenseModalLabel">Edit Expense</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form id="editExpenseForm" method="POST">
                                <div class="form-group">
                                    <label for="edit_expense_name">Expense Name</label>
                                    <input type="text" class="form-control" id="edit_expense_name" name="expense_name" required>
                                </div>
                                <div class="form-group">
                                    <label for="edit_amount">Amount</label>
                                    <input type="number" class="form-control" id="edit_amount" name="amount" required>
                                </div>
                                <div class="form-group">
                                    <label for="edit_date">Date</label>
                                    <input type="date" class="form-control" id="edit_date" name="date" required>
                                </div>
                                <div class="form-group">
                                    <label for="edit_description">Description</label>
                                    <textarea class="form-control" id="edit_description" name="description"></textarea>
                                </div>
                                <input type="hidden" id="edit_expense_id" name="expense_id">
                                <input type="hidden" name="action" value="edit_expense">
                                <button type="submit" class="btn btn-primary">Update Expense</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<script>
    // JavaScript for edit button
    document.querySelectorAll('.btn-edit-expense').forEach(button => {
        button.addEventListener('click', () => {
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            const amount = button.getAttribute('data-amount');
            const date = button.getAttribute('data-date');
            const description = button.getAttribute('data-description');

            document.getElementById('edit_expense_id').value = id;
            document.getElementById('edit_expense_name').value = name;
            document.getElementById('edit_amount').value = amount;
            document.getElementById('edit_date').value = date;
            document.getElementById('edit_description').value = description;

            $('#editExpenseModal').modal('show');
        });
    });

    // JavaScript for delete confirmation
    document.querySelectorAll('.btn-delete-expense').forEach(button => {
        button.addEventListener('click', () => {
            const id = button.getAttribute('data-id');
            if (confirm('Are you sure you want to delete this expense?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'expense_id';
                input.value = id;
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete_expense';
                form.appendChild(input);
                form.appendChild(actionInput);
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
</script>