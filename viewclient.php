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

// Get the client ID from the query string
$client_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($client_id <= 0) {
    // Invalid client ID
    echo '<div class="alert alert-danger" role="alert">Invalid client ID.</div>';
    exit();
}

// Retrieve client details from the database
$stmt = $conn->prepare("SELECT * FROM clients WHERE client_id = ?");
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Client not found
    echo '<div class="alert alert-danger" role="alert">Client not found.</div>';
    exit();
}

$client = $result->fetch_assoc();
$next_follow_up = strtotime($client['next_follow_up']);
$current_time = time();
$time_difference = $next_follow_up - $current_time;

if ($time_difference > 86400) {
    // More than 24 hours left
    $bg_color = "#ccffcc"; // Green background
} elseif ($time_difference > 0) {
    // Less than 24 hours left
    $bg_color = "#ffcc99"; // Orange background
} else {
    // Follow-up time has passed
    $bg_color = "#ffcccc"; // Red background
}

// Calculate days and hours remaining
$days_remaining = floor($time_difference / 86400);
$hours_remaining = floor(($time_difference % 86400) / 3600);
$time_remaining_text = "";

if ($time_difference > 0) {
    if ($days_remaining > 0) {
        $time_remaining_text .= "$days_remaining days ";
    }
    $time_remaining_text .= "$hours_remaining hours remaining";
} else {
    $time_remaining_text = "Follow-up overdue!";
}
?>

<div class="d-flex" id="wrapper">
    <?php include 'includes/side-bar.php'; ?>
    <div id="page-content-wrapper">
        <div class="container-fluid">
            <h1 class="mt-4">Client Details</h1>
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    Client Information
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">Client ID</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($client['client_id']); ?></dd>
                        <dt class="col-sm-3">First Name</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($client['first_name']); ?></dd>
                        <dt class="col-sm-3">Last Name</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($client['last_name']); ?></dd>
                        <dt class="col-sm-3">Email</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($client['email']); ?></dd>
                        <dt class="col-sm-3">Phone Number</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($client['phone_number']); ?></dd>
                        <dt class="col-sm-3">Address</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($client['address']); ?></dd>
                        <dt class="col-sm-3">Company Name</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($client['company_name']); ?></dd>
                        <dt class="col-sm-3">Lead Source</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($client['lead_source']); ?></dd>
                        <dt class="col-sm-3">Lead Status</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($client['lead_status']); ?></dd>
                        <dt class="col-sm-3">Additional Notes</dt>
                        <dd class="col-sm-9"><?php echo nl2br(htmlspecialchars($client['additional_notes'])); ?></dd>
                        <dt class="col-sm-3">Created By</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($client['created_by']); ?></dd>
                        <dt class="col-sm-3">Created At</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($client['created_at']); ?></dd>
                        <dt class="col-sm-3">Updated At</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($client['updated_at']); ?></dd>
                        <dt class="col-sm-3">Next Follow-up</dt>
                        <dd class="col-sm-9">
                            <span style="background-color: <?php echo $bg_color; ?>; padding: 5px;">
                                <?php echo htmlspecialchars(date('Y-m-d H:i', $next_follow_up)); ?>
                                <?php if ($time_difference > 0): ?>
                                    (<?php echo $time_remaining_text; ?>)
                                <?php else: ?>
                                    <?php echo $time_remaining_text; ?>
                                <?php endif; ?>
                            </span>
                        </dd>
                    </dl>
                </div>
            </div>
            <a href="javascript:history.back()" class="btn btn-primary">Back</a>
            <a href="editclient.php?id=<?php echo $client['client_id']; ?>" class="btn btn-warning">Edit</a>
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