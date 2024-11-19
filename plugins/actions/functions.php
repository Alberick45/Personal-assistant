<?php
require_once("config.php");  // Include the config file to access the database connection

// Function to get the number of pending tasks for the user
function getMyDayCount($user_id, $conn) {
    $query = "SELECT count(*) as number_myday 
              FROM tasks 
              WHERE task_status = 'PENDING' AND DATE(deadline) = CURDATE() AND (task_assigner = ? OR task_assignee = ?)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['number_myday'] ?? 0;
}

// Function to retrieve user data
function getUserData($user_id, $conn) {
    $query = "SELECT * FROM users WHERE id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Function to get the sum of transactions for a specific category (income or expense)
function getTransactionSum($user_id, $category, $conn) {
    $query = "SELECT sum(total_amount) as total_amount 
              FROM transactions t 
              LEFT JOIN financial_category fc ON t.category_id = fc.category_id 
              WHERE t.user_id = ? AND fc.category = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $user_id, $category);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['total_amount'] ?? 0;
}

// Function to get the sum of cashflow for a specific category (asset or liability)
function getCashflow($user_id, $category, $conn) {
    $query = "SELECT sum(abs(cashflow)) as total_cashflow 
              FROM resources r 
              LEFT JOIN financial_category fc ON r.category_id = fc.category_id 
              WHERE r.user_id = ? AND fc.category = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $user_id, $category);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['total_cashflow'] ?? 0;
}

// Function to add a diary entry
function addDiaryEntry($user_id, $entry, $date, $title, $conn) {
    $query = "INSERT INTO Diary (diary_title, diary_entry, entry_date, diary_owner) 
              VALUES (?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssi", $title, $entry, $date, $user_id);
    return $stmt->execute();
}

// Function to add a website entry
function addWebsiteEntry($user_id, $name, $url, $description, $category, $conn) {
    $query = "INSERT INTO favourite_sites (website_name, website_url, website_description, website_category, website_user) 
              VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssi", $name, $url, $description, $category, $user_id);
    return $stmt->execute();
}

// Function to update user balance
function updateUserBalance($user_id, $balance, $conn) {
    $query = "UPDATE users SET balance = ? WHERE id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("di", $balance, $user_id);
    return $stmt->execute();
}

// Function to count important tasks
function getImportantTaskCount($user_id, $conn) {
    $query = "SELECT count(*) as number_important FROM tasks WHERE task_status = 'PENDING' AND task_importance = 'Important' AND (task_assigner = ? OR task_assignee = ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc()['number_important'] ?? 0;
    $stmt->close();
    return $result;
}

// Function to count all tasks
function getTotalTaskCount($user_id, $conn) {
    $query = "SELECT count(*) as number_tasks FROM tasks WHERE (task_assigner = ? OR task_assignee = ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc()['number_tasks'] ?? 0;
    $stmt->close();
    return $result;
}

// for diet
function addDish($conn, $user_id, $dish_name, $day, $time, $dish_details = '') {
    $dish_details_json = json_encode($dish_details, JSON_UNESCAPED_SLASHES);
    $query = "INSERT INTO menu (user_id, dish_details, dish_name) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('iss', $user_id, $dish_details_json, $dish_name);
    return $stmt->execute();
}

function updateDish($conn, $user_id, $dish_name, $day, $time) {
    $query = "
        UPDATE menu 
        SET dish_name = ? 
        WHERE user_id = ? 
        AND JSON_UNQUOTE(JSON_EXTRACT(dish_details, '$.day')) = ? 
        AND JSON_UNQUOTE(JSON_EXTRACT(dish_details, '$.time')) = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('siss', $dish_name, $user_id, $day, $time);
    return $stmt->execute();
}


function addIngredient($user_id, $name, $price, $quantity) {
    global $conn;
    
    $query = "INSERT INTO inventory (ingredient_name, price_per_unit, total_quantity, user_id) 
              VALUES ('$name', '$price', $quantity, $user_id)";
    
    if ($conn->query($query) === TRUE) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        return "Error: " . $query . "<br>" . $conn->error;
    }
}



