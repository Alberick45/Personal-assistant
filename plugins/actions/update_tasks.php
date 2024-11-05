<?php
session_start();
require_once("config.php");

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $taskid = $conn->real_escape_string($_POST['task']);

    // Update Task Description
    if (isset($_POST['description']) && !empty($_POST['description'])) {
        $task_description = $conn->real_escape_string($_POST['description']);
        $sql = "UPDATE tasks SET task_description = '$task_description' WHERE id = $taskid";
        $conn->query($sql);
    }

    // Update Assignee
    if (isset($_POST['assignee']) && !empty($_POST['assignee'])) {
        $assignee = $conn->real_escape_string($_POST['assignee']);
        $sql = "UPDATE tasks SET task_assignee = '$assignee' WHERE id = $taskid";
        $conn->query($sql);
    }

    // Update Deadline
    if (isset($_POST['deadline']) && !empty($_POST['deadline'])) {
        $deadline = $conn->real_escape_string($_POST['deadline']);
        $sql = "UPDATE tasks SET deadline = '$deadline' WHERE id = $taskid";
        $conn->query($sql);
    }

    // Update Reminder
    if (isset($_POST['reminder']) && !empty($_POST['reminder'])) {
        $reminder = $conn->real_escape_string($_POST['reminder']);
        $sql = "UPDATE tasks SET reminder_interval = '$reminder', reminder_status = 'Yes' WHERE id = $taskid";
        $conn->query($sql);
    }

   // Update Repeat
if (isset($_POST['repeat']) && !empty($_POST['repeat'])) {
    // Use explode to split the repeat value into interval and unit
    list($repeat_interval, $repeat_unit) = explode(',', $conn->real_escape_string($_POST['repeat']));

    // Validate repeat_unit
    $valid_units = ['DAY', 'WEEK', 'MONTH', 'YEAR'];
    if (!in_array($repeat_unit, $valid_units)) {
        die("Invalid repeat unit.");
    }

    // Prepare the SQL statement
    $sql = "UPDATE tasks 
            SET repeat_interval = ?, 
                repeat_unit = ?, 
                repeat_status = 'Yes' 
            WHERE id = ?";
    
    // Prepare statement
    $stmt = $conn->prepare($sql);
    // Bind parameters
    $stmt->bind_param("isi", $repeat_interval, $repeat_unit, $taskid);
    
    // Execute the statement
    if ($stmt->execute()) {
        echo "Task updated successfully.";
    } else {
        echo "Error updating task: " . $stmt->error;
    }
    
    // Close the statement
    $stmt->close();
}


    // Handle File Upload
    if (isset($_FILES['file']['name']) && !empty($_FILES['file']['name'])) {
        $file_name = $_FILES['file']['name'];
        $tmp_name = $_FILES['file']['tmp_name'];
        $error = $_FILES['file']['error'];

        if ($error === 0) {
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_exts = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];

            if (in_array($file_ext, $allowed_exts)) {
                $new_file_name = uniqid('task_', true).'-'.$user_id . '.' . $file_ext;
                $file_upload_path = '../tasks/files/' . $new_file_name;

                // Move the uploaded file
                // move_uploaded_file($tmp_name, $file_upload_path);
                 // Check if the file was moved successfully
                if (move_uploaded_file($tmp_name, $file_upload_path)) {
                    // Update file path in the database
                    $sql = "UPDATE tasks SET task_resource_filename = '$new_file_name' WHERE id = $taskid";
                    $conn->query($sql);
                } else {
                    $em = "Failed to move uploaded file!";
                    header("Location: ../../tasks.php?error=$em");
                    exit;
                }

                // Update file path in the database
                $sql = "UPDATE tasks SET task_resource_filename = '$new_file_name' WHERE id = $taskid";
                $conn->query($sql);
            } else {
                $em = "You can't upload files of this type.";
                header("Location: ../../tasks.php?error=$em");
                exit;
            }
        } else {
            $em = "Unknown error occurred during file upload!";
            header("Location: ../../tasks.php?error=$em");
            exit;
        }
    }

    // Redirect after successful update
    header('Location: ../../tasks.php?success=Task updated successfully');
    $conn->close();
    exit();
}
?>