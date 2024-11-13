<?php
// family_functions.php

require_once("config.php");



 // Function to get a single count result from tasks
 function getPendingTasksCount($conn, $family_id) {
    $query = "SELECT count(*) as number_myday FROM tasks WHERE task_status = 'PENDING' AND DATE(deadline) = CURDATE() AND family_id = $family_id";
    $result = $conn->query($query);
    return $result->num_rows > 0 ? $result->fetch_assoc()['number_myday'] : 0;
}

// Function to get family details
function getFamilyDetails($conn, $family_id) {
    $query = "SELECT * FROM family WHERE id = $family_id";
    $result = $conn->query($query);
    return $result->num_rows > 0 ? $result->fetch_assoc() : [];
}
// add family_id8*****************************************
function getResourceDetails($conn, $resource_id) {
    $query = "SELECT * FROM resources WHERE resource_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $resource_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Function to retrieve user data
function getUserData($user_id, $conn) {
    $query = "SELECT * FROM users WHERE id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Function to update user balance
function updateUserBalance($user_id, $balance, $conn) {
    $query = "UPDATE users SET balance = ? WHERE id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("di", $balance, $user_id);
    return $stmt->execute();
}

// Function to run a general query with financial categories
function getFinancialData($conn, $table, $family_id, $category = null) {
    $categoryFilter = $category ? "AND fc.category = '$category'" : '';
    $query = "SELECT * FROM $table t LEFT JOIN financial_category fc ON t.category_id = fc.category_id WHERE t.family_id = $family_id $categoryFilter";
    return $conn->query($query);
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



// Function to get sum total by category
function getCategorySum($conn, $family_id, $category) {
    $query = "SELECT sum(total_amount) as sum_total FROM transactions t LEFT JOIN financial_category fc ON t.category_id = fc.category_id WHERE t.family_id = $family_id AND fc.category = '$category'";
    $result = $conn->query($query);
    return $result->num_rows > 0 ? $result->fetch_assoc()['sum_total'] : 0;
}



function getMyDayCount($conn, $family_id) {
    $query = "SELECT count(*) as number_myday FROM tasks WHERE task_status = 'PENDING' AND DATE(deadline) = CURDATE() AND family_id = $family_id";
    $results = $conn->query($query);
    if ($results && $results->num_rows > 0) {
        return $results->fetch_assoc()['number_myday'];
    }
    return 0;
}



function getBudget($conn, $family_id) {
    $query = "SELECT * FROM budget b LEFT JOIN financial_category fc ON b.category_id = fc.category_id WHERE b.family_id = $family_id";
    return $conn->query($query);
}

function getResources($conn, $family_id) {
    $query = "SELECT * FROM resources r LEFT JOIN financial_category fc ON r.category_id = fc.category_id WHERE r.family_id = $family_id";
    return $conn->query($query);
}

function getTransactions($conn, $family_id, $category = null) {
    $query = "SELECT * FROM transactions t LEFT JOIN financial_category fc ON t.category_id = fc.category_id WHERE t.family_id = $family_id";
    if ($category) {
        $query .= " AND fc.category = '$category'";
    }
    return $conn->query($query);
}

function getSumByCategory($conn, $family_id, $category) {
    $query = "SELECT sum(total_amount) as total FROM transactions t LEFT JOIN financial_category fc ON t.category_id = fc.category_id WHERE t.family_id = $family_id AND fc.category = '$category'";
    $results = $conn->query($query);
    if ($results && $results->num_rows > 0) {
        return $results->fetch_assoc()['total'];
    }
    return 0;
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

function getCashflowByCategory($conn, $family_id, $category) {
    $query = "SELECT sum(abs(cashflow)) as cashflow FROM resources r LEFT JOIN financial_category fc ON r.category_id = fc.category_id WHERE r.family_id = $family_id AND fc.category = '$category'";
    $results = $conn->query($query);
    if ($results && $results->num_rows > 0) {
        return $results->fetch_assoc()['cashflow'];
    }
    return 0;
}

function addTransaction($conn, $family_id, $category, $user, $amount, $time, $note = null) {
    $time = !empty($time) ? $conn->real_escape_string($time) : date('Y/m/d H:i:s');
    $amount = !empty($amount) ? $conn->real_escape_string($amount) : '0.00';
    $note = $note ? $conn->real_escape_string($note) : null;

    $query = "INSERT INTO transactions (category_id, user_id, total_amount, transaction_time, family_id, note) 
              VALUES ($category, $user, '$amount', '$time', $family_id,'$note')";
    $budgetQuery = "UPDATE budget SET budget_amount_remaining = (budget_amount - $amount) WHERE category_id = $category";

    $conn->query($budgetQuery);
    return $conn->query($query);
}

function addBudget($conn, $family_id, $category, $user, $amount, $month_input, $note = null) {
    $amount = !empty($amount) ? $conn->real_escape_string($amount) : '0.00';
    $month = !empty($month_input) ? date('F', strtotime($month_input)) : date('F');
    $note = $note ? $conn->real_escape_string($note) : null;

    $query = "INSERT INTO budget (category_id, family_id, budget_amount, budget_amount_remaining, budget_month, note) 
              VALUES ($category, $family_id, '$amount', '$amount', '$month', '$note')";
    return $conn->query($query);
}

function addResource($conn, $family_id, $category, $user, $name, $amount, $time, $note = null) {
    $time = !empty($time) ? $conn->real_escape_string($time) : date('Y/m/d H:i:s');
    $amount = !empty($amount) ? $conn->real_escape_string($amount) : '0.00';
    $name = !empty($name) ? $conn->real_escape_string($name) : 'unknown';
    $note = $note ? $conn->real_escape_string($note) : null;
    $category = $category ? $conn->real_escape_string($category) : 1;

    $resourceQuery = "INSERT INTO resources (item_name, item_price, family_id, category_id, item_description) 
                      VALUES ('$name', '$amount', $family_id, $category, '$note')";
    $transactionQuery = "INSERT INTO transactions (category_id, family_id, total_amount, transaction_time, note) 
                         VALUES ($category, $family_id, '$amount', '$time', '$note')";

    $conn->query($resourceQuery);
    return $conn->query($transactionQuery);
}

function getPassiveIncome($conn, $family_id) {
    $positiveCashflow = getCashflowByCategory($conn, $family_id, 'asset');
    $negativeCashflow = getCashflowByCategory($conn, $family_id, 'liability');
    return $positiveCashflow - $negativeCashflow;
}

function getTotalIncome($passive_income, $only_income) {
    return $passive_income + $only_income;
}



function updateBalance($conn, $family_id, $total_income, $total_expense) {
    $balance = $total_income - $total_expense;

    $query = "UPDATE family SET balance = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param('di', $balance, $family_id);
        $stmt->execute();
        $stmt->close();
    } else {
        echo "Error: " . $conn->error;
    }
}



function updateResource($conn, $resource_id, $name, $description, $notes, $price, $cashflow) {
    $query = "UPDATE resources 
              SET item_name = ?, item_description = ?, personal_notes = ?, 
                  item_price = ?, cashflow = ? 
              WHERE resource_id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param('ssssdi', $name, $description, $notes, $price, $cashflow, $resource_id);
        $stmt->execute();
        $stmt->close();
    } else {
        echo "Error: " . $conn->error;
    }
}

function addDish($conn, $family_id, $dish_name, $day, $time, $dish_details = '') {
    $dish_details_json = json_encode($dish_details, JSON_UNESCAPED_SLASHES);
    $query = "INSERT INTO menu (dish_details, dish_name, family_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssi', $dish_details_json, $dish_name, $family_id);
    $stmt->execute();
    $stmt->close();
}

function addIngredient($conn, $family_id, $name, $price, $quantity) {
    $query = "INSERT INTO inventory (ingredient_name, price_per_unit, total_quantity, family_id) 
              VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sdii', $name, $price, $quantity, $family_id);
    $stmt->execute();
    $stmt->close();
}

function addDiaryEntry($conn, $family_id, $title, $entry, $date) {
    $query = "INSERT INTO Diary (diary_title, diary_entry, entry_date, diary_owner) 
              VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sssi', $title, $entry, $date, $family_id);
    $stmt->execute();
    $stmt->close();
}


// Function to add a favorite website
function addFavoriteWebsite($conn, $family_id, $name, $url, $description, $category = 'Leisure') {
    $website_user = $family_id;
    $name = !empty($name) ? $conn->real_escape_string($name) : '';
    $url = !empty($url) ? $conn->real_escape_string($url) : '';
    $description = !empty($description) ? $conn->real_escape_string($description) : '';
    $category = !empty($category) ? $conn->real_escape_string($category) : 'Leisure';

    $query = "INSERT INTO favourite_sites (website_name, website_url, website_description, website_category, family_id) 
              VALUES ('$name', '$url', '$description', '$category', $website_user)";
    return $conn->query($query);
}


// Function to add a task with optional file upload
function addTask($conn, $family_id, $task_name, $task_description, $task_deadline, $task_duration, $task_assigner, $file) {
    $fileUploaded = false;
    $new_file_name = null;

    if (!empty($file['name'])) {
        $file_name = $file['name'];
        $tmp_name = $file['tmp_name'];
        $file_error = $file['error'];

        if ($file_error === 0) {
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_exts = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
            if (in_array($file_ext, $allowed_exts)) {
                $new_file_name = uniqid('', true) . ".$file_ext-$family_id";
                $file_upload_path = 'plugins/tasks/file' . $new_file_name;
                move_uploaded_file($tmp_name, $file_upload_path);
                $fileUploaded = true;
            } else {
                return 'Invalid file type';
            }
        } else {
            return 'File upload error';
        }
    }

    $query = $fileUploaded ? 
        "INSERT INTO tasks (task_name, deadline, task_assigner, task_duration, task_description, task_status, family_id, task_resource_filename) 
         VALUES (?, ?, ?, ?, ?, 'PENDING', ?, ?)" : 
        "INSERT INTO tasks (task_name, deadline, task_assigner, task_duration, task_description, task_status, family_id) 
         VALUES (?, ?, ?, ?, ?,'PENDING', ?)";

    $stmt = $conn->prepare($query);
    if ($fileUploaded) {
        $stmt->bind_param("ssissis", $task_name, $task_deadline, $task_assigner, $task_duration, $task_description, $family_id, $new_file_name);
    } else { 
        $stmt->bind_param("ssissi", $task_name, $task_deadline, $task_assigner, $task_duration, $task_description, $family_id);
    }

    return $stmt->execute();
}


// Function to retrieve tasks by status
function getTasksByStatus($conn, $family_id, $status) {
    $query = "SELECT * FROM tasks WHERE task_status = ? AND family_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $status, $family_id);
    $stmt->execute();
    return $stmt->get_result();
}


// Function to display session messages
function displaySessionMessage() {
    if (isset($_SESSION['message'])) {
        echo $_SESSION['message'];
        unset($_SESSION['message']);
    }
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

// Function to fetch all financial categories for a specific family
function fetchFinancialCategories($conn, $family_id) {
    $query = "SELECT * FROM financial_category WHERE family_id = $family_id";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Function to add a new financial category with family_id
function addFinancialCategory($conn, $name, $parent_id, $category_type, $family_id) {
    // If parent_id is empty, set it to NULL
    if (empty($parent_id)) {
        $parent_id = 'NULL';  // or use 0 if that's your default value
    }

    // Prepare the query to insert the category with family_id
    $query = "INSERT INTO financial_category (category_name, parent_category_id, category, family_id) 
              VALUES ('$name', $parent_id, '$category_type', $family_id)";
    
    // Execute the query
    return mysqli_query($conn, $query);
}

// Function to update an existing financial category with family_id
function updateFinancialCategory($conn, $id, $name, $parent_id, $category_type, $family_id) {
    $query = "UPDATE financial_category SET category_name = '$name', parent_category_id = '$parent_id', category = '$category_type', family_id = $family_id WHERE category_id = $id";
    return mysqli_query($conn, $query);
}

// Function to delete a financial category with family_id
function deleteFinancialCategory($conn, $id, $family_id) {
    $query = "DELETE FROM financial_category WHERE category_id = $id AND family_id = $family_id";
    return mysqli_query($conn, $query);
}



function updateFamilyProfile($conn, $family_id, $family_name, $family_goal, $family_description, $family_profile_pic, $old_family_profile_pic) {
    // Initialize the update fields array
    $fieldsToUpdate = [];

    // Update family_name if provided
    if (!empty($family_name)) {
        $fieldsToUpdate[] = "name = '$family_name'";
    }

    // Update family_goal if provided
    if (!empty($family_goal)) {
        $fieldsToUpdate[] = "financial_goal = '$family_goal'";
    }

    // Update family_description if provided
    if (!empty($family_description)) {
        $fieldsToUpdate[] = "family_description = '$family_description'";
    }

    // Handle profile picture upload
    if (!empty($family_profile_pic['name'])) {
        // Handle file upload logic
        $target_dir = "plugins/images/family_profile/"; // Directory to store profile pictures
        $target_file = $target_dir . basename($family_profile_pic["name"]);
        $uploadOk = 1;

        // Check if the file is an image
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        if (getimagesize($family_profile_pic["tmp_name"]) === false) {
            $uploadOk = 0;
        }

        // Check file size (e.g., 5MB max)
        if ($family_profile_pic["size"] > 5000000) {
            $uploadOk = 0;
        }

        // Allow certain file formats
        if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            $uploadOk = 0;
        }

        // Check if upload is successful
        if ($uploadOk === 1) {
            if (move_uploaded_file($family_profile_pic["tmp_name"], $target_file)) {
                // If the upload is successful, add the file path to the update query
                $fieldsToUpdate[] = "profile_pic = '$target_file'";

                // Optionally delete the old profile picture if new one is uploaded
                if (!empty($old_family_profile_pic) && file_exists($old_family_profile_pic)) {
                    unlink($old_family_profile_pic); // Delete old file
                }
            } else {
                // If file upload failed, return an error
                return "Sorry, there was an error uploading your file.";
            }
        } else {
            return "Sorry, your file was not uploaded. It may be too large or in an unsupported format.";
        }
    }

    // If no fields to update, return an error message
    if (empty($fieldsToUpdate)) {
        return "No changes were made.";
    }

    // Join the fields to update into a single string
    $updateString = implode(", ", $fieldsToUpdate);

    // Construct the SQL query
    $query = "UPDATE family SET $updateString WHERE id = $family_id";

    // Execute the query
    if (mysqli_query($conn, $query)) {
        return "Family profile updated successfully!";
    } else {
        return "Error updating profile: " . mysqli_error($conn);
    }
}


function updateFamilyPosition($conn, $user_id, $family_role) {
    // Sanitize the input to prevent SQL injection
    $family_role = mysqli_real_escape_string($conn, $family_role);

    // Construct the update query
    $query = "UPDATE users SET family_position = '$family_role' WHERE id = $user_id";

    // Execute the query
    if (mysqli_query($conn, $query)) {
        return "Family role updated successfully!";
    } else {
        return "Error updating family role: " . mysqli_error($conn);
    }
}


function renderTransactionForm($transactionType, $family_id, $conn) {
    $categoryType = $transactionType === 'expense' ? 'expense' : 'income';
    $categoriesQuery = "SELECT category_id, category_name FROM financial_category WHERE category_type = 'Child' AND family_id = $family_id AND category = '$categoryType'";
    $categories = $conn->query($categoriesQuery);

    ob_start();
    ?>
    <form action="" method="post" style="width: 560px;">
        <input type="hidden" name="user" value="<?php echo $family_id; ?>">

        <div class="col-8">
            <div class="input-group">
                <span class="input-group-text"><label for="amount">Enter Amount(GH)</label></span>
                <input type="number" name="amount" required>
            </div>
        </div>

        <br>

        <div class="col-8">
            <div class="input-group">
                <span class="input-group-text"><label for="category">Select Category</label></span>
                <select class="form-select" name="category" required>
                    <option value="">-- Select Category --</option>
                    <?php while ($category = $categories->fetch_assoc()) {
                        echo "<option value=\"{$category['category_id']}\">{$category['category_name']}</option>";
                    } ?>
                </select>
            </div>
        </div>

        <br>

        <div class="col-8">
            <div class="input-group">
                <span class="input-group-text"><label for="time">Time</label></span>
                <input type="datetime-local" name="time">
            </div>
        </div>

        <br>

        <div class="col-8">
            <div class="input-group">
                <span class="input-group-text"><label for="note">Note</label></span>
                <textarea name="note"></textarea>
            </div>
        </div>

        <br>

        <input type="submit" class="btn btn-primary" name="add_transaction" value="Add <?php echo ucfirst($transactionType); ?>">
    </form>
    <?php
    return ob_get_clean();
}

function renderResourceForm($resourceType, $family_id, $conn) {
    $categoriesQuery = "SELECT category_id, category_name FROM financial_category WHERE category_type = 'Child' AND family_id = $family_id AND category = '$resourceType'";
    $categories = $conn->query($categoriesQuery);

    ob_start();
    ?>
    <form action="" method="post" style="width: 560px;">
        <input type="hidden" name="user" value="<?php echo $family_id; ?>">

        <div class="col-8">
            <div class="input-group">
                <span class="input-group-text"><label for="name">Enter Item Name</label></span>
                <input type="text" name="name" required>
            </div>
        </div>

        <br>

        <div class="col-8">
            <div class="input-group">
                <span class="input-group-text"><label for="amount">Enter Amount(GH)</label></span>
                <input type="number" name="amount" required>
            </div>
        </div>

        <br>

        <div class="col-8">
            <div class="input-group">
                <span class="input-group-text"><label for="category">Select Category</label></span>
                <select class="form-select" name="category">
                    <option value="">-- Select Category --</option>
                    <?php while ($category = $categories->fetch_assoc()) {
                        echo "<option value=\"{$category['category_id']}\">{$category['category_name']}</option>";
                    } ?>
                </select>
            </div>
        </div>

        <br>

        <div class="col-8">
            <div class="input-group">
                <span class="input-group-text"><label for="time">Time</label></span>
                <input type="datetime-local" name="time">
            </div>
        </div>

        <br>

        <div class="col-8">
            <div class="input-group">
                <span class="input-group-text"><label for="note">Note</label></span>
                <textarea name="note"></textarea>
            </div>
        </div>

        <br>

        <input type="submit" class="btn btn-primary" name="add_resource" value="Add <?php echo ucfirst($resourceType); ?>">
    </form>
    <?php
    return ob_get_clean();
}


function fetchExpenseCategories($conn, $family_id) {
    $query = "SELECT category_id, category_name 
              FROM financial_category 
              WHERE category_type = 'Child' 
                AND family_id = ? 
                AND category = 'expense'";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $family_id);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}
// Function to get dishes for a specific day and time
function getDishes($conn, $family_id, $day, $time) {
    // Query to get dishes for a specific family_id, day, and time
    $query = "SELECT dish_name 
              FROM menu 
              WHERE family_id = ? 
              AND JSON_UNQUOTE(JSON_EXTRACT(dish_details, '$.day')) = ? 
              AND JSON_UNQUOTE(JSON_EXTRACT(dish_details, '$.time')) = ?";
    
    // Prepare and execute the query using prepared statements
    $stmt = $conn->prepare($query);
    $stmt->bind_param('iss', $family_id, $day, $time);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch and return dish names if available
    $dishes = [];
    while ($row = $result->fetch_assoc()) {
        $dishes[] = $row['dish_name'];
    }
    $stmt->close();
    
    return $dishes;
}

// Function to display dishes in a table cell with modals
function displayDishes($conn, $family_id, $day, $time) {
    $dishes = getDishes($conn, $family_id, $day, $time);

    if (!empty($dishes)) {
        foreach ($dishes as $dish) {
            echo '<a href="#" data-bs-toggle="modal" data-bs-target="#update_menu_modal" data-bs-update-info="' 
                . htmlspecialchars($day) . ',' 
                . htmlspecialchars($time) . ',' 
                . htmlspecialchars($dish) . '"> ' 
                . htmlspecialchars($dish) . ' </a>';
        }
    } else {
        echo '<a href="#" data-bs-toggle="modal" data-bs-target="#add_menu_modal" data-bs-add-info="' 
            . htmlspecialchars($day) . ',' 
            . htmlspecialchars($time) . '"> + </a>';
    }
}