//finance


function addTransaction($user_id, $category_id, $amount, $time, $note, $conn) {
    $query = "INSERT INTO transactions (category_id, user_id, total_amount, transaction_time, note) 
              VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iisss", $category_id, $user_id, $amount, $time, $note);
    return $stmt->execute();
}

function updateBudgetRemaining($category_id, $amount, $note, $conn) {
    $query = "UPDATE budget SET budget_amount_remaining = (budget_amount - ?), note = ? 
              WHERE category_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("dsi", $amount, $note, $category_id);
    return $stmt->execute();
}

function addBudget($user_id, $category_id, $amount, $month, $note, $conn) {
    $query = "INSERT INTO budget (category_id, user_id, budget_amount, budget_amount_remaining, budget_month, note) 
              VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iidsss", $category_id, $user_id, $amount, $amount, $month, $note);
    return $stmt->execute();
}

function addResource($user_id, $category_id, $item_name, $amount, $description, $conn) {
    $query = "INSERT INTO resources (item_name, item_price, user_id, category_id, item_description) 
              VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sdis", $item_name, $amount, $user_id, $category_id, $description);
    return $stmt->execute();
}

function updateResource($resource_id, $name, $description, $notes, $price, $cashflow, $conn) {
    $query = "UPDATE resources SET item_name = ?, item_description = ?, personal_notes = ?, 
              item_price = ?, cashflow = ? WHERE resource_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssdi", $name, $description, $notes, $price, $cashflow, $resource_id);
    return $stmt->execute();
}


// Function to generate each financial section with an optional filter
function generateSection($resultSet, $sectionTitle, $valueClass, $currency = 'GH₵', $categoryFilter = null) {
    $hasData = false; // Track if there's any data to display

    echo "<header class='bg-secondary'><h2 class='text-white'>$sectionTitle</h2></header><main>";
    
    if ($resultSet->num_rows > 0) {
        while ($row = $resultSet->fetch_assoc()) {
            // Apply the filter if provided
            if ($categoryFilter === null || $row['category_name'] == $categoryFilter) {
                $hasData = true;
                echo "<div class='d-flex justify-content-between' id='{$sectionTitle}-{$row['transaction_id']}'>";
                echo "<h3>{$row['category_name']}</h3>";
                echo "<h3 class='d-flex justify-content-end $valueClass'>{$row['total_amount']} $currency</h3>";
                echo "</div><hr style='color: black; border: 1px;'>";
            }
        }
    }

    if (!$hasData) {
        echo "<p>No data available for $sectionTitle.</p>";
    }

    echo "</main>";
}

// tasks

// Function to add a new task, including optional file upload handling
function addTask($task_name, $task_deadline, $task_duration, $task_description, $task_assigner, $user_id, $file = null, $conn) {
    // Default task status
    $task_status = 'PENDING';

    // Check if there's a file to upload
    if ($file && isset($file['name']) && !empty($file['name'])) {
        $file_name = $file['name'];
        $tmp_name = $file['tmp_name'];
        $error = $file['error'];

        if ($error === 0) {
            $file_ex = pathinfo($file_name, PATHINFO_EXTENSION);
            $file_ex_to_lc = strtolower($file_ex);
            $allowed_exs = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];

            if (in_array($file_ex_to_lc, $allowed_exs)) {
                $new_file_name = uniqid('', true) . '.' . $file_ex_to_lc . '-' . $user_id;
                $file_upload_path = 'plugins/tasks/file' . $new_file_name;
                move_uploaded_file($tmp_name, $file_upload_path);

                // Prepare SQL with file
                $query = "INSERT INTO tasks (task_name, deadline, task_duration, task_description, task_status, task_assigner, task_resource_filename) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("sssssis", $task_name, $task_deadline, $task_duration, $task_description, $task_status, $task_assigner, $new_file_name);
            } else {
                throw new Exception("Invalid file type.");
            }
        } else {
            throw new Exception("File upload error.");
        }
    } else {
        // SQL without file
        $query = "INSERT INTO tasks (task_name, deadline, task_duration, task_description, task_status, task_assigner) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssi", $task_name, $task_deadline, $task_duration, $task_description, $task_status, $task_assigner);
    }

    if ($stmt->execute()) {
        return true;
    } else {
        throw new Exception("Error adding task: " . $stmt->error);
    }
}


