<?php
require_once("config.php");
session_start();


// Handle the checkbox submission
if (isset($_POST['task_id'])) {
    $task_id = $conn -> real_escape_string($_POST['task_id']);
    

    // Update query
    $sql = "DELETE FROM tasks WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i',  $task_id);
    $stmt->execute();
  

    $stmt->close();
    $conn->close();
}
header("Location: ../../task.php");
exit();