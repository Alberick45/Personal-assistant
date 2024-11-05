<?php
require_once("config.php");
session_start();


// Handle the checkbox submission
if (isset($_POST['task_id']) && isset($_POST['status'])) {
    $task_id = $conn -> real_escape_string($_POST['task_id']);
    $status = $conn -> real_escape_string($_POST['status']);
    

    // Update query
    $sql = "UPDATE tasks SET task_status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $status, $task_id);
    $stmt->execute();
  

    $stmt->close();
    $conn->close();
}
header("Location: ../../task.php");
exit();