// Function to count pending tasks for a user
function countPendingTasks($user_id, $conn) {
    $query = "SELECT count(*) as number_tasks FROM tasks WHERE task_status = 'PENDING' AND (task_assigner = ? OR task_assignee = ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['number_tasks'];
    }
    return 0;
}


// Function to fetch tasks based on status for a user
function getTasksByStatus($status, $user_id, $conn) {
    $query = "SELECT * FROM tasks WHERE task_status = ? AND (task_assigner = ? OR task_assignee = ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sii", $status, $user_id, $user_id);
    $stmt->execute();
    return $stmt->get_result();
}

function getTasksByStatusAndDate($user_id, $status, $conn) {
    $query = "SELECT * FROM tasks WHERE task_status = ? AND DATE(deadline) = CURDATE() AND (task_assigner = ? OR task_assignee = ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sii", $status, $user_id, $user_id);
    $stmt->execute();
    return $stmt->get_result();
}

// Count tasks by status and optional date filter
function getTaskCountByStatus($user_id, $status, $date, $conn) {
    $query = "SELECT count(*) as task_count FROM tasks WHERE task_status = ? AND DATE(deadline) = ? AND (task_assigner = ? OR task_assignee = ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssii", $status, $date, $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['task_count'];
}



// Function to fetch completed tasks
function getCompletedTasks($user_id, $conn) {
    $query = "SELECT * FROM tasks WHERE task_status = 'COMPLETED' AND task_importance = 'Important' AND (task_assigner = ? OR task_assignee = ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();
    return $stmt->get_result();
}

// Function to fetch important tasks count
function getImportantTasksCount($user_id, $conn) {
    $query = "SELECT COUNT(*) as number_important FROM tasks WHERE task_status = 'PENDING' AND task_importance = 'Important' AND (task_assigner = ? OR task_assignee = ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['number_important'];
}

// Function to fetch important tasks list
function getImportantTasks($user_id, $conn) {
    $query = "SELECT * FROM tasks WHERE task_status = 'PENDING' AND task_importance = 'Important' AND (task_assigner = ? OR task_assignee = ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();
    return $stmt->get_result();
}
// Function for handling file uploads
function uploadTaskFile($file, $upload_dir = '../uploads/tasks/') {
    $allowed_exs = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
    $file_name = $file['name'];
    $tmp_name = $file['tmp_name'];
    $error = $file['error'];

    if ($error === 0) {
        $file_ex = pathinfo($file_name, PATHINFO_EXTENSION);
        $file_ex_to_lc = strtolower($file_ex);

        if (in_array($file_ex_to_lc, $allowed_exs)) {
            $new_file_name = uniqid('', true) . '.' . $file_ex_to_lc;
            $file_upload_path = $upload_dir . 'file' . $new_file_name;

            if (move_uploaded_file($tmp_name, $file_upload_path)) {
                return $new_file_name; // Return the new file name if upload succeeds
            } else {
                throw new Exception("Failed to move uploaded file.");
            }
        } else {
            throw new Exception("File type not allowed.");
        }
    } else {
        throw new Exception("Unknown error occurred during file upload.");
    }
}

// Function for inserting a task into the database
function insertTask($conn, $task_name, $task_deadline, $task_duration, $task_description, $task_assigner, $file_name = null) {
    $task_status = 'PENDING';
    $task_importance = 'Important';

    $query = "INSERT INTO tasks (task_name, deadline, task_duration, task_description, task_status, task_assigner, task_importance, task_resource_filename) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssiss", $task_name, $task_deadline, $task_duration, $task_description, $task_status, $task_assigner, $task_importance, $file_name);

    if (!$stmt->execute()) {
        throw new Exception("Error executing query: " . $stmt->error);
    }
    return true;
}


