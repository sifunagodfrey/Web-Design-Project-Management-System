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

// Get the client ID from the URL
$client_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Retrieve the client's details from the database
$stmt = $conn->prepare("SELECT * FROM clients WHERE client_id = ?");
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();
$client = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    $address = $_POST['address'];
    $company_name = $_POST['company_name'];
    $lead_source = $_POST['lead_source'];
    $lead_status = $_POST['lead_status'];
    $new_note = $_POST['new_note']; // Get the new note from the form
    $next_follow_up = $_POST['next_follow_up']; // Get the next follow-up date from the form
    $edited_by = $_SESSION['email']; // Get the user who is editing
    $staff_name = isset($_SESSION['first_name']) ? $_SESSION['first_name'] : 'Unknown Staff';

        date_default_timezone_set('Africa/Nairobi');

        // Update additional notes with old notes
        $current_notes = $client['additional_notes'];

        if (!empty($new_note)) {
            $timestamp = date('F j, Y \a\t g:i A');
            
            // Append staff name, timestamp, and new note
            $additional_notes = !empty($current_notes) 
                ? $current_notes . "\n" . $timestamp . " [" . $staff_name . "] : " . $new_note 
                : $timestamp . " - Added by " . $staff_name . ": " . $new_note;
        } else {
            $additional_notes = $current_notes;
        }


    // Update the client details in the database
    $stmt = $conn->prepare("UPDATE clients SET first_name = ?, last_name = ?, email = ?, phone_number = ?, address = ?, company_name = ?, lead_source = ?, lead_status = ?, additional_notes = ?, next_follow_up = ?, updated_at = NOW(), edited_by = ? WHERE client_id = ?");
    $stmt->bind_param("sssssssssssi", $first_name, $last_name, $email, $phone_number, $address, $company_name, $lead_source, $lead_status, $additional_notes, $next_follow_up, $edited_by, $client_id);
    $stmt->execute();

    // Redirect to the view client page
    header("Location: viewclient.php?id=" . $client_id);
    exit();
}

$stmt->close();
$conn->close();
?>

<div class="d-flex" id="wrapper">
    <?php include 'includes/side-bar.php'; ?>
    <div id="page-content-wrapper">
        <div class="container-fluid">
            <h1 class="mt-4">Edit Client</h1>
            <form method="POST">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($client['first_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($client['last_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($client['email']); ?>">
                </div>
                <div class="form-group">
                    <label for="phone_number">Phone Number</label>
                    <input type="text" class="form-control" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($client['phone_number']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($client['address']); ?>">
                </div>
                <div class="form-group">
                    <label for="company_name">Company Name</label>
                    <input type="text" class="form-control" id="company_name" name="company_name" value="<?php echo htmlspecialchars($client['company_name']); ?>">
                </div>
                <div class="form-group">
                    <label for="lead_source">Lead Source</label>
                    <select class="form-control" id="lead_source" name="lead_source">
                        <option value="Website" <?php echo ($client['lead_source'] == 'Website') ? 'selected' : ''; ?>>Website</option>
                        <option value="Referral" <?php echo ($client['lead_source'] == 'Referral') ? 'selected' : ''; ?>>Referral</option>
                        <option value="Social Media" <?php echo ($client['lead_source'] == 'Social Media') ? 'selected' : ''; ?>>Social Media</option>
                        <option value="Other" <?php echo ($client['lead_source'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="lead_status">Lead Status</label>
                    <select class="form-control" id="lead_status" name="lead_status">
                        <option value="New" <?php echo ($client['lead_status'] == 'New') ? 'selected' : ''; ?>>New</option>
                        <option value="Pending Meeting" <?php echo ($client['lead_status'] == 'Pending Meeting') ? 'selected' : ''; ?>>Pending Meeting</option>
                        <option value="Won" <?php echo ($client['lead_status'] == 'Won') ? 'selected' : ''; ?>>Won</option>
                        <option value="Quotations Sent" <?php echo ($client['lead_status'] == 'Quotations Sent') ? 'selected' : ''; ?>>Quotations Sent</option>
                        <option value="Follow-Up Required" <?php echo ($client['lead_status'] == 'Follow-Up Required') ? 'selected' : ''; ?>>Follow-Up Required</option>
                        <option value="Refused" <?php echo ($client['lead_status'] == 'Refused') ? 'selected' : ''; ?>>Refused</option>
                        <option value="Dormant" <?php echo ($client['lead_status'] == 'Dormant') ? 'selected' : ''; ?>>Dormant</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="current_notes">Current Notes</label>
                    <textarea class="form-control" id="current_notes" rows="6" style="width: 100%;" readonly><?php echo htmlspecialchars($client['additional_notes']); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="new_note">Add New Note</label>
                    <textarea class="form-control" id="new_note" name="new_note" rows="4" style="width: 100%;"></textarea>
                </div>
                <div class="form-group">
                    <label for="next_follow_up">Next Follow-Up</label>
                    <input type="datetime-local" class="form-control" id="next_follow_up" name="next_follow_up" value="<?php echo htmlspecialchars(date('Y-m-d\TH:i', strtotime($client['next_follow_up']))); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="viewclient.php?id=<?php echo $client_id; ?>" class="btn btn-secondary">Back</a>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>