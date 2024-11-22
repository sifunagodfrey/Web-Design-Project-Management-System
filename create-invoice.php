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
    <title>Create Invoice</title>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="d-flex" id="wrapper">
        <?php include 'includes/side-bar.php'; ?>
        <div id="page-content-wrapper">
            <div class="container-fluid">
                <h1 class="mt-4">Create Invoice</h1>
                
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Wenamax Invoice Template</h5>
                        <form action="submit-invoice.php" method="post">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <h6>Company Details</h6>
                                    <p><strong>Wenamax</strong><br>
                                    123 Business Rd.<br>
                                    Ongata Rongai, Kenya<br>
                                    Email: info@wenamax.com<br>
                                    Phone: +254 700 000 000</p>
                                </div>
                                <div class="col-md-6 text-right">
                                    <h6>Invoice Details</h6>
                                    <div class="form-group">
                                        <label for="invoice-number">Invoice Number</label>
                                        <input type="text" class="form-control" id="invoice-number" name="invoice_number" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="invoice-date">Invoice Date</label>
                                        <input type="date" class="form-control" id="invoice-date" name="invoice_date" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="due-date">Due Date</label>
                                        <input type="date" class="form-control" id="due-date" name="due_date" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <h6>Client Details</h6>
                                    <div class="form-group">
                                        <label for="client-name">Client Name</label>
                                        <input type="text" class="form-control" id="client-name" name="client_name" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="client-address">Client Address</label>
                                        <textarea class="form-control" id="client-address" name="client_address" rows="3" required></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="client-email">Client Email</label>
                                        <input type="email" class="form-control" id="client-email" name="client_email" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="client-phone">Client Phone</label>
                                        <input type="text" class="form-control" id="client-phone" name="client_phone" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <h6>Invoice Items</h6>
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Description</th>
                                                <th>Quantity</th>
                                                <th>Unit Price</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody id="invoice-items">
                                            <tr>
                                                <td><input type="text" class="form-control" name="item_description[]" required></td>
                                                <td><input type="number" class="form-control" name="item_quantity[]" required></td>
                                                <td><input type="number" class="form-control" name="item_price[]" required></td>
                                                <td><input type="number" class="form-control" name="item_total[]" readonly></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <button type="button" class="btn btn-secondary" id="add-item">Add Item</button>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="subtotal">Subtotal</label>
                                        <input type="number" class="form-control" id="subtotal" name="subtotal" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tax">Tax (10%)</label>
                                        <input type="number" class="form-control" id="tax" name="tax" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="total">Total</label>
                                        <input type="number" class="form-control" id="total" name="total" readonly>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Generate Invoice</button>
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
</body>
</html>
