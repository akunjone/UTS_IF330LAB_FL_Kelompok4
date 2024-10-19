<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$list_id = filter_input(INPUT_GET, 'list_id', FILTER_SANITIZE_NUMBER_INT);

if (!$list_id) {
    header("Location: dashboard.php");
    exit();
}

$list_info = getToDoListInfo($list_id, $user_id);
if (!$list_info) {
    header("Location: dashboard.php");
    exit();
}

$filter = filter_input(INPUT_GET, 'filter', FILTER_SANITIZE_STRING) ?: 'all';
$searchQuery = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING) ?: '';

$tasks = [
    'must_do' => getTasks($list_id, 'must_do', $searchQuery, $filter),
    'should_do' => getTasks($list_id, 'should_do', $searchQuery, $filter),
    'could_do' => getTasks($list_id, 'could_do', $searchQuery, $filter),
    'if_time' => getTasks($list_id, 'if_time', $searchQuery, $filter),
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_task'])) {
        $task_description = filter_input(INPUT_POST, 'task_description', FILTER_SANITIZE_STRING);
        $priority = filter_input(INPUT_POST, 'priority', FILTER_SANITIZE_STRING);
        addTask($list_id, $task_description, $priority);
    } elseif (isset($_POST['update_task'])) {
        $task_id = filter_input(INPUT_POST, 'task_id', FILTER_SANITIZE_NUMBER_INT);
        $is_completed = isset($_POST['is_completed']) ? 1 : 0;
        updateTaskStatus($task_id, $is_completed);
    } elseif (isset($_POST['delete_task'])) {
        $task_id = filter_input(INPUT_POST, 'task_id', FILTER_SANITIZE_NUMBER_INT);
        deleteTask($task_id);
    }
    header("Location: todo.php?list_id=$list_id");
    exit();
}
?>

<script>
    document.querySelectorAll('.btn-filter').forEach(button => {
        button.addEventListener('click', function() {
            // Get the filter value from the data-filter attribute
            const filterValue = this.getAttribute('data-filter');

            // Update the URL with the selected filter
            const url = new URL(window.location.href);
            url.searchParams.set('filter', filterValue);

            // Reload the page with the new filter
            window.location.href = url;
        });
    });

    // Get the current filter from the URL
    const currentFilter = new URLSearchParams(window.location.search).get('filter') || 'all';

    // Set the corresponding button as active
    document.querySelectorAll('.btn-filter').forEach(button => {
        if (button.getAttribute('data-filter') === currentFilter) {
            button.classList.add('active');
        } else {
            button.classList.remove('active');
        }
    });

    function filterTasks(filter) {
        const listId = "<?php echo $list_id; ?>"; // Get the list ID from PHP
        window.location.href = `todo.php?list_id=${listId}&filter=${filter}`;
    }
</script>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($list_info['title']); ?> - To-Do List App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">To-Do List</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="profile.php">Profile</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="todo-header">
            <h1><?php echo htmlspecialchars($list_info['title']); ?></h1>
            <p class="todo-date">Date: <?php echo date('d.m.Y'); ?></p>
        </div>

        <div class="mb-3">
            <button class="btn btn-filter active" data-filter="all" onclick="filterTasks('all')">All</button>
            <button class="btn btn-filter" data-filter="completed" onclick="filterTasks('completed')">Completed</button>
            <button class="btn btn-filter" data-filter="uncompleted" onclick="filterTasks('uncompleted')">Uncompleted</button>
        </div>

        
        <div class="todo-grid">
            <?php
            $priorities = [
                'must_do' => 'MUST DO:',
                'should_do' => 'SHOULD DO:',
                'could_do' => 'COULD DO:',
                'if_time' => 'IF I HAVE TIME:'
            ];
            foreach ($priorities as $key => $title): ?>
                <div class="todo-column <?php echo $key; ?>">
                    <h2>
                        <img src="../assets/images/clock.png" class="clock-icon">
                        <?php echo $title; ?>
                    </h2>
                    <?php foreach ($tasks[$key] as $task): ?>
                        <div class="todo-item <?php echo $task['is_completed'] ? 'completed' : ''; ?>">
                        <form action="todo.php?list_id=<?php echo $list_id; ?>" method="post">
                            <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                            <input type="hidden" name="update_task" value="1"> <!--update task, jadi nanti pas dicentang, database update value is_completed-->
                            <input type="checkbox" name="is_completed" id="task-<?php echo $task['id']; ?>" <?php echo $task['is_completed'] ? 'checked' : ''; ?> onchange="this.form.submit()">
                            <label for="task-<?php echo $task['id']; ?>"><?php echo htmlspecialchars($task['description']); ?></label>
                        </form>
                        </div>
                    <?php endforeach; ?>
                    <button class="btn btn-sm btn-add-task" data-bs-toggle="modal" data-bs-target="#addTaskModal" data-priority="<?php echo $key; ?>">+ Add Task</button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="modal fade" id="addTaskModal" tabindex="-1" aria-labelledby="addTaskModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addTaskModalLabel">Add New Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="todo.php?list_id=<?php echo $list_id; ?>" method="post">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="task_description" class="form-label">Task Description</label>
                            <input type="text" class="form-control" id="task_description" name="task_description" required>
                        </div>
                        <input type="hidden" id="priority" name="priority" value="">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_task" class="btn btn-primary">Add Task</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.btn-add-task').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('priority').value = this.dataset.priority;
            });
        });
    </script>
    <script src="assets/js/script.js"></script>
</body>
</html>