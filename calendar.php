<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    // Redirect to index.php if not logged in
    header("Location: index.php");
    exit();
}

include 'includes/header.php'; 
include 'includes/connection.php'; // Include the database connection file

// Fetch tasks and projects from the database
$tasks_query = "SELECT task_id AS id, task_name AS title, due_date AS start, 'task' AS type FROM tasks";
$projects_query = "SELECT project_id AS id, project_name AS title, start_date AS start, 'project' AS type FROM projects";
$tasks_result = mysqli_query($conn, $tasks_query);
$projects_result = mysqli_query($conn, $projects_query);

$events = [];
while ($row = mysqli_fetch_assoc($tasks_result)) {
    $events[] = $row;
}
while ($row = mysqli_fetch_assoc($projects_result)) {
    $events[] = $row;
}

mysqli_close($conn); // Close the database connection
?>
<div class="d-flex" id="wrapper">
    <?php include 'includes/side-bar.php'; ?>
    <div id="page-content-wrapper">
        <div class="container-fluid">
            <h2 class="mt-4">Company Calendar</h2>
            
            <div class="calendar-container">
                <div id="calendar"></div>
                <a href="manage-projects.php" class="btn btn-primary mt-3">Go to Projects</a>
                <a href="manage-tasks.php" class="btn btn-primary mt-3">Go to Tasks</a>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>

<!-- Include FullCalendar JavaScript and CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
<script>
    $(document).ready(function() {
        $('#calendar').fullCalendar({
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
            },
            defaultView: 'month',
            editable: false,
            events: <?php echo json_encode($events); ?>,
            eventRender: function(event, element) {
                // Assign colors based on event type
                if (event.type === 'task') {
                    element.css('background-color', '#ff9f00'); // Yellow for tasks
                } else if (event.type === 'project') {
                    element.css('background-color', '#1e90ff'); // Blue for projects
                }
                element.css('border-color', '#000'); // Black border for all events
            },
            eventClick: function(event) {
                var eventUrl = '';
                if (event.type === 'task') {
                    eventUrl = 'task-details.php?id=' + event.id;
                } else if (event.type === 'project') {
                    eventUrl = 'project-details.php?id=' + event.id;
                }

                // Open the event details in a new tab
                window.open(eventUrl, '_blank');
            }
        });
    });
</script>
