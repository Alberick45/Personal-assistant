<?php
session_start();
require_once("config.php");

if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_FILES['task_file'])) {
    $task_file = $_FILES['task_file'];

     // Update Reminder
    //  if (!empty($_POST['reminder'])) {
    //     $reminder = $conn->real_escape_string($_POST['reminder']);
    //     $sql = "UPDATE tasks SET reminder_interval = '$reminder', reminder_status = 'Yes' WHERE id = $taskid";
    //     if (!$conn->query($sql)) {
    //         header("Location: ../../tasks.php?error=Failed to update reminder.");
    //         exit();
    //     }
    // }

    // Update Repeat
// if (isset($_POST['repeat']) && !empty($_POST['repeat'])) {
//     // Use explode to split the repeat value into interval and unit
//     list($repeat_interval, $repeat_unit) = explode(',', $conn->real_escape_string($_POST['repeat']));

//     // Validate repeat_unit
//     $valid_units = ['DAY', 'WEEK', 'MONTH', 'YEAR'];
//     if (!in_array($repeat_unit, $valid_units)) {
//         die("Invalid repeat unit.");
//     }

//     // Prepare the SQL statement
//     $sql = "UPDATE tasks 
//             SET repeat_interval = ?, 
//                 repeat_unit = ?, 
//                 repeat_status = 'Yes' 
//             WHERE id = ?";
    
//     // Prepare statement
//     $stmt = $conn->prepare($sql);
//     // Bind parameters
//     $stmt->bind_param("isi", $repeat_interval, $repeat_unit, $taskid);
    
//     // Execute the statement
//     if ($stmt->execute()) {
//         echo "Task updated successfully.";
//     } else {
//         echo "Error updating task: " . $stmt->error;
//     }
    
//     // Close the statement
//     $stmt->close();
// }


    // Check if the file was uploaded without errors
    if ($task_file['error'] === 0) {
        $task_file_path = $task_file['tmp_name'];

        // Validate file type (only allow CSV)
        $file_ext = strtolower(pathinfo($task_file['name'], PATHINFO_EXTENSION));
        if ($file_ext !== 'csv') {
            die("Invalid file type. Please upload a CSV file.");
        }

        // Open the CSV file for reading
        if (($handle = fopen($task_file_path, "r")) !== FALSE) {
            // Get and validate the CSV headers
            $headers = fgetcsv($handle, 1000, ",");
            $expected_headers = ['Task', 'Date_time_start', 'Duration'];

            if (count($headers) !== 3) {
                die("Invalid CSV format. The file must have exactly 3 columns.");
            }

            if ($headers !== $expected_headers) {
                die("Invalid CSV header. Expected: 'Task', 'Date_time_start', 'Duration'");
            }

            // Loop through the CSV rows and insert data into the database
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $task = $conn->real_escape_string($data[0]);
                $date_time_start = $conn->real_escape_string($data[1]);
                $duration = $conn->real_escape_string($data[2]);

                // Format the data
                $description = "$task for $duration seconds";

                // Convert the date string into MySQL's DATETIME format
                $formatted_date = date('Y-m-d H:i:s', strtotime($date_time_start));

                $user_id = $_SESSION['user_id']; // Assuming user ID is stored in session

                // Insert data into the tasks table
                $query = "
                    INSERT INTO tasks (task_name, deadline, task_duration, task_description, task_assigner,task_status)
                    VALUES ('$task', '$formatted_date', '$duration', '$description', '$user_id','PENDING')
                ";

                // Execute the query and check for errors
                if (!$conn->query($query)) {
                    echo "Error: " . $conn->error;
                }
            }

            // Close the file and database connection
            fclose($handle);
            $conn->close();

            // Redirect with a success message
            header('Location: ../../task.php?success=CSV file data successfully imported!');
            exit();
        } else {
            echo "Error opening the file.";
        }
    } else {
        echo "Error uploading the file.";
    }
} else {
    echo "No file was uploaded.";
}
exit();
