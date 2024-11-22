<?php
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sidebar</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        #sidebar-wrapper {
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: 200px;
            background-color: #f8f9fa;
            z-index: 1000;
            transition: all 0.3s;
        }

        #page-content-wrapper {
            margin-left: 250px;
            transition: margin-left 0.3s;
        }

        #menu-toggle.active ~ #sidebar-wrapper {
            margin-left: -250px;
        }

        #menu-toggle.active ~ #page-content-wrapper {
            margin-left: 0;
        }

        .sidebar-heading {
            padding: 1rem;
            font-size: 1.25rem;
            font-weight: bold;
        }

        .list-group-item {
            border: none;
        }

        .list-group-item:hover {
            background-color: #e9ecef;
        }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <div class="bg-light border-right" id="sidebar-wrapper">
            <div class="sidebar-heading"><img src="images/wenamax-logo.png" style="width: 150px"></div> 
            <div class="list-group list-group-flush">
                <a href="dashboard.php" class="list-group-item list-group-item-action bg-light"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="add-clients.php" class="list-group-item list-group-item-action bg-light"><i class="fas fa-user-plus"></i> Add Clients</a>
                <a href="manage-expenses.php" class="list-group-item list-group-item-action bg-light"><i class="fas fa-money-bill-wave"></i> Add Expenses</a>

                <a href="manage-clients.php" class="list-group-item list-group-item-action bg-light"><i class="fas fa-users-cog"></i> Manage Clients</a>
                <a href="manage-projects.php" class="list-group-item list-group-item-action bg-light"><i class="fas fa-project-diagram"></i> Manage Projects</a>
                <a href="manage-tasks.php" class="list-group-item list-group-item-action bg-light"><i class="fas fa-tasks"></i> Manage Tasks</a>
                <a href="calendar.php" class="list-group-item list-group-item-action bg-light"><i class="fas fa-calendar-alt"></i> Our Calendar</a>
                <a href="reports.php" class="list-group-item list-group-item-action bg-light"><i class="fas fa-chart-line"></i> View Reports</a>
                <a href="account-details.php" class="list-group-item list-group-item-action bg-light"><i class="fas fa-cogs"></i> Account Details</a>
                <a href="resources.php" class="list-group-item list-group-item-action bg-light"><i class="fas fa-book"></i> Our Resources</a>
                <?php if ($is_admin): ?>
                    <a href="manage-staff.php" class="list-group-item list-group-item-action bg-light"><i class="fas fa-user-plus"></i> Manage Staff</a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Page Content will go here -->
        

    <!-- Bootstrap JavaScript and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        // Toggle sidebar for small screens
        document.addEventListener('DOMContentLoaded', function() {
            var menuToggle = document.getElementById('menu-toggle');
            menuToggle.addEventListener('click', function() {
                document.getElementById('sidebar-wrapper').classList.toggle('active');
                document.getElementById('page-content-wrapper').classList.toggle('active');
            });
        });
    </script>
</body>
</html>