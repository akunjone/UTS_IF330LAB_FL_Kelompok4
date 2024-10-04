<?php
require_once '../config/database.php';

function createToDoList($user_id, $title) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("INSERT INTO todo_lists (user_id, title) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $title);
    return $stmt->execute();
}

function getToDoLists($user_id) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT * FROM todo_lists WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function deleteToDoList($list_id, $user_id) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("DELETE FROM todo_lists WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $list_id, $user_id);
    return $stmt->execute();
}

function addTask($list_id, $description, $priority) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("INSERT INTO tasks (list_id, description, priority) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $list_id, $description, $priority);
    return $stmt->execute();
}

function getTasks($list_id, $priority) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT * FROM tasks WHERE list_id = ? AND priority = ? ORDER BY is_completed, id");
    $stmt->bind_param("is", $list_id, $priority);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function updateTaskStatus($task_id, $is_completed) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("UPDATE tasks SET is_completed = ? WHERE id = ?");
    $stmt->bind_param("ii", $is_completed, $task_id);
    return $stmt->execute();
}

function searchTasks($user_id, $search_term) {
    $conn = getDbConnection();
    $search_term = "%$search_term%";
    $stmt = $conn->prepare("SELECT t.* FROM tasks t JOIN todo_lists l ON t.list_id = l.id WHERE l.user_id = ? AND t.description LIKE ?");
    $stmt->bind_param("is", $user_id, $search_term);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function updateUserProfile($user_id, $username, $email) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
    $stmt->bind_param("ssi", $username, $email, $user_id);
    return $stmt->execute();
}

function updateUserPassword($user_id, $new_password) {
    $conn = getDbConnection();
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed_password, $user_id);
    return $stmt->execute();
}

function getToDoListInfo($list_id, $user_id) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT * FROM todo_lists WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $list_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function deleteTask($task_id) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->bind_param("i", $task_id);
    return $stmt->execute();
}

function getUserData($user_id) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

?>