function importTasksFromCSV($conn, $task_file, $user_id) {
    // Check if the file was uploaded without errors
    if ($task_file['error'] !== 0) {
        return "Error uploading the file.";
    }
    
    $task_file_path = $task_file['tmp_name'];
    $file_ext = strtolower(pathinfo($task_file['name'], PATHINFO_EXTENSION));
    
    // Validate file type (only allow CSV)
    if ($file_ext !== 'csv') {
        return "Invalid file type. Please upload a CSV file.";
    }

    // Open the CSV file for reading
    if (($handle = fopen($task_file_path, "r")) !== FALSE) {
        // Validate the CSV headers
        $headers = fgetcsv($handle, 1000, ",");
        $expected_headers = ['Task', 'Date_time_start', 'Duration'];
        
        if (count($headers) !== count($expected_headers) || $headers !== $expected_headers) {
            return "Invalid CSV format. Expected headers: 'Task', 'Date_time_start', 'Duration'";
        }

        // Loop through the CSV rows and insert data into the database
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $task = $conn->real_escape_string($data[0]);
            $date_time_start = $conn->real_escape_string($data[1]);
            $duration = $conn->real_escape_string($data[2]);
            
            $description = "$task for $duration seconds";
            $formatted_date = date('Y-m-d H:i:s', strtotime($date_time_start));

            // Insert data into the tasks table
            $query = "
                INSERT INTO tasks (task_name, deadline, task_duration, task_description, task_assigner, task_status)
                VALUES ('$task', '$formatted_date', '$duration', '$description', '$user_id', 'PENDING')
            ";

            // Execute the query and check for errors
            if (!$conn->query($query)) {
                fclose($handle);
                return "Error: " . $conn->error;
            }
        }

        // Close the file
        fclose($handle);
        return "CSV file data successfully imported!";
    } else {
        return "Error opening the file.";
    }
}


/* function importTimetableFromCSV($conn, $timetable_file, $user_id) {
    // Check if the file was uploaded without errors
    if ($timetable_file['error'] === 0) {
        $timetable_file_path = $timetable_file['tmp_name'];

        // Validate file type (only allow CSV)
        $file_ext = strtolower(pathinfo($timetable_file['name'], PATHINFO_EXTENSION));
        if ($file_ext !== 'csv') {
            return "Invalid file type. Please upload a CSV file.";
        }

        // Open the CSV file for reading
        if (($handle = fopen($timetable_file_path, "r")) !== FALSE) {
            // Get and validate the CSV headers
            $headers = fgetcsv($handle, 1000, ",");
            $expected_headers = ['Subject', 'Date_time_start', 'Duration', 'Location'];

            if (count($headers) !== 4) {
                return "Invalid CSV format. The file must have exactly 4 columns.";
            }

            if ($headers !== $expected_headers) {
                return "Invalid CSV header. Expected: 'Subject', 'Date_time_start', 'Duration', 'Location'.";
            }

            // Loop through the CSV rows and insert data into the database
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $subject = $conn->real_escape_string($data[0]);
                $date_time_start = $conn->real_escape_string($data[1]);
                $duration = $conn->real_escape_string($data[2]);
                $location = $conn->real_escape_string($data[3]);

                // Format the data
                $taskname = "Learn " . $subject;
                $description = "$subject for $duration seconds at $location";

                // Convert the date string into MySQL's DATETIME format
                $formatted_date = date('Y-m-d H:i:s', strtotime($date_time_start));

                // Insert data into the tasks table
                $query = "
                    INSERT INTO tasks (task_name, deadline, task_duration, task_description, task_assigner, task_status)
                    VALUES ('$taskname', '$formatted_date', '$duration', '$description', '$user_id', 'PENDING')
                ";

                // Execute the query and check for errors
                if (!$conn->query($query)) {
                    return "Error: " . $conn->error;
                }
            }

            // Close the file
            fclose($handle);

            return "CSV file data successfully imported!";
        } else {
            return "Error opening the file.";
        }
    } else {
        return "Error uploading the file.";
    }
} */

