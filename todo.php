<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_task'])) {
    $subject = trim($_POST['subject']);
    $task = trim($_POST['task']);
    $deadline = $_POST['deadline'];

    if (empty($subject) || empty($task)) {
        $message = "Subject and Task fields cannot be empty.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO tasks (user_id, subject, task, deadline) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$user_id, $subject, $task, $deadline])) {
            $message = "Task added successfully.";
        } else {
            $message = "Failed to add task.";
        }
    }
}

if (isset($_GET['delete'])) {
    $task_id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$task_id, $user_id])) {
        $message = "Task deleted successfully.";
    } else {
        $message = "Failed to delete task.";
    }
}

if (isset($_GET['complete'])) {
    $task_id = intval($_GET['complete']);
    $stmt = $pdo->prepare("UPDATE tasks SET completed = 1 WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$task_id, $user_id])) {
        $message = "Task marked as completed.";
    } else {
        $message = "Failed to update task.";
    }
}

$stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY deadline ASC");
$stmt->execute([$user_id]);
$tasks = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your To-Do List</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Your To-Do List</h1>
        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST" action="todo.php" class="task-form">
            <h2>Add New Task</h2>
            <label for="subject">Subject</label>
            <input type="text" name="subject" id="subject" placeholder="Task subject" required>

            <label for="task">Task</label>
            <textarea name="task" id="task" placeholder="Describe your task" required></textarea>

            <label for="deadline">Deadline</label>
            <input type="date" name="deadline" id="deadline">

            <button type="submit" name="add_task">Add Task</button>
        </form>

        <h2>Existing Tasks</h2>
        <?php if (count($tasks) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Task</th>
                        <th>Deadline</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $task): ?>
                        <tr class="<?php echo $task['completed'] ? 'completed' : ''; ?>">
                            <td><?php echo htmlspecialchars($task['subject']); ?></td>
                            <td><?php echo htmlspecialchars($task['task']); ?></td>
                            <td><?php echo htmlspecialchars($task['deadline']); ?></td>
                            <td><?php echo $task['completed'] ? 'Completed' : 'Pending'; ?></td>
                            <td>
                                <?php if (!$task['completed']): ?>
                                    <a href="todo.php?complete=<?php echo $task['id']; ?>" class="complete">Complete</a>
                                <?php endif; ?>
                                <a href="edit.php?id=<?php echo $task['id']; ?>" class="edit">Edit</a>
                                <a href="todo.php?delete=<?php echo $task['id']; ?>" class="delete" onclick="return confirm('Are you sure you want to delete this task?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No tasks found. Add your first task!</p>
        <?php endif; ?>

        <p><a href="logout.php">Logout</a></p>
    </div>
</body>
</html>
