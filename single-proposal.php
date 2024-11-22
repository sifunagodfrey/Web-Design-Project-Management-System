<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    // Redirect to index.php if not logged in
    header("Location: index.php");
    exit();
} 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Single Proposal</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.ckeditor.com/ckeditor5/ckeditor.js"></script>
    <style>
        .proposal-header, .proposal-footer {
            margin-bottom: 20px;
        }
        .proposal-details {
            margin-top: 20px;
        }
        .proposal-actions {
            margin-top: 20px;
        }
        .btn-custom {
            margin-right: 10px;
        }
        .container-fluid {
            width: 100%;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="d-flex" id="wrapper">
        <?php include 'includes/side-bar.php'; ?>
        <div id="page-content-wrapper">
            <div class="container-fluid">
                <h1 class="mt-4">Proposal Details</h1>

                <!-- Proposal Header -->
                <div class="card proposal-header">
                    <div class="card-body">
                        <h4 class="card-title">Proposal Title</h4>
                        <p class="card-text"><strong>Client:</strong> Client Name</p>
                        <p class="card-text"><strong>Proposal Date:</strong> 2024-08-15</p>
                        <p class="card-text"><strong>Due Date:</strong> 2024-08-30</p>
                        <p class="card-text"><strong>Prepared By:</strong> Your Name</p>
                        <p class="card-text"><strong>Contact Information:</strong> your.email@example.com</p>
                    </div>
                </div>

                <!-- Proposal Details -->
                <div class="card proposal-details">
                    <div class="card-body">
                        <h5 class="card-title">Proposal Description</h5>
                        <div id="proposalDescription">
                            <p>This is where the detailed proposal description will be displayed. It should contain all relevant details and information regarding the proposal.</p>
                        </div>
                    </div>
                </div>

                <!-- Project Scope -->
                <div class="card proposal-details">
                    <div class="card-body">
                        <h5 class="card-title">Project Scope</h5>
                        <p>Detail the scope of the project, including deliverables, milestones, and timelines.</p>
                    </div>
                </div>

                <!-- Pricing -->
                <div class="card proposal-details">
                    <div class="card-body">
                        <h5 class="card-title">Pricing</h5>
                        <p>Outline the pricing structure, including any breakdown of costs and payment terms.</p>
                    </div>
                </div>

                <!-- Terms and Conditions -->
                <div class="card proposal-details">
                    <div class="card-body">
                        <h5 class="card-title">Terms and Conditions</h5>
                        <p>Provide the terms and conditions related to the proposal, including payment terms, project changes, and other contractual obligations.</p>
                    </div>
                </div>

                <!-- Proposal Footer -->
                <div class="card proposal-footer">
                    <div class="card-body">
                        <h5 class="card-title">Footer</h5>
                        <p><strong>Company Details:</strong></p>
                        <p>Wenamax<br>123 Business Street<br>Ongata Rongai, Kenya<br>Email: contact@wenamax.com<br>Phone: +254 123 456 789</p>
                    </div>
                </div>

                <!-- Proposal Actions -->
                <div class="proposal-actions">
                    <a href="#" class="btn btn-primary btn-custom" id="downloadProposal">Download</a>
                    <a href="#" class="btn btn-success btn-custom" id="sendProposalEmail">Send to Email</a>
                    <a href="edit-proposal.php" class="btn btn-warning btn-custom">Edit</a>
                    <a href="#" class="btn btn-danger btn-custom" id="deleteProposal">Delete</a>
                </div>
            </div>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="script.js"></script>
    <script>
        // Example functions for button actions
        document.getElementById('downloadProposal').addEventListener('click', function(event) {
            event.preventDefault();
            alert('Download functionality to be implemented.');
        });

        document.getElementById('sendProposalEmail').addEventListener('click', function(event) {
            event.preventDefault();
            alert('Send to Email functionality to be implemented.');
        });

        document.getElementById('deleteProposal').addEventListener('click', function(event) {
            event.preventDefault();
            if (confirm('Are you sure you want to delete this proposal?')) {
                alert('Delete functionality to be implemented.');
            }
        });
    </script>
</body>
</html>
