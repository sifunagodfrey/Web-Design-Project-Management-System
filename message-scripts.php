<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

include 'includes/header.php';
include 'includes/connection.php';

// Handle update request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_admin) {
    header('Content-Type: application/json');
    
    $id = $_POST['id'];
    $category = $_POST['category'];
    $title = $_POST['title'];
    $content = $_POST['content'];

    // Sanitize input to avoid SQL injection
    $id = $conn->real_escape_string($id);
    $category = $conn->real_escape_string($category);
    $title = $conn->real_escape_string($title);
    $content = $conn->real_escape_string($content);

    $stmt = $conn->prepare("UPDATE message_scripts SET category = ?, title = ?, content = ? WHERE id = ?");
    $stmt->bind_param('sssi', $category, $title, $content, $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Message updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error updating message']);
    }

    $stmt->close();
    $conn->close();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message Scripts</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            padding: 20px;
        }
        .card {
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .modal-content {
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include 'includes/side-bar.php'; ?>
        <div id="page-content-wrapper">
            <div class="container-fluid">
                <header>
                    <h1>Message Scripts</h1>
                </header>
                <main>
                    <div class="card">
                        <div class="card-header">Message Scripts</div>
                        <div class="card-body">
                            <?php
                            // Fetch message scripts from the database
                            $sql = "SELECT * FROM message_scripts";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                echo '<table class="table table-bordered">';
                                echo '<thead><tr><th>Category</th><th>Title</th><th>Content</th>';
                                if ($is_admin) {
                                    echo '<th>Actions</th>';
                                }
                                echo '</tr></thead><tbody>';
                                while ($row = $result->fetch_assoc()) {
                                    echo '<tr>';
                                    echo '<td>' . htmlspecialchars($row['category']) . '</td>';
                                    echo '<td>' . htmlspecialchars($row['title']) . '</td>';
                                    echo '<td>' . htmlspecialchars($row['content']) . '</td>';
                                    if ($is_admin) {
                                        echo '<td><button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editModal" data-id="' . $row['id'] . '" data-category="' . htmlspecialchars($row['category']) . '" data-title="' . htmlspecialchars($row['title']) . '" data-content="' . htmlspecialchars($row['content']) . '"><i class="fas fa-edit"></i> Edit</button></td>';
                                    }
                                    echo '</tr>';
                                }
                                echo '</tbody></table>';
                            } else {
                                echo '<p>No messages found.</p>';
                            }
                            ?>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Message Script</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editForm">
                    <div class="modal-body">
                        <input type="hidden" id="editId" name="id">
                        <div class="form-group">
                            <label for="editCategory">Category</label>
                            <input type="text" class="form-control" id="editCategory" name="category" required>
                        </div>
                        <div class="form-group">
                            <label for="editTitle">Title</label>
                            <input type="text" class="form-control" id="editTitle" name="title" required>
                        </div>
                        <div class="form-group">
                            <label for="editContent">Content</label>
                            <textarea class="form-control" id="editContent" name="content" rows="5" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Populate modal with data
        $('#editModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');
            var category = button.data('category');
            var title = button.data('title');
            var content = button.data('content');

            var modal = $(this);
            modal.find('#editId').val(id);
            modal.find('#editCategory').val(category);
            modal.find('#editTitle').val(title);
            modal.find('#editContent').val(content);
        });

        // Handle form submission
        $('#editForm').on('submit', function (event) {
            event.preventDefault();
            
            var formData = $(this).serialize();
            $.ajax({
                type: 'POST',
                url: 'message-scripts.php', // This should be the current page to handle form submission
                data: formData,
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        $('#editModal').modal('hide');
                        location.reload(); // Reload the page to reflect changes
                    } else {
                        alert(response.message);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error: ' + status + ' - ' + error);
                }
            });
        });
    </script>
</body>
</html>

<?php
include 'includes/footer.php';
?>