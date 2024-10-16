<?php

include 'config.php';
session_start();


if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$message = '';


if (isset($_GET['id'])) {
    $task_id = intval($_GET['id']);
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$task_id, $_SESSION['user_id']]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$task) {
        header('Location: todo.php');
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject = trim($_POST['subject']);
    $task = trim($_POST['task']);
    $deadline = $_POST['deadline'];

    if (empty($subject) || empty($task)) {
        $message = "Subject and Task fields cannot be empty.";
    } else {
        $stmt = $pdo->prepare("UPDATE tasks SET subject = ?, task = ?, deadline = ? WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$subject, $task, $deadline, $task_id, $_SESSION['user_id']])) {
            header('Location: todo.php');
            exit();
        } else {
            $message = "Failed to update task.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Edit Task - ToDo App</title>
</head>
<body>
    <div class="container">
        <h1>Edit Task</h1>

        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST" action="edit.php?id=<?php echo $task_id; ?>" class="task-form">
            <label for="subject">Subject</label>
            <input type="text" name="subject" id="subject" value="<?php echo htmlspecialchars($task['subject']); ?>" required>

            <label for="task">Task</label>
            <textarea name="task" id="task" required><?php echo htmlspecialchars($task['task']); ?></textarea>

            <label for="deadline">Deadline</label>
            <input type="date" name="deadline" id="deadline" value="<?php echo htmlspecialchars($task['deadline']); ?>">

            <button type="submit">Update Task</button>
        </form>

        <a href="todo.php" class="back">Back to To-Do List</a>
    </div>
</body>
</html>
