
<?php
session_start();
require_once("config.php");

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect if not logged in
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty($_POST['task'])) {
        header("Location: ../../tasks.php?error=Task ID is required.");
        exit();
    }

    $taskid = $conn->real_escape_string($_POST['task']);

    // Update Task Description
    if (!empty($_POST['description'])) {
        $task_description = $conn->real_escape_string($_POST['description']);
        $sql = "UPDATE tasks SET task_description = '$task_description' WHERE id = $taskid";
        if (!$conn->query($sql)) {
            header("Location: ../../tasks.php?error=Failed to update description.");
            exit();
        }
    }

    // Update Assignee
    if (!empty($_POST['assignee'])) {
        $assignee = $conn->real_escape_string($_POST['assignee']);
        $sql = "UPDATE tasks SET task_assignee = '$assignee' WHERE id = $taskid";
        if (!$conn->query($sql)) {
            header("Location: ../../tasks.php?error=Failed to update assignee.");
            exit();
        }
    }

    // Update Deadline
    if (!empty($_POST['deadline'])) {
        $deadline = $conn->real_escape_string($_POST['deadline']);
        $sql = "UPDATE tasks SET deadline = '$deadline' WHERE id = $taskid";
        if (!$conn->query($sql)) {
            header("Location: ../../tasks.php?error=Failed to update deadline.");
            exit();
        }
    }

    // Update Reminder
    if (!empty($_POST['reminder'])) {
        $reminder = $conn->real_escape_string($_POST['reminder']);
        $sql = "UPDATE tasks SET reminder_interval = '$reminder', reminder_status = 'Yes' WHERE id = $taskid";
        if (!$conn->query($sql)) {
            header("Location: ../../tasks.php?error=Failed to update reminder.");
            exit();
        }
    }

    // Update Repeat
    if (!empty($_POST['repeat'])) {
        $repeat = $conn->real_escape_string($_POST['repeat']);
        $sql = "UPDATE tasks SET repeat_interval = '$repeat', repeat_status = 'Yes' WHERE id = $taskid";
        if (!$conn->query($sql)) {
            header("Location: ../../tasks.php?error=Failed to update repeat interval.");
            exit();
        }
    }

    // Handle File Upload
    if (isset($_FILES['file']) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file_name = $_FILES['file']['name'];
        $tmp_name = $_FILES['file']['tmp_name'];
        $error = $_FILES['file']['error'];

        if ($error === UPLOAD_ERR_OK) {
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_exts = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];

            if (in_array($file_ext, $allowed_exts)) {
                $new_file_name = uniqid('task_', true) . '.' . $file_ext;
                $file_upload_path = '../tasks/files/' . $new_file_name;

                // Ensure the directory exists
                if (!is_dir('../tasks/files')) {
                    mkdir('../tasks/files', 0775, true);
                }

                // Move the uploaded file
                if (move_uploaded_file($tmp_name, $file_upload_path)) {
                    $sql = "UPDATE tasks SET task_resource_filename = '$new_file_name' WHERE id = $taskid";
                    if (!$conn->query($sql)) {
                        header("Location: ../../tasks.php?error=Failed to update file path in the database.");
                        exit();
                    }
                } else {
                    header("Location: ../../tasks.php?error=Failed to move uploaded file!");
                    exit();
                }
            } else {
                header("Location: ../../tasks.php?error=Invalid file type.");
                exit();
            }
        } else {
            $error_message = match ($error) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => "File is too large.",
                UPLOAD_ERR_PARTIAL => "File was only partially uploaded.",
                UPLOAD_ERR_NO_TMP_DIR => "Temporary folder is missing.",
                UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk.",
                default => "Unknown file upload error.",
            };
            header("Location: ../../tasks.php?error=$error_message");
            exit();
        }
    }

    // Redirect after successful update
    header('Location: ../../tasks.php?success=Task updated successfully');
    $conn->close();
    exit();
}
?>
