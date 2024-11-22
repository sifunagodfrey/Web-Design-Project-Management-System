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
    <title>Edit Proposal</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Include CKEditor -->
    <script src="https://cdn.ckeditor.com/ckeditor5/ckeditor.js"></script>
    <style>
        .proposal-details {
            margin-top: 20px;
        }
        .form-control {
            width: 100%;
        }
        .container-fluid {
            width: 100%;
        }
        .card-header h5 {
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="d-flex" id="wrapper">
        <?php include 'includes/side-bar.php'; ?>
        <div id="page-content-wrapper">
            <div class="container-fluid">
                <!-- Proposal Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">Edit Proposal</h5>
                    </div>
                    <div class="card-body">
                        <form>
                            <!-- Proposal Type Selection -->
                            <div class="form-group">
                                <label for="proposalType">Proposal Type</label>
                                <select class="form-control" id="proposalType" onchange="toggleProposalDetails()">
                                    <option value="" disabled>Select Proposal Type</option>
                                    <option value="web-design">Web Design</option>
                                    <option value="digital-marketing">Digital Marketing</option>
                                </select>
                            </div>

                            <!-- Web Design Details -->
                            <div id="webDesignDetails" class="proposal-details" style="display: none;">
                                <h6 class="font-weight-bold">Web Design Proposal Details</h6>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="designProject">Project Name</label>
                                        <input type="text" class="form-control" id="designProject" placeholder="Enter Project Name" value="Existing Project Name">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="designClient">Client Name</label>
                                        <input type="text" class="form-control" id="designClient" placeholder="Enter Client Name" value="Existing Client Name">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="designDescription">Project Description</label>
                                    <textarea id="designDescription" name="designDescription" class="form-control" rows="10">Existing Project Description</textarea>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="designTimeline">Timeline</label>
                                        <input type="text" class="form-control" id="designTimeline" placeholder="Enter Timeline" value="Existing Timeline">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="designBudget">Estimated Budget</label>
                                        <input type="text" class="form-control" id="designBudget" placeholder="Enter Budget" value="Existing Budget">
                                    </div>
                                </div>
                            </div>

                            <!-- Digital Marketing Details -->
                            <div id="digitalMarketingDetails" class="proposal-details" style="display: none;">
                                <h6 class="font-weight-bold">Digital Marketing Proposal Details</h6>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="marketingCampaign">Campaign Name</label>
                                        <input type="text" class="form-control" id="marketingCampaign" placeholder="Enter Campaign Name" value="Existing Campaign Name">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="marketingClient">Client Name</label>
                                        <input type="text" class="form-control" id="marketingClient" placeholder="Enter Client Name" value="Existing Client Name">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="marketingObjectives">Campaign Objectives</label>
                                    <textarea id="marketingObjectives" name="marketingObjectives" class="form-control" rows="10">Existing Campaign Objectives</textarea>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="marketingTimeline">Timeline</label>
                                        <input type="text" class="form-control" id="marketingTimeline" placeholder="Enter Timeline" value="Existing Timeline">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="marketingBudget">Estimated Budget</label>
                                        <input type="text" class="form-control" id="marketingBudget" placeholder="Enter Budget" value="Existing Budget">
                                    </div>
                                </div>
                            </div>

                            <!-- Common Details -->
                            <div class="form-group">
                                <label for="proposalDate">Proposal Date</label>
                                <input type="date" class="form-control" id="proposalDate" value="2024-08-10">
                            </div>
                            <div class="form-group">
                                <label for="proposalDueDate">Due Date</label>
                                <input type="date" class="form-control" id="proposalDueDate" value="2024-08-15">
                            </div>

                            <!-- Save Proposal -->
                            <button type="submit" class="btn btn-primary">Save Proposal</button>
                            <a href="resources.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Function to toggle proposal details based on the selected type
        function toggleProposalDetails() {
            const proposalType = document.getElementById('proposalType').value;
            document.getElementById('webDesignDetails').style.display = (proposalType === 'web-design') ? 'block' : 'none';
            document.getElementById('digitalMarketingDetails').style.display = (proposalType === 'digital-marketing') ? 'block' : 'none';
        }

        // Initialize CKEditor for Web Design Description
        ClassicEditor
            .create(document.querySelector('#designDescription'), {
                toolbar: ['bold', 'italic', 'heading', '|', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'undo', 'redo']
            })
            .catch(error => {
                console.error(error);
            });

        // Initialize CKEditor for Digital Marketing Objectives
        ClassicEditor
            .create(document.querySelector('#marketingObjectives'), {
                toolbar: ['bold', 'italic', 'heading', '|', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'undo', 'redo']
            })
            .catch(error => {
                console.error(error);
            });

        // Pre-select the proposal type and display relevant details
        document.addEventListener('DOMContentLoaded', () => {
            const proposalType = document.getElementById('proposalType').value;
            if (proposalType) {
                toggleProposalDetails();
            }
        });
    </script>
</body>
</html>
