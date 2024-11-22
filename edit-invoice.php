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
    <title>Edit Invoice</title>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="d-flex" id="wrapper">
        <?php include 'includes/side-bar.php'; ?>
        <div id="page-content-wrapper">
            <div class="container-fluid">
                <h1 class="mt-4">Edit Invoice</h1>

                <!-- Edit Invoice Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">Invoice #INV-001</h5>
                    </div>
                    <div class="card-body">
                        <form>
                            <!-- Client Details -->
                            <h6 class="font-weight-bold">Client Details</h6>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="clientName">Client Name</label>
                                    <input type="text" class="form-control" id="clientName" value="John Doe">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="clientEmail">Client Email</label>
                                    <input type="email" class="form-control" id="clientEmail" value="john.doe@example.com">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="clientPhone">Client Phone</label>
                                    <input type="text" class="form-control" id="clientPhone" value="+254 711 111 111">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="clientAddress">Client Address</label>
                                    <input type="text" class="form-control" id="clientAddress" value="456 Client St, Nairobi, Kenya">
                                </div>
                            </div>

                            <!-- Invoice Details -->
                            <h6 class="font-weight-bold">Invoice Details</h6>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="invoiceDate">Invoice Date</label>
                                    <input type="date" class="form-control" id="invoiceDate" value="2024-08-10">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="dueDate">Due Date</label>
                                    <input type="date" class="form-control" id="dueDate" value="2024-09-10">
                                </div>
                            </div>

                            <!-- Invoice Items -->
                            <h6 class="font-weight-bold">Invoice Items</h6>
                            <div id="invoiceItems">
                                <div class="form-row mb-2">
                                    <div class="form-group col-md-6">
                                        <label for="itemDescription1">Description</label>
                                        <input type="text" class="form-control" id="itemDescription1" value="Website Design">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="itemQuantity1">Quantity</label>
                                        <input type="number" class="form-control" id="itemQuantity1" value="1">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="itemPrice1">Unit Price</label>
                                        <input type="text" class="form-control" id="itemPrice1" value="$1,000.00">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="itemTotal1">Total</label>
                                        <input type="text" class="form-control" id="itemTotal1" value="$1,000.00" readonly>
                                    </div>
                                </div>
                                <div class="form-row mb-2">
                                    <div class="form-group col-md-6">
                                        <label for="itemDescription2">Description</label>
                                        <input type="text" class="form-control" id="itemDescription2" value="Hosting (1 Year)">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="itemQuantity2">Quantity</label>
                                        <input type="number" class="form-control" id="itemQuantity2" value="1">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="itemPrice2">Unit Price</label>
                                        <input type="text" class="form-control" id="itemPrice2" value="$200.00">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="itemTotal2">Total</label>
                                        <input type="text" class="form-control" id="itemTotal2" value="$200.00" readonly>
                                    </div>
                                </div>
                            </div>

                            <!-- Totals -->
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

                            <!-- Save Changes -->
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                            <a href="view-invoice.php?invoiceID=INV-001" class="btn btn-secondary">Cancel</a>
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
    <script src="script.js"></script>
    <script>
        // JavaScript to handle invoice item calculations
        document.addEventListener('input', function(event) {
            if (event.target.id.startsWith('itemQuantity') || event.target.id.startsWith('itemPrice')) {
                calculateItemTotal(event.target);
                calculateTotals();
            }
        });

        function calculateItemTotal(element) {
            const row = element.closest('.form-row');
            const quantity = row.querySelector('[id^=itemQuantity]').value;
            const price = row.querySelector('[id^=itemPrice]').value.replace('$', '').replace(',', '');
            const totalField = row.querySelector('[id^=itemTotal]');
            const total = (quantity * parseFloat(price)).toFixed(2);
            totalField.value = `$${total}`;
        }

        function calculateTotals() {
            const subtotals = Array.from(document.querySelectorAll('[id^=itemTotal]'))
                .map(item => parseFloat(item.value.replace('$', '').replace(',', '')))
                .reduce((sum, value) => sum + value, 0);
            document.getElementById('subtotal').value = `$${subtotals.toFixed(2)}`;
            const tax = (subtotals * 0.10).toFixed(2);
            document.getElementById('tax').value = `$${tax}`;
            document.getElementById('total').value = `$${(subtotals + parseFloat(tax)).toFixed(2)}`;
        }
    </script>
</body>
</html>
