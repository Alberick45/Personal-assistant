<?php
// Include database connection (adjust the path if needed)
require_once 'config.php';

// Get the current date and time
$current_time = date('Y-m-d H:i:s');

// Prepare the SQL query to check for completed tasks that need to be repeated
$sql = "SELECT id, task_name, task_description, repeat_interval, repeat_unit, deadline 
        FROM tasks 
        WHERE repeat_status = 'Yes' 
        AND task_status = 'COMPLETED'";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die(json_encode(['status' => 'error', 'message' => 'Failed to prepare SQL statement']));
}
$stmt->execute();
$result = $stmt->get_result();

// Fetch tasks that need to be repeated
$tasks = $result->fetch_all(MYSQLI_ASSOC);

// If there are tasks to repeat, update their status
if ($tasks) {
    foreach ($tasks as $task) {
        // Determine the interval unit dynamically
        $interval_unit = strtoupper($task['repeat_unit']); // Ensure unit is uppercase
        $valid_units = ['DAY', 'HOUR', 'MONTH', 'YEAR']; // Define valid interval units

        if (!in_array($interval_unit, $valid_units)) {
            continue; // Skip invalid units
        }

        // Handle interval formatting for DateInterval
        $date_interval = "";
        switch ($interval_unit) {
            case 'DAY':
                $date_interval = "P{$task['repeat_interval']}D";
                break;
            case 'MONTH':
                $date_interval = "P{$task['repeat_interval']}M";
                break;
            case 'YEAR':
                $date_interval = "P{$task['repeat_interval']}Y";
                break;
            case 'HOUR':
                $date_interval = "PT{$task['repeat_interval']}H";
                break;
        }

        try {
            // Calculate the new deadline by adding the repeat interval
            $deadline = new DateTime($task['deadline']);
            $deadline->add(new DateInterval($date_interval)); // Add the interval

            // Dynamically update the task
            $update_sql = "UPDATE tasks 
                           SET task_status = 'PENDING', 
                               deadline = ? 
                           WHERE id = ?";
            
            // Prepare the update statement
            $update_stmt = $conn->prepare($update_sql);
            if (!$update_stmt) {
                continue; // Skip if the statement couldn't be prepared
            }

            // Bind the parameters: new deadline and task id
            $new_deadline = $deadline->format('Y-m-d H:i:s');
            $update_stmt->bind_param("si", $new_deadline, $task['id']);

            // Execute the update statement
            $update_stmt->execute();
        } catch (Exception $e) {
            // Handle DateInterval or DateTime errors gracefully
            error_log("Error updating task ID {$task['id']}: " . $e->getMessage());
            continue;
        }
    }

    // Return success response
    echo json_encode(['status' => 'success', 'message' => 'Tasks repeated successfully']);
} else {
    // If no tasks are found for repetition
    echo json_encode(['status' => 'error', 'message' => 'No tasks to repeat']);
}

// Close the database connection
$stmt->close();
$conn->close();
?>
