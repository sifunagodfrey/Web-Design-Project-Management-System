$(document).ready(function() {
    $('.navbar-toggler').click(function() {
        $('#sidebar-wrapper').toggleClass('active');
    });

    $('#sidebar-wrapper .list-group-item').click(function(e) {
        e.preventDefault();
        $('#sidebar-wrapper .list-group-item').removeClass('active');
        $(this).addClass('active');
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const themeSelect = document.getElementById('themeSettings');
    const currentTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', currentTheme);
    themeSelect.value = currentTheme;

    themeSelect.addEventListener('change', function () {
        const selectedTheme = themeSelect.value;
        document.documentElement.setAttribute('data-theme', selectedTheme);
        localStorage.setItem('theme', selectedTheme);
    });
});

document.addEventListener('DOMContentLoaded', () => {
    // Get references to important elements
    const addItemButton = document.getElementById('add-item');
    const invoiceItems = document.getElementById('invoice-items');
    
    // Event listener for the "Add Item" button
    addItemButton.addEventListener('click', () => {
        // Create a new table row for additional invoice items
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><input type="text" class="form-control" name="item_description[]" required></td>
            <td><input type="number" class="form-control" name="item_quantity[]" required></td>
            <td><input type="number" class="form-control" name="item_price[]" required></td>
            <td><input type="number" class="form-control" name="item_total[]" readonly></td>
        `;
        // Append the new row to the invoice items table
        invoiceItems.appendChild(row);
    });

    // Event listener for input changes in the table
    document.addEventListener('input', (event) => {
        // Check if the changed input is quantity or price
        if (event.target.name === 'item_quantity[]' || event.target.name === 'item_price[]') {
            // Get the closest table row
            const row = event.target.closest('tr');
            // Get the quantity and price values
            const quantity = row.querySelector('input[name="item_quantity[]"]').value;
            const price = row.querySelector('input[name="item_price[]"]').value;
            // Calculate the total for this item
            const total = row.querySelector('input[name="item_total[]"]');
            total.value = (quantity * price) || 0;

            // Update the totals for the entire invoice
            updateTotals();
        }
    });

    // Function to update subtotal, tax, and total
    function updateTotals() {
        let subtotal = 0;
        // Get all item total inputs
        const itemTotals = document.querySelectorAll('input[name="item_total[]"]');
        // Calculate subtotal
        itemTotals.forEach((totalInput) => {
            subtotal += parseFloat(totalInput.value) || 0;
        });
        
        // Calculate tax (10%)
        const tax = subtotal * 0.10;
        // Calculate total
        const total = subtotal + tax;

        // Update the subtotal, tax, and total fields
        document.getElementById('subtotal').value = subtotal.toFixed(2);
        document.getElementById('tax').value = tax.toFixed(2);
        document.getElementById('total').value = total.toFixed(2);
    }
});

document.getElementById('login-form').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent the form from submitting the default way

    var formData = new FormData(this);

    fetch('index.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'error') {
            // Display error message
            document.getElementById('error-message').textContent = data.message;
            document.getElementById('error-message').style.display = 'block';
        } else {
            // Redirect to dashboard or handle success
            window.location.href = 'dashboard.php';
        }
    })
    .catch(error => console.error('Error:', error));
});
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('new-password-form');
    var errorMessage = document.getElementById('error-message');
    var successMessage = document.getElementById('success-message');

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        var formData = new FormData(form);

        fetch('new-password.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'error') {
                errorMessage.textContent = data.message;
                errorMessage.style.display = 'block';
                successMessage.style.display = 'none';
            } else if (data.status === 'success') {
                successMessage.textContent = data.message;
                successMessage.style.display = 'block';
                errorMessage.style.display = 'none';
            }
        })
        .catch(error => {
            errorMessage.textContent = 'An error occurred.';
            errorMessage.style.display = 'block';
            successMessage.style.display = 'none';
        });
    });
});

