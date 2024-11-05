<?php
require_once("config.php");
session_start();


// Handle the checkbox submission
if (isset($_POST['task_id']) && isset($_POST['importance'])) {
    $task_id = $conn -> real_escape_string($_POST['task_id']);
    $importance = $conn -> real_escape_string($_POST['importance']);
    

    // Update query
    $sql = "UPDATE tasks SET task_importance = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $importance, $task_id);
    $stmt->execute();
  

    $stmt->close();
    $conn->close();
}
header("Location: ../../task.php");
exit();