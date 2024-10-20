<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$todo_lists = getToDoLists($user_id);
$search_results = []; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['new_list'])) {
        $list_title = filter_input(INPUT_POST, 'list_title', FILTER_SANITIZE_STRING);
        createToDoList($user_id, $list_title);
        header("Location: dashboard.php");
        exit();
    } elseif (isset($_POST['delete_list'])) { 
        $list_id = filter_input(INPUT_POST, 'delete_list', FILTER_SANITIZE_NUMBER_INT);
        deleteToDoList($list_id, $user_id);
        header("Location: dashboard.php");
        exit();
    } elseif (isset($_POST['search_tasks'])) { 
        $search_query = filter_input(INPUT_POST, 'search_input', FILTER_SANITIZE_STRING);
        $search_results = searchTasks($user_id, $search_query);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - To-Do List App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="#">To-Do List</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="profile.php">Profile</a>
            <a class="nav-link" href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="container mt-4">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <?php 
        date_default_timezone_set('Asia/Jakarta');
        echo date("l, d F Y, h:i A");
        ?>
        <div class="row mt-4">
            <div class="col-md-4">
                <h2>Create New List</h2>
                <form action="dashboard.php" method="post">
                    <div class="mb-3">
                        <input type="text" class="form-control" name="list_title" required placeholder="List Title">
                    </div>
                    <button type="submit" name="new_list" class="btn btn-primary">Create List</button>
                </form>
                <div class="mt-3">
                    <h2>Search Tasks</h2>
                    <form action="dashboard.php" method="post">
                        <input type="text" class="form-control" name="search_input" placeholder="Search tasks..." required>
                        <button type="submit" name="search_tasks" class="btn btn-secondary mt-2">Search</button>
                    </form>
                </div>
            </div>
            <div class="col-md-8">
                <h2>Your To-Do Lists</h2>
                <div class="row">
                    <?php foreach ($todo_lists as $list): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($list['title']); ?></h5>
                                    <small>Created: <?php echo date("d F Y, h:i A", strtotime($list['created_at'])); ?></small><br/>
                                    <a href="todo.php?list_id=<?php echo $list['id']; ?>" class="btn btn-sm btn-primary">View Tasks</a>
                                    <form action="dashboard.php" method="post" class="d-inline">
                                        <input type="hidden" name="delete_list" value="<?php echo $list['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this list?')">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (!empty($search_results)): ?>
                    <h2>Search Results</h2>
                    <div class="row">
                        <?php foreach ($search_results as $task): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($task['description']); ?></h5>
                                        <small>Priority: <?php echo htmlspecialchars($task['priority']); ?></small><br/>
                                        <small>Status: <?php echo $task['is_completed'] ? 'Completed' : 'Uncompleted'; ?></small>
                                        <form action="dashboard.php" method="post" class="d-inline">
                                            <input type="hidden" name="delete_task" value="<?php echo $task['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this task?')">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>
