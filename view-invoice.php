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
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>View Invoice</title>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="d-flex" id="wrapper">
        <?php include 'includes/side-bar.php'; ?>
        <div id="page-content-wrapper">
            <div class="container-fluid">
                <h1 class="mt-4">View Invoice</h1>

                <!-- Invoice Header -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">Invoice #INV-001</h5>
                        <div class="card-actions">
                            <a href="edit-invoice.php?invoiceID=INV-001" class="btn btn-warning btn-sm">Edit</a>
                            <a href="javascript:void(0);" class="btn btn-danger btn-sm" onclick="deleteInvoice()">Delete</a>
                            <select class="form-control form-control-sm d-inline-block w-auto" id="payment-status">
                                <option value="unpaid" selected>Change Payment Status</option>
                                <option value="paid">Paid</option>
                                <option value="pending">Pending</option>
                            </select>
                            <button class="btn btn-success btn-sm ml-2" onclick="saveChanges()">Save</button>
                            <button class="btn btn-primary btn-sm ml-2" onclick="downloadInvoice()">Download</button>
                            <button class="btn btn-info btn-sm ml-2" onclick="sendEmail()">Send to Email</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="font-weight-bold">Company Details</h6>
                                <p>
                                    <strong>Wenamax</strong><br>
                                    123 Business Rd.<br>
                                    Ongata Rongai, Kenya<br>
                                    Email: info@wenamax.com<br>
                                    Phone: +254 700 000 000
                                </p>
                            </div>
                            <div class="col-md-6 text-right">
                                <h6 class="font-weight-bold">Client Details</h6>
                                <p>
                                    <strong>John Doe</strong><br>
                                    456 Client St.<br>
                                    Nairobi, Kenya<br>
                                    Email: john.doe@example.com<br>
                                    Phone: +254 711 111 111
                                </p>
                            </div>
                        </div>

                        <h6 class="font-weight-bold">Invoice Details</h6>
                        <p>
                            <strong>Invoice Date:</strong> August 10, 2024<br>
                            <strong>Due Date:</strong> September 10, 2024
                        </p>

                        <h6 class="font-weight-bold">Invoice Items</h6>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Website Design</td>
                                    <td>1</td>
                                    <td>$1,000.00</td>
                                    <td>$1,000.00</td>
                                </tr>
                                <tr>
                                    <td>Hosting (1 Year)</td>
                                    <td>1</td>
                                    <td>$200.00</td>
                                    <td>$200.00</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="subtotal">Subtotal</label>
                                    <input type="text" class="form-control" id="subtotal" value="$1,200.00" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tax">Tax (10%)</label>
                                    <input type="text" class="form-control" id="tax" value="$120.00" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="total">Total</label>
                                    <input type="text" class="form-control" id="total" value="$1,320.00" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invoice Actions -->
                <div class="card mb-4">
                    <div class="card-body">
                        <button class="btn btn-primary" onclick="printInvoice()">Print Invoice</button>
                        <a href="create-invoice.php" class="btn btn-secondary">Create New Invoice</a>
                    </div>
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
        // Function to handle invoice deletion
        function deleteInvoice() {
            if (confirm('Are you sure you want to delete this invoice?')) {
                // Logic to delete the invoice (e.g., send an AJAX request)
                alert('Invoice deleted.');
            }
        }

        // Function to print the invoice
        function printInvoice() {
            window.print();
        }

        // Function to download the invoice as a PDF
        function downloadInvoice() {
            // Logic to download the invoice (e.g., convert HTML to PDF)
            alert('Download functionality is not implemented.');
        }

        // Function to send the invoice via email
        function sendEmail() {
            // Logic to send the invoice (e.g., send an AJAX request)
            alert('Send email functionality is not implemented.');
        }

        // Function to save changes to payment status
        function saveChanges() {
            // Logic to save changes (e.g., send an AJAX request)
            alert('Payment status changed.');
        }
    </script>
</body>
</html>
