<?php
include '../auth/config.php';
include '../auth/session.php';

// Check if the user is logged in
if (!checkUserSession()) {
    // Redirect to the login page if not logged in
    header("Location: ../auth/login.php");
    exit();
}
$user = getUserFromSession();

// Count the number of databases and fetch their details
$database_count = 0;
$database_details = array();

if ($user) {
    $user_id = $user['id'];
    
    // Prepare and bind
    $stmt = $conn->prepare("SELECT * FROM db WHERE userid = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Count the number of databases
    $database_count = $result->num_rows;

    // Fetch details of each database
    while ($row = $result->fetch_assoc()) {
        $database_details[] = $row;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
        }
        .sidebar {
            min-width: 250px;
            max-width: 250px;
            background-color: #f8f9fa;
            padding: 1rem;
            border-right: 1px solid #dee2e6;
        }
        .main-content {
            flex-grow: 1;
            padding: 1rem;
        }
        .card {
            border: 1px solid rgba(0, 0, 0, 0.125);
            border-radius: 0.25rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 0.75rem 1.25rem;
            margin-bottom: 0;
        }
        .badge {
            font-size: 0.9rem;
            padding: 0.3rem 0.5rem;
            border-radius: 0.25rem;
        }
        .card-body {
            padding: 1rem;
        }
        .table {
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <?php include 'sidenav.php'; ?>
    <div class="main-content">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?>" role="alert">
                <?php echo $_SESSION['message']; ?>
            </div>
            <?php
            // Clear message after displaying
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
            ?>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h2>Database Info</h2>
                    <span class="badge badge-primary">Databases: <?php echo $database_count; ?>/2</span>
                </div>
            </div>

            <div class="card-body">
                <?php if ($database_count > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col">Host</th>
                                    <th scope="col">Username</th>
                                    <th scope="col">Password</th>
                                    <th scope="col">Database Name</th>
                                    <th scope='col'>Port</th>
                                    <th scope="col">Created At</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($database_details as $db): ?>
                                    <tr>
                                        <td>primary.mysql--pcscn2qr8zht.addon.code.run</td>
                                        <td><?php echo $user['username'];?></td>
                                        <td><?php echo $user['db_password'];?></td>
                                        <td><?php echo $db['name']; ?></td>
                                        <td>29631</td>
                                        <td><?php echo $db['created_at']; ?></td>
                                        <td>
                                            <form method="post" action="delete_database.php" onsubmit="return confirmDelete(this);">
                                                <input type="hidden" name="db_id" value="<?php echo $db['id']; ?>">
                                                <input type="hidden" name="db_name" value="<?php echo $db['name']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" id='deletedatabase'>Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info" role="alert">
                        <strong>No databases found.</strong>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card-footer text-right">
                <?php if ($database_count < 2): ?>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createDatabaseModal">
                        Create New Database
                    </button>
                <?php else: ?>
                    <button type="button" class="btn btn-primary" disabled>
                        Maximum Databases Reached
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- COMMENT -->
                    
        <!-- COMMENT -->



        <!-- Modal -->
        <div class="modal fade" id="createDatabaseModal" tabindex="-1" aria-labelledby="createDatabaseModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createDatabaseModalLabel">Create New Database</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="createDatabaseForm" method="post" action="create_database.php">
                            <div class="form-group">
                                <label for="databaseName">User Name</label>
                                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                                <label for="databaseName">Password</label>
                                <input type="text" class="form-control" id="password" name="password" value="<?php echo $user['db_password']; ?>" readonly>
                                <label for="databaseName">Database Name</label>
                                <input type="text" class="form-control" id="databaseName" name="databaseName" required>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" form="createDatabaseForm">Create</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>

    function confirmDelete(form) {
        return confirm("Are you sure you want to delete this database?");
    }

</script>

</body>
</html>
