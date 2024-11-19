<?php
// Include the database connection (adjust the path if needed)
require_once 'config.php';  // This file should include your database connection details

// Get the current date and time
$current_time = date('Y-m-d H:i:s');

// Prepare the SQL query to check for reminders
$sql = "SELECT task_description, reminder_interval FROM tasks WHERE reminder_status = 'Yes' AND reminder_interval <= ?";
$stmt = $conn->prepare($sql);

// Bind the parameter for the current time
$stmt->bind_param("s", $current_time);

// Execute the query
$stmt->execute();

// Get the result
$result = $stmt->get_result();

// Fetch all tasks that have reminders and are due
$tasks = $result->fetch_all(MYSQLI_ASSOC);

// Debug: Print fetched tasks (for troubleshooting purposes)
if ($tasks) {
    // Update reminder status to 'No' after sending reminder
    $update_sql = "UPDATE tasks SET reminder_status = 'No' WHERE reminder_status = 'Yes' AND reminder_interval <= ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("s", $current_time);
    $update_stmt->execute();
    
    // Return the first task reminder message
    $response = ['status' => 'success', 'reminder' => ['message' => $tasks[0]['task_description']]];
    
    $update_stmt->close();
} else {
    // If no reminders are found
    $response = ['status' => 'error', 'message' => 'No reminders at this time'];
}

// Return the response as JSON
echo json_encode($response);

// Close the statement and connection


$stmt->close();
$conn->close();
?>
