<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    // Redirect to index.php if not logged in
    header("Location: index.php");
    exit();
}

include 'includes/connection.php'; // Ensure connection to the database
include 'includes/header.php';

// Initialize variables
$leadType = isset($_POST['leadType']) ? $_POST['leadType'] : 'new';
$timePeriod = isset($_POST['timePeriod']) ? $_POST['timePeriod'] : 'today';
$month = isset($_POST['month']) ? $_POST['month'] : date('Y-m');

// Function to fetch report data
function getReportData($conn, $leadType, $timePeriod, $month) {
    $conditions = [];
    $params = [];
    $query = "SELECT COUNT(*) as count FROM clients WHERE lead_status = ?";

    // Time period conditions
    if ($timePeriod === 'today') {
        $conditions[] = "DATE(updated_at) = CURDATE()";
    } elseif ($timePeriod === 'weekly') {
        $conditions[] = "YEARWEEK(updated_at, 1) = YEARWEEK(CURDATE(), 1)";
    } elseif ($timePeriod === 'monthly') {
        $conditions[] = "YEAR(updated_at) = YEAR(?) AND MONTH(updated_at) = MONTH(?)";
        $params[] = $month;
        $params[] = $month;
    }

    if (count($conditions) > 0) {
        $query .= " AND " . implode(" AND ", $conditions);
    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s" . str_repeat("s", count($params)), $leadType, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();

    return $data['count'];
}

// Fetch the report data
$reportData = getReportData($conn, $leadType, $timePeriod, $month);
?>

<div class="d-flex" id="wrapper">
    <?php include 'includes/side-bar.php'; ?>
    <div id="page-content-wrapper">
        <div class="container-fluid">
            <h1 class="mt-4">Reports</h1>
            
            <!-- Lead Type Selection -->
            <div class="form-group">
                <label for="leadTypeSelect">Select Lead Type</label>
                <select id="leadTypeSelect" class="form-control">
                    <option value="new" <?php echo $leadType === 'new' ? 'selected' : ''; ?>>New Leads</option>
                    <option value="qualified" <?php echo $leadType === 'qualified' ? 'selected' : ''; ?>>Qualified Leads</option>
                    <option value="won" <?php echo $leadType === 'won' ? 'selected' : ''; ?>>Won Leads</option>
                    <option value="proposals" <?php echo $leadType === 'proposals' ? 'selected' : ''; ?>>Proposals Sent</option>
                    <option value="followUp" <?php echo $leadType === 'followUp' ? 'selected' : ''; ?>>Follow-Up Required</option>
                    <option value="refused" <?php echo $leadType === 'refused' ? 'selected' : ''; ?>>Refused Leads</option>
                    <option value="dormant" <?php echo $leadType === 'dormant' ? 'selected' : ''; ?>>Dormant Leads</option>
                </select>
            </div>

            <!-- Time Period Selection -->
            <div class="form-group">
                <label for="timePeriodSelect">Select Time Period</label>
                <select id="timePeriodSelect" class="form-control">
                    <option value="today" <?php echo $timePeriod === 'today' ? 'selected' : ''; ?>>Today</option>
                    <option value="weekly" <?php echo $timePeriod === 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                    <option value="monthly" <?php echo $timePeriod === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                </select>
            </div>

            <!-- Month Selection for Monthly Report -->
            <div class="form-group" id="monthSelectGroup" style="display: <?php echo $timePeriod === 'monthly' ? 'block' : 'none'; ?>;">
                <label for="monthSelect">Select Month</label>
                <input type="month" id="monthSelect" class="form-control" value="<?php echo $month; ?>">
            </div>

            <!-- Chart Container -->
            <div class="chart-container">
                <canvas id="reportChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Chart.js instance
        var ctx = document.getElementById('reportChart').getContext('2d');
        var reportChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Leads'],
                datasets: [{
                    label: 'Leads',
                    data: [<?php echo $reportData; ?>],
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Function to update the chart
        function updateChart(leadType, timePeriod, month) {
            // Fetch data based on the selections
            fetch('fetch_report_data.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ leadType: leadType, timePeriod: timePeriod, month: month })
            })
            .then(response => response.json())
            .then(data => {
                reportChart.data.datasets[0].data = [data.count];
                reportChart.data.datasets[0].label = leadType.charAt(0).toUpperCase() + leadType.slice(1) + ' Leads';
                reportChart.update();
            });
        }

        // Event listeners for the selects
        document.getElementById('leadTypeSelect').addEventListener('change', function() {
            const leadType = this.value;
            const timePeriod = document.getElementById('timePeriodSelect').value;
            const month = document.getElementById('monthSelect').value;
            updateChart(leadType, timePeriod, month);
        });

        document.getElementById('timePeriodSelect').addEventListener('change', function() {
            const timePeriod = this.value;
            const leadType = document.getElementById('leadTypeSelect').value;
            const month = document.getElementById('monthSelect').value;
            
            // Show or hide the month selector based on the time period selection
            document.getElementById('monthSelectGroup').style.display = (timePeriod === 'monthly') ? 'block' : 'none';
            updateChart(leadType, timePeriod, month);
        });

        document.getElementById('monthSelect').addEventListener('change', function() {
            const month = this.value;
            const leadType = document.getElementById('leadTypeSelect').value;
            const timePeriod = document.getElementById('timePeriodSelect').value;
            updateChart(leadType, timePeriod, month);
        });

        // Initial chart update
        updateChart(document.getElementById('leadTypeSelect').value, document.getElementById('timePeriodSelect').value, document.getElementById('monthSelect').value);
    });
</script>