function importTimetableFromCSV($conn, $timetable_file, $user_id) {
    // Check if the file was uploaded without errors
    if ($timetable_file['error'] === 0) {
        $timetable_file_path = $timetable_file['tmp_name'];

        // Validate file type (only allow CSV)
        $file_ext = strtolower(pathinfo($timetable_file['name'], PATHINFO_EXTENSION));
        if ($file_ext !== 'csv') {
            return "Invalid file type. Please upload a CSV file.";
        }

        // Open the CSV file for reading
        if (($handle = fopen($timetable_file_path, "r")) !== FALSE) {
            // Read the first row to get the time range headers
            $time_headers = fgetcsv($handle, 1000, ",");

            // Ensure the first cell of the time headers is empty (top-left corner)
            if (trim($time_headers[0]) !== "") {
                return "Invalid CSV format. The top-left cell must be empty.";
            }

            // Loop through the remaining rows to process days and tasks
            while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // The first cell in each row is the day
                $day = $conn->real_escape_string($row[0]);

                // Iterate through the rest of the row for time-task pairs
                for ($i = 1; $i < count($row); $i++) {
                    $task_details = trim($row[$i]);

                    // Skip empty cells
                    if (empty($task_details)) {
                        continue;
                    }

                    // Process the time range from the header
                    $time_range = $time_headers[$i];
                    $time_parts = explode('-', $time_range);

                    if (count($time_parts) !== 2) {
                        return "Invalid time range format in column $i. Expected format: 'HH:MM-HH:MM'.";
                    }

                    // Parse start and end times
                    $start_time = date('H:i:s', strtotime(trim($time_parts[0])));
                    $end_time = date('H:i:s', strtotime(trim($time_parts[1])));

                    // Combine day and start time to create a datetime for the task
                    $date_time_start = date('Y-m-d H:i:s', strtotime("$day $start_time"));
                    $date_time_end = date('Y-m-d H:i:s', strtotime("$day $end_time"));

                    // Calculate task duration in seconds
                    $start_datetime = strtotime("$day $start_time");
                    $end_datetime = strtotime("$day $end_time");
                    $duration_seconds = $end_datetime - $start_datetime;

                    // Prepare task details
                    $taskname = "Task on $day ($time_range)";
                    $description = $conn->real_escape_string($task_details);

                    // Insert data into the tasks table
                    $query = "
                        INSERT INTO tasks (task_name, deadline, task_duration, task_description, task_assigner, task_status)
                        VALUES ('$taskname', '$date_time_start', '$duration_seconds', '$description', '$user_id', 'PENDING')
                    ";

                    // Execute the query and check for errors
                    if (!$conn->query($query)) {
                        return "Error: " . $conn->error;
                    }
                }
            }

            // Close the file
            fclose($handle);

            return "Timetable with time ranges and task durations successfully imported!";
        } else {
            return "Error opening the file.";
        }
    } else {
        return "Error uploading the file.";
    }
}




// Function to display session messages
function displaySessionMessage() {
    if (isset($_SESSION['message'])) {
        echo $_SESSION['message'];
        unset($_SESSION['message']);
    }
}

// contacts
function createContact($user_id, $contact_name, $contact_phone, $address, $birthday = null) {
    global $conn;

    $sql = "INSERT INTO contacts (Contact_name, Contact_phone, Address, Birthday, user_id) 
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $contact_name, $contact_phone, $address, $birthday, $user_id);

    return $stmt->execute();
}

function editContact($contact_id, $user_id, $contact_name, $contact_phone, $address, $birthday = null) {
    global $conn;

    $sql = "UPDATE contacts 
            SET Contact_name = ?, Contact_phone = ?, Address = ?, Birthday = ?
            WHERE Contact_id = ? AND user_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssii", $contact_name, $contact_phone, $address, $birthday, $contact_id, $user_id);

    return $stmt->execute();
}

function deleteContact($contact_id, $user_id) {
    global $conn;

    $sql = "DELETE FROM contacts WHERE Contact_id = ? AND user_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $contact_id, $user_id);

    return $stmt->execute();
}

function getContacts($user_id) {
    global $conn;

    $sql = "SELECT * FROM contacts WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    return null; // No contacts found
}
?>
