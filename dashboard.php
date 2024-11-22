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

// Function to count leads based on lead_status
function countLeads($conn, $status) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM clients WHERE lead_status = ?");
    $stmt->bind_param("s", $status);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return $count;
}

// Retrieve lead counts
$newLeadsCount = countLeads($conn, 'New');
$pendingMeetingCount = countLeads($conn, 'Pending Meeting');
$wonLeadsCount = countLeads($conn, 'Won');
$quotationsSentCount = countLeads($conn, 'Quotations Sent');
$followUpRequiredCount = countLeads($conn, 'Follow-Up Required');
$refusedLeadsCount = countLeads($conn, 'Refused');
$dormantLeadsCount = countLeads($conn, 'Dormant');
?>

<div class="d-flex" id="wrapper">
    <?php include 'includes/side-bar.php'; ?>
    <div id="page-content-wrapper">
        <div class="container-fluid">
            <h1 class="mt-4">Dashboard</h1>
            <div class="row">
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            New Leads
                        </div>
                        <div class="card-body">
                            <p class="card-text">Number of new leads: <?php echo $newLeadsCount; ?></p>
                            <a href="new-leads.php" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header bg-warning text-white">
                            Pending Meetings
                        </div>
                        <div class="card-body">
                            <p class="card-text">Number of Pending Meetings: <?php echo $pendingMeetingCount; ?></p>
                            <a href="pending-meetings.php" class="btn bg-warning text-white">View Details</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header bg-success text-white">
                            Quotations Sent
                        </div>
                        <div class="card-body">
                            <p class="card-text">Number of Quotations sent: <?php echo $quotationsSentCount; ?></p>
                            <a href="quotations-sent.php" class="btn btn-success">View Details</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header bg-danger text-white">
                            Follow-Up Required
                        </div>
                        <div class="card-body">
                            <p class="card-text">Number of follow-ups required: <?php echo $followUpRequiredCount; ?></p>
                            <a href="follow-up-required.php" class="btn btn-danger">View Details</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header bg-dark text-white">
                            Dormant Leads
                        </div>
                        <div class="card-body">
                            <p class="card-text">Number of dormant leads: <?php echo $dormantLeadsCount; ?></p>
                            <a href="dormant-leads.php" class="btn btn-dark">View Details</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            Won Leads
                        </div>
                        <div class="card-body">
                            <p class="card-text">Number of won leads: <?php echo $wonLeadsCount; ?></p>
                            <a href="won-leads.php" class="btn btn-info">View Details</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header bg-secondary text-white">
                            Refused
                        </div>
                        <div class="card-body">
                            <p class="card-text">Number of refused leads: <?php echo $refusedLeadsCount; ?></p>
                            <a href="refused-leads.php" class="btn btn-secondary">View Details</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>