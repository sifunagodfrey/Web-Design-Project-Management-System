<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    // Redirect to index.php if not logged in
    header("Location: index.php");
    exit();
}

include 'includes/connection.php'; // Include the database connection

// Retrieve the current logged-in user's staff_id
$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT staff_id FROM staff WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($created_by);
$stmt->fetch();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect form data
    $first_name = $_POST['firstName'];
    $last_name = $_POST['lastName'];
    $email = $_POST['email'];
    $phone_number = $_POST['phoneNumber'];
    $address = $_POST['address'];
    $company_name = $_POST['companyName'];
    $lead_source = $_POST['leadSource'];
    $additional_notes = $_POST['additionalNotes'];
    $created_at = date('Y-m-d H:i:s'); // Current timestamp
    $lead_status = 'New'; // Default status
    $next_follow_up = $_POST['nextFollowUp'];

    // Convert next_follow_up to the correct format
    $formatted_follow_up = date('Y-m-d H:i:s', strtotime($next_follow_up));

    // Prepare SQL statement to insert the new client with next_follow_up
    $stmt = $conn->prepare("INSERT INTO clients (first_name, last_name, email, phone_number, address, company_name, lead_source, additional_notes, created_by, created_at, lead_status, next_follow_up) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssssss", $first_name, $last_name, $email, $phone_number, $address, $company_name, $lead_source, $additional_notes, $created_by, $created_at, $lead_status, $formatted_follow_up);

        // add new client notification
            $_SESSION['staff_id'] = $created_by;
            $notification_title = "New Client Added";
            $notification_description = "Client $first_name $last_name was added";
            $created_at = date('Y-m-d H:i:s');

            $stmt_notification = $conn->prepare("INSERT INTO notifications (title, description, user_id, date_created) VALUES (?, ?, ?, ?)");
            $stmt_notification->bind_param("ssis", $notification_title, $notification_description, $created_by, $created_at);
            $stmt_notification->execute();
            $stmt_notification->close();
             // end add new client notification

    if ($stmt->execute()) {
        $client_id = $stmt->insert_id; // Get the inserted client's ID

        // Insert the follow-up task into the tasks table
        $task_name = "Client Follow-up";
        $description = "A follow-up on $first_name $last_name ($phone_number)";
        $status = "Not Started";
        $assigned_to = $created_by;
        $due_date = $formatted_follow_up; // Use formatted follow-up date

        $stmt_task = $conn->prepare("INSERT INTO tasks (task_name, description, status, assigned_to, due_date, created_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_task->bind_param("sssssi", $task_name, $description, $status, $assigned_to, $due_date, $created_by);
        $stmt_task->execute();
        $stmt_task->close();

        // Redirect to dashboard or display success message
        header("Location: dashboard.php");
        exit();
    } else {
        // Display error message
        $error_message = "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Client</title>
    <link rel="stylesheet" href="path/to/your/css/style.css"> <!-- Update with your CSS file path -->
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="d-flex" id="wrapper">
        <?php include 'includes/side-bar.php'; ?>
        <div id="page-content-wrapper">
            <div class="container-fluid">
                <h2 class="mt-4">Add New Client</h2>
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>
                <div class="row">
                    <div class="col-md-12">
                        <form method="post">
                            <div class="form-row mb-3">
                                <div class="col-md-3">
                                    <label for="firstName">First Name (required)</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" id="firstName" name="firstName" required>
                                </div>
                            </div>
                            <div class="form-row mb-3">
                                <div class="col-md-3">
                                    <label for="lastName">Last Name (Optional)</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" id="lastName" name="lastName">
                                </div>
                            </div>
                            <div class="form-row mb-3">
                                <div class="col-md-3">
                                    <label for="email">Email (Optional)</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="email" class="form-control" id="email" name="email">
                                </div>
                            </div>
                            <div class="form-row mb-3">
                                <div class="col-md-3">
                                    <label for="phoneNumber">Phone Number</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="tel" class="form-control" id="phoneNumber" name="phoneNumber" required>
                                </div>
                            </div>
                            <div class="form-row mb-3">
                                <div class="col-md-3">
                                    <label for="address">Address</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" id="address" name="address">
                                </div>
                            </div>
                            <div class="form-row mb-3">
                                <div class="col-md-3">
                                    <label for="companyName">Company Name</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" id="companyName" name="companyName">
                                </div>
                            </div>
                            <div class="form-row mb-3">
                                <div class="col-md-3">
                                    <label for="leadSource">Lead Source</label>
                                </div>
                                <div class="col-md-9">
                                    <select class="form-control" id="leadSource" name="leadSource">
                                        <option value="Website">Website</option>
                                        <option value="Referral">Referral</option>
                                        <option value="Advertisement">Advertisement</option>
                                        <option value="Google Search">Google Search</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-row mb-3">
                                <div class="col-md-3">
                                    <label for="additionalNotes">Additional Notes</label>
                                </div>
                                <div class="col-md-9">
                                    <textarea class="form-control" id="additionalNotes" name="additionalNotes"></textarea>
                                </div>
                            </div>
                            <div class="form-row mb-3">
                                <div class="col-md-3">
                                    <label for="nextFollowUp">Next Follow-up</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="datetime-local" class="form-control" id="nextFollowUp" name="nextFollowUp" value="<?php echo date('Y-m-d\TH:i', strtotime('+2 days')); ?>" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit</button>
                            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>
</html>