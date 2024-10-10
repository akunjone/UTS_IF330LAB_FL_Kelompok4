<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$todo_lists = getToDoLists($user_id);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['new_list'])) {
        $list_title = filter_input(INPUT_POST, 'list_title', FILTER_SANITIZE_STRING);
        createToDoList($user_id, $list_title);
        header("Location: dashboard.php");
        exit();
    }elseif (isset($_POST['delete_list'])) { //buat ngapus list
        $list_id = filter_input(INPUT_POST, 'delete_list', FILTER_SANITIZE_NUMBER_INT);
        deleteToDoList($list_id, $user_id);  //panggil fungsi di functions.php
        header("Location: dashboard.php");
        exit();
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
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="#">To-Do List</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="profile.php">Profile</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        
        <div class="row mt-4">
            <div class="col-md-4">
                <h2>Create New List</h2>
                <form action="dashboard.php" method="post">
                    <div class="mb-3">
                        <input type="text" class="form-control" name="list_title" required placeholder="List Title">
                    </div>
                    <button type="submit" name="new_list" class="btn btn-primary">Create List</button>
                </form>
            </div>
            <div class="col-md-8">
                <h2>Your To-Do Lists</h2>
                <div class="row">
                    <?php foreach ($todo_lists as $list): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($list['title']); ?></h5>
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
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>