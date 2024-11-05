<?php

require_once("plugins/actions/config.php");
session_start();

if(isset($_SESSION['user_id'])){


$user_id = $_SESSION['user_id'];

// for myday 
$query = "SELECT count(*) as number_myday FROM tasks WHERE task_status = 'PENDING' AND DATE(deadline) = CURDATE() AND (task_assigner = $user_id OR task_assignee = $user_id)";

$resultsmyday = $conn->query($query);
if ($resultsmyday -> num_rows > 0 ){
    $row = $resultsmyday -> fetch_assoc();
    $myday_no = $row['number_myday'];

}

// for diary  
$resultsfetchdiaryquery = "SELECT * FROM Diary  WHERE diary_owner = $user_id ";
$resultsfetchdiaryquery = "SELECT * FROM Diary  WHERE diary_owner = $user_id ";


$resultsfetchdiary = $conn->query($resultsfetchdiaryquery);



// for  user 
$query = "SELECT * FROM users WHERE id = $user_id";

$results = $conn->query($query);

if ($results -> num_rows > 0){
    while($row = $results->fetch_assoc()){
        $username = $row['username'];
        $profile_pic = $row['profile_pic'];
        $cash = $row['balance'];
        $goal = $row['financial_goal'];
    }
}



// for budget 
$resultsbudgetquery = "SELECT * FROM budget b LEFT JOIN financial_category fc ON b.category_id = fc.category_id WHERE  b.user_id = $user_id ";


$resultsbudget = $conn->query(query: $resultsbudgetquery);
// for resources 
$resultsresourcesquery = "SELECT * FROM resources r LEFT JOIN financial_category fc ON r.category_id = fc.category_id WHERE  r.user_id = $user_id ";


$resultsresources = $conn->query(query: $resultsresourcesquery);
// for transactions 
$resultstransactionquery = "SELECT * FROM transactions t LEFT JOIN financial_category fc ON t.category_id = fc.category_id WHERE  t.user_id = $user_id ";


$resultstransaction = $conn->query($resultstransactionquery);
// for income 
$resultsincomequery = "SELECT * FROM transactions t LEFT JOIN financial_category fc ON t.category_id = fc.category_id WHERE  t.user_id = $user_id AND fc.category = 'income'";


$resultsincome = $conn->query($resultsincomequery);


// for expense 
$resultsexpensequery = "SELECT * FROM transactions t LEFT JOIN financial_category fc ON t.category_id = fc.category_id WHERE  t.user_id = $user_id AND fc.category = 'expense'";


$resultsexpense = $conn->query($resultsexpensequery);

// for assets 
$resultsassetsquery = "SELECT * FROM  resources r LEFT JOIN financial_category fc ON r.category_id = fc.category_id WHERE  r.user_id = $user_id AND fc.category = 'asset'";


$resultsassets = $conn->query($resultsassetsquery);

// for liabilities 
$resultsliabilitiesquery = "SELECT * FROM resources r LEFT JOIN financial_category fc ON r.category_id = fc.category_id WHERE  r.user_id = $user_id AND fc.category = 'liability'";


$resultsliabilities = $conn->query($resultsliabilitiesquery);



// for  user 
$query = "SELECT * FROM users WHERE id = $user_id";

$results = $conn->query($query);

if ($results -> num_rows > 0){
    while($row = $results->fetch_assoc()){
        $username = $row['username'];
        $profile_pic = $row['profile_pic'];
        $cash = $row['balance'];
        $goal = $row['financial_goal'];
    }
}

// for only total income
$query = "SELECT sum(total_amount) as number_income FROM transactions t LEFT JOIN financial_category fc ON t.category_id = fc.category_id WHERE  t.user_id = $user_id AND fc.category = 'income'";

$resultsincomeno= $conn->query($query);
if ($resultsincomeno -> num_rows > 0 ){
    $row = $resultsincomeno -> fetch_assoc();
    $only_income = $row['number_income'];
}



// for total positive cashflow
$query = "SELECT sum(abs(cashflow)) as positive_cashflow FROM resources r LEFT JOIN financial_category fc ON r.category_id = fc.category_id WHERE  r.user_id = $user_id AND fc.category = 'asset'";

$resultspositive_cashflow = $conn->query($query);
if ($resultspositive_cashflow -> num_rows > 0 ){
    $row = $resultspositive_cashflow -> fetch_assoc();
    $positive_cashflow = $row['positive_cashflow'];
}
// for total negative cashflow
$query = "SELECT sum(abs(cashflow)) as negative_cashflow FROM resources r LEFT JOIN financial_category fc ON r.category_id = fc.category_id WHERE  r.user_id = $user_id AND fc.category = 'liability'";

$resultsnegative_cashflow = $conn->query($query);
if ($resultsnegative_cashflow -> num_rows > 0 ){
    $row = $resultsnegative_cashflow -> fetch_assoc();
    $negative_cashflow = $row['negative_cashflow'];
}

// for only total expense
$query = "SELECT sum(total_amount) as number_expense FROM transactions t LEFT JOIN financial_category fc ON t.category_id = fc.category_id WHERE  t.user_id = $user_id AND fc.category = 'expense'";

$resultsexpenseno= $conn->query($query);
if ($resultsexpenseno -> num_rows > 0 ){
    $row = $resultsexpenseno -> fetch_assoc();
    $only_expense = $row['number_expense'];
}
// for total expense
$query = "SELECT sum(total_amount) as number_expense FROM transactions t LEFT JOIN financial_category fc ON t.category_id = fc.category_id WHERE  t.user_id = $user_id AND (fc.category = 'expense' OR fc.category = 'asset' OR fc.category = 'liability')";

$resultsexpensetotal= $conn->query($query);
if ($resultsexpensetotal -> num_rows > 0 ){
    $row = $resultsexpensetotal -> fetch_assoc();
    $total_expense = $row['number_expense'];
}

$passive_income = ($positive_cashflow - $negative_cashflow);


// for adding transaction
if (isset($_POST['add_transaction'])) {
  // Retrieve and sanitize input values
  $transaction_user = $conn->real_escape_string($_POST['user']);
  $amount = !empty($_POST['amount']) ? $conn->real_escape_string($_POST['amount']) : '0.00';
  $time = !empty($_POST['time']) ? $conn->real_escape_string($_POST['time']) : date('Y/m/d H:i:s');
  $category = $conn->real_escape_string($_POST['category']) ;


  if (isset($_POST['note'])){
    $cat_note = $conn->real_escape_string($_POST['note']);
    // transaction_item,
     // SQL query with parent_category_id handling
    $querytransaction = "INSERT INTO transactions (category_id, user_id, total_amount, transaction_time,note) 
    VALUES ($category, $transaction_user, '$amount', '$time', '$cat_note')";

    $querybudgettransaction = "UPDATE budget SET budget_amount_remaining= (budget_amount-$amount), note = '$cat_note' WHERE category_id=$category";
    $conn->query($querybudgettransaction);

   
  }else{
        // SQL query with parent_category_id handling
    $querytransaction = "INSERT INTO transactions (category_id, user_id, total_amount, transaction_time) 
    VALUES ($category, $transaction_user, '$amount', '$time')";

    $querybudgettransaction = "UPDATE budget SET budget_amount_remaining= (budget_amount-$amount) WHERE category_id=$category";
    $conn->query($querybudgettransaction);
  }

  

  // Execute the query and check for errors
  if ($conn->query($querytransaction)) {
      echo "Category added successfully!";
  } else {
      echo "Error: " . $conn->error;
  }
 // Redirect to the same page to prevent form resubmission on refresh
      header("Location: " . $_SERVER['PHP_SELF']);
      exit();
}



// for adding budget
if (isset($_POST['add_budget'])) {
  // Retrieve and sanitize input values
  $budget_user = $conn->real_escape_string($_POST['user']);
  // $item_name = !empty($_POST['name']) ? $conn->real_escape_string($_POST['name']) : 'unknown';
  $amount = !empty($_POST['amount']) ? $conn->real_escape_string($_POST['amount']) : '0.00';


   // Extract the month name from the 'YYYY-MM' input
   if (!empty($_POST['month'])) {
    $month_input = $_POST['month']; // e.g., '2024-10'
    $month = date('F', strtotime($month_input)); // Converts to 'October'
} else {
    $month = date('F'); // Current month name if no input
}

  $category = $conn->real_escape_string($_POST['category']) ;


  if (isset($_POST['note'])){
    $cat_note = $conn->real_escape_string($_POST['note']);
    // budget_item,
     // SQL query with parent_category_id handling
    $querybudget = "INSERT INTO budget (category_id, user_id, budget_amount, budget_amount_remaining, budget_month,note) 
    VALUES ($category, $budget_user, '$amount', '$amount', '$month', '$cat_note')";
  }else{
        // SQL query with parent_category_id handling
    $querybudget = "INSERT INTO budget (category_id, user_id, budget_amount, budget_amount_remaining, budget_month) 
    VALUES ($category, $budget_user, '$amount', '$amount','$month')";
  }

  // Execute the query and check for errors
  if ($conn->query($querybudget)) {
      echo "Category added successfully!";
  } else {
      echo "Error: " . $conn->error;
  }
 // Redirect to the same page to prevent form resubmission on refresh
      header("Location: " . $_SERVER['PHP_SELF']);
      exit();
}


// for total income sum
$total_income =$passive_income + $only_income;


// for adding resource
if (isset($_POST['add_resource'])) {
  
  // Retrieve and sanitize input values
  $transaction_user = $conn->real_escape_string($_POST['user']);
  $item_name = !empty($_POST['name']) ? $conn->real_escape_string($_POST['name']) : 'unknown';
  $amount = !empty($_POST['amount']) ? $conn->real_escape_string($_POST['amount']) : '0.00';
  $time = !empty($_POST['time']) ? $conn->real_escape_string($_POST['time']) : date('Y/m/d H:i:s');
  $category = $conn->real_escape_string($_POST['category']) ;


  if (isset($_POST['note'])){
    $cat_note = $conn->real_escape_string($_POST['note']);
    // transaction_item,
     // SQL query with parent_category_id handling
    $querytransaction = "INSERT INTO transactions (category_id, user_id, total_amount, transaction_time,note) 
    VALUES ($category, $transaction_user, '$amount', '$time', '$cat_note')";

    $queryresource = "INSERT INTO resources (item_name, item_price, user_id, category_id,item_description) 
    VALUES ('$item_name', '$amount', $transaction_user, $category, '$cat_note')";
    $conn->query($queryresource);
  }else{
        // SQL query with parent_category_id handling
    $querytransaction = "INSERT INTO transactions (category_id, user_id, total_amount, transaction_time) 
    VALUES ($category, $transaction_user, '$amount', '$time')";

    $queryresource = "INSERT INTO resources (item_name, item_price, user_id, category_id) 
    VALUES ('$item_name', '$amount', $transaction_user, $category)";
    $conn->query($queryresource);
  }

  

  // Execute the query and check for errors
  if ($conn->query($querytransaction)) {
      echo "Category added successfully!";
  } else {
      echo "Error: " . $conn->error;
  }
 // Redirect to the same page to prevent form resubmission on refresh
      header("Location: " . $_SERVER['PHP_SELF']);
      exit();
}






$balance = $total_income - $total_expense;

// Use a prepared statement to update the balance securely
$query_profilebalance = "UPDATE users SET balance = ? WHERE id = ?";

$stmt = $conn->prepare($query_profilebalance);
if ($stmt) {
    $stmt->bind_param('di', $balance, $user_id);  // 'd' for decimal, 'i' for integer
    $stmt->execute();
    $stmt->close();
} else {
    echo "Error: " . $conn->error;
}




// for adding website entry
if (isset($_POST['add_website'])&& isset($_POST['url'])) {
  
  // Retrieve and sanitize input values
  $website_user = $user_id;
  $name = !empty($_POST['name']) ? $conn->real_escape_string($_POST['name']) : '';
  $url = !empty($_POST['url']) ? $conn->real_escape_string($_POST['url']) : '';
  $website_description = !empty($_POST['description']) ? $conn->real_escape_string($_POST['description']) : '';
  $website_category = !empty($_POST['category']) ? $conn->real_escape_string($_POST['category']): 'Leisure' ;


    $querywebsiteentry = "INSERT INTO favourite_sites (website_name, website_url, website_description, website_category, website_user) 
    VALUES ('$name', '$url', '$website_description', '$website_category', $website_user)";
    $conn->query($querywebsiteentry);


    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
  }



if (isset($_POST['update_resources'])) {
  $resource_id = $_POST['resource'];

  // Fetch the current values from the database
  $query = "SELECT item_name, item_description, personal_notes, item_price, cashflow 
            FROM resources WHERE resource_id = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param('i', $resource_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $resource = $result->fetch_assoc();
  $stmt->close();

  // Assign values or keep the original ones if input is empty
  $name = !empty($_POST['name']) ? $conn->real_escape_string($_POST['name']) : $resource['item_name'];
  $description = !empty($_POST['description']) ? $conn->real_escape_string($_POST['description']) : $resource['item_description'];
  $notes = !empty($_POST['notes']) ? $conn->real_escape_string($_POST['notes']) : $resource['personal_notes'];
  $price = !empty($_POST['price']) ? $conn->real_escape_string($_POST['price']) : $resource['item_price'];
  $cashflow = !empty($_POST['cashflow']) ? $conn->real_escape_string($_POST['cashflow']) : $resource['cashflow'];

  // SQL query to update the resource
  $updateQuery = "UPDATE resources 
                  SET item_name = ?, item_description = ?, personal_notes = ?, 
                      item_price = ?, cashflow = ? 
                  WHERE resource_id = ?";

  $stmt = $conn->prepare($updateQuery);
  if ($stmt) {
      $stmt->bind_param('ssssdi', $name, $description, $notes, $price, $cashflow, $resource_id);

      if ($stmt->execute()) {
          echo "Resource updated successfully!";
      } else {
          echo "Error: " . $stmt->error;
      }
      $stmt->close();
  } else {
      echo "Error: " . $conn->error;
  }

  header("Location: " . $_SERVER['PHP_SELF']);
      exit();
}



// for only total income
$query = "SELECT sum(total_amount) as number_income FROM transactions t LEFT JOIN financial_category fc ON t.category_id = fc.category_id WHERE  t.user_id = $user_id AND fc.category = 'income'";

$resultsincomeno= $conn->query($query);
if ($resultsincomeno -> num_rows > 0 ){
    $row = $resultsincomeno -> fetch_assoc();
    $only_income = $row['number_income'];
}



// for total positive cashflow
$query = "SELECT sum(abs(cashflow)) as positive_cashflow FROM resources r LEFT JOIN financial_category fc ON r.category_id = fc.category_id WHERE  r.user_id = $user_id AND fc.category = 'asset'";

$resultspositive_cashflow = $conn->query($query);
if ($resultspositive_cashflow -> num_rows > 0 ){
    $row = $resultspositive_cashflow -> fetch_assoc();
    $positive_cashflow = $row['positive_cashflow'];
}
// for total negative cashflow
$query = "SELECT sum(abs(cashflow)) as negative_cashflow FROM resources r LEFT JOIN financial_category fc ON r.category_id = fc.category_id WHERE  r.user_id = $user_id AND fc.category = 'liability'";

$resultsnegative_cashflow = $conn->query($query);
if ($resultsnegative_cashflow -> num_rows > 0 ){
    $row = $resultsnegative_cashflow -> fetch_assoc();
    $negative_cashflow = $row['negative_cashflow'];
}

// for only total expense
$query = "SELECT sum(total_amount) as number_expense FROM transactions t LEFT JOIN financial_category fc ON t.category_id = fc.category_id WHERE  t.user_id = $user_id AND fc.category = 'expense'";

$resultsexpenseno= $conn->query($query);
if ($resultsexpenseno -> num_rows > 0 ){
    $row = $resultsexpenseno -> fetch_assoc();
    $only_expense = $row['number_expense'];
}
// for total expense
$query = "SELECT sum(total_amount) as number_expense FROM transactions t LEFT JOIN financial_category fc ON t.category_id = fc.category_id WHERE  t.user_id = $user_id AND (fc.category = 'expense' OR fc.category = 'asset' OR fc.category = 'liability')";

$resultsexpensetotal= $conn->query($query);
if ($resultsexpensetotal -> num_rows > 0 ){
    $row = $resultsexpensetotal -> fetch_assoc();
    $total_expense = $row['number_expense'];
}

$passive_income = ($positive_cashflow - $negative_cashflow);



if (isset($_POST['add_dish'])) {
    // Retrieve and sanitize input values
    $user = $user_id;
    $day = !empty($_POST['day']) ? $conn->real_escape_string($_POST['day']) : null;
    $time = !empty($_POST['time']) ? $conn->real_escape_string($_POST['time']) : null;
    $dish_name = !empty($_POST['dish_name']) ? $conn->real_escape_string($_POST['dish_name']) : '';

    // Check if day and time were provided
    if (!$day || !$time) {
        echo "Error: Please provide both day and time.";
        exit();
    }

    // JSON object for dish details
    $dish_details = json_encode([
        'dish_category' => '',
        'preparation_process' => '',
        'dietary_restrictions' => '',
        'ingredients_used' => [
            'name' => '',
            'quantity' => ''
        ],
        'day' => $day,
        'time' => $time
    ], JSON_UNESCAPED_SLASHES);

    // Insert query
    $querydishentry = "INSERT INTO menu (dish_details, dish_name, user_id) 
                       VALUES ('$dish_details', '$dish_name', $user)";

    if ($conn->query($querydishentry) === TRUE) {
        // Redirect to refresh the page after insertion
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error: " . $querydishentry . "<br>" . $conn->error;
    }
}




// for adding ingredient 
if (isset($_POST['add_ingredient'])) {
  
  // Retrieve and sanitize input values
  $ingredient_user = $user_id;
  $name = !empty($_POST['name']) ? $conn->real_escape_string($_POST['name']) : '';
  $price = !empty($_POST['price']) ? $conn->real_escape_string($_POST['price']) : '0.00';
  $quantity = !empty($_POST['quantity']) ? $conn->real_escape_string($_POST['quantity']) : 0;


    $queryingrediententry = "INSERT INTO inventory (ingredient_name, price_per_unit, total_quantity, user_id) 
    VALUES ('$name', '$price', $quantity, $ingredient_user)";
    $conn->query($queryingrediententry);


    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
  }

// for total income sum
$total_income =$passive_income + $only_income;


// for adding diary entry
if (isset($_POST['add_entry'])&& isset($_POST['entry'])) {
  
  // Retrieve and sanitize input values
  $diary_owner = $user_id;
  $entry = !empty($_POST['entry']) ? $conn->real_escape_string($_POST['entry']) : '';
  $date = !empty($_POST['date']) ? $conn->real_escape_string($_POST['date']) : date('d/m/y');
  $entry_title = !empty($_POST['title']) ? $conn->real_escape_string($_POST['title']): '' ;


    $querydiaryentry = "INSERT INTO Diary (diary_title, diary_entry, entry_date, diary_owner) 
    VALUES ('$entry_title', '$entry', '$date', $diary_owner)";
    $conn->query($querydiaryentry);


    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
  }


// for adding website entry
if (isset($_POST['add_website'])&& isset($_POST['url'])) {
  
  // Retrieve and sanitize input values
  $website_user = $user_id;
  $name = !empty($_POST['name']) ? $conn->real_escape_string($_POST['name']) : '';
  $url = !empty($_POST['url']) ? $conn->real_escape_string($_POST['url']) : '';
  $website_description = !empty($_POST['description']) ? $conn->real_escape_string($_POST['description']) : '';
  $website_category = !empty($_POST['category']) ? $conn->real_escape_string($_POST['category']): 'Leisure' ;


    $querywebsiteentry = "INSERT INTO favourite_sites (website_name, website_url, website_description, website_category, website_user) 
    VALUES ('$name', '$url', '$website_category', '$website_description', $website_user)";
    $conn->query($querywebsiteentry);


    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
  }



$balance = $total_income - $total_expense;

// Use a prepared statement to update the balance securely
$query_profilebalance = "UPDATE users SET balance = ? WHERE id = ?";

$stmt = $conn->prepare($query_profilebalance);
if ($stmt) {
    $stmt->bind_param('di', $balance, $user_id);  // 'd' for decimal, 'i' for integer
    $stmt->execute();
    $stmt->close();
} else {
    echo "Error: " . $conn->error;
}




$user_id = $_SESSION['user_id'];

if (isset($_POST['submit'])) {
// Assign form inputs to variables or default to NULL
$task_name = !empty($_POST['name']) ? $_POST['name'] : null;
$task_description = !empty($_POST['description']) ? $_POST['description'] : null;
$task_deadline = !empty($_POST['deadline']) ? $_POST['deadline'] : null;
$task_duration = !empty($_POST['duration']) ? $_POST['duration'] : null;
$task_assigner = !empty($_POST['assigner']) ? $_POST['assigner'] : null;

// Handle file upload for task
if (isset($_FILES['file']['name']) && !empty($_FILES['file']['name'])) {
    $file_name = $_FILES['file']['name'];
    $tmp_name = $_FILES['file']['tmp_name'];
    $error = $_FILES['file']['error'];

    if ($error === 0) {
        $file_ex = pathinfo($file_name, PATHINFO_EXTENSION);
        $file_ex_to_lc = strtolower($file_ex);

        // Allowed file extensions (adjust as needed)
        $allowed_exs = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        if (in_array($file_ex_to_lc, $allowed_exs)) {
            $new_file_name = uniqid('', true) . '.' . $file_ex_to_lc.'-'.$user_id;
            $file_upload_path = 'plugins/tasks/file' . $new_file_name;

            // Move the uploaded file to the specified directory
            move_uploaded_file($tmp_name, $file_upload_path);

            // Prepare the SQL query with placeholders
            $query = "INSERT INTO tasks (task_name, deadline, task_duration, task_description, task_status, task_assigner, task_resource_filename) 
                      VALUES (?, ?, ?, ?, 'PENDING', ?, ?)";

            // Prepare the statement using the MySQLi connection
            $stmt = $conn->prepare($query);

            // Bind parameters
            $stmt->bind_param("ssssis", $task_name, $task_deadline, $task_duration, $task_description, $task_assigner, $new_file_name);

            // Execute the statement and check if it was successful
            if ($stmt->execute()) {
                echo "New task created successfully!";
            } else {
                echo "Error: " . $stmt->error;
            }

            // Redirect to the same page to prevent form resubmission on refresh
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $em = "You can't upload files of this type";
            header("Location: " . $_SERVER['PHP_SELF'] . "?error=$em");
            exit();
        }
    } else {
        $em = "Unknown error occurred!";
        header("Location: " . $_SERVER['PHP_SELF'] . "?error=$em");
        exit();
    }
} else {
    // Insert the task without a file if no file is uploaded
    $query = "INSERT INTO tasks (task_name, deadline, task_duration, task_description, task_status, task_assigner) 
              VALUES (?, ?, ?, ?, 'PENDING', ?)";

    // Prepare the statement using the MySQLi connection
    $stmt = $conn->prepare($query);

    // Bind parameters (without file)
    $stmt->bind_param("ssssi", $task_name, $task_deadline, $task_duration, $task_description, $task_assigner);

    // Execute the statement and check if it was successful
    if ($stmt->execute()) {
        echo "New task created successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Redirect to the same page to prevent form resubmission on refresh
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
}





// for  tasks  No
$query = "SELECT count(*) as number_tasks FROM tasks WHERE task_status = 'PENDING' and (task_assigner = $user_id OR task_assignee = $user_id)";

$resultstasks_no = $conn->query($query);
if ($resultstasks_no -> num_rows > 0 ){
    $row = $resultstasks_no -> fetch_assoc();
    $tasks_no = $row['number_tasks'];

}


// for  tasks 
$query = "SELECT * FROM tasks WHERE task_status = 'PENDING' and (task_assigner = $user_id OR task_assignee = $user_id)";

$resultstasks = $conn->query($query);


if(isset($_SESSION['message'])){
    echo $_SESSION['message'];
    unset($_SESSION['message']);
}

// for completed  tasks 
$query = "SELECT * FROM tasks WHERE task_status = 'COMPLETED' and (task_assigner = $user_id OR task_assignee = $user_id) ";

$resultstasks_completed = $conn->query($query);




// for  user 
$query = "SELECT * FROM users WHERE id = $user_id";

$results = $conn->query($query);

if ($results -> num_rows > 0){
    while($row = $results->fetch_assoc()){
        $username = $row['username'];
        $profile_pic = $row['profile_pic'];
    }
}







if(isset($_SESSION['message'])){
    echo $_SESSION['message'];
    unset($_SESSION['message']);
}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Manager</title>
    <link rel="canonical" href="https://www.wrappixel.com/templates/ample-admin-lite/" />
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="plugins/images/jop.png">
    <!-- Custom CSS -->
   <link href="plugins/css/style.min.css" rel="stylesheet">
   <link href="plugins/css/bootstrap.min.css" rel="stylesheet">
   <link href="plugins/css/docs.css" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
</head>

<body class="bg-light">
    <!-- ============================================================== -->
    <!-- Preloader - style you can find in spinners.css -->
    <!-- ============================================================== -->
    <div class="preloader">
        <div class="lds-ripple">
            <div class="lds-pos"></div>
            <div class="lds-pos"></div>
        </div>
    </div>
    <!-- ============================================================== -->
    <!-- Main wrapper - style you can find in pages.scss -->
    <!-- ============================================================== -->
   
        <!-- ============================================================== -->
        <!-- Topbar header - style you can find in pages.scss -->
        <!-- ============================================================== -->
        <header class="topbar" data-navbarbg="skin5">
            <nav class="navbar top-navbar navbar-expand-md navbar-dark">
                <div class="navbar-header" data-logobg="skin6">
                  
                    <!-- ============================================================== -->
                    <!-- toggle and nav items -->
                    <!-- ============================================================== -->
                    
                </div>
                <!-- ============================================================== -->
                <!-- End Logo -->
                <!-- ============================================================== -->
                <div class="navbar-collapse collapse bg-warning" id="navbarSupportedContent" >
                   
                    <!-- ============================================================== -->
                    <!-- Right side toggle and nav items -->
                    <!-- ============================================================== -->
                    <ul class="navbar-nav ms-auto d-flex align-items-center">

                        <!-- ============================================================== -->
                        <!-- Search -->
                        <!-- ============================================================== -->
                        <li class=" in">
                            <form role="search" class="app-search d-none d-md-block me-3">
                                <input type="text" placeholder="Search..." class="form-control mt-0">
                                <a href="" class="active">
                                    <i class="fa fa-search"></i>
                                </a>
                            </form>
                        </li>
                        <!-- ============================================================== -->
                        <!-- User profile and search -->
                        <!-- ============================================================== -->
                        <li>

                            <div class="dropdown">
                            <a class="btn  dropdown-toggle profile-pic" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="plugins/images/users/<?php echo $profile_pic?>" alt="user-img" width="45" height="45"
                            class="img-circle"><span class="text-white font-medium"><?php echo $username ?></span>
                            </a>

                            <ul class="dropdown-menu dropdown-menu-end">
                              <?php if(isset($user_id)) {?>
                              <div id="left" class="d-flex justify-content-center">
                                    <a class="btn profile-pic-enlarged" href="#">
                                  <img src="plugins/images/users/<?php echo $profile_pic?>" alt="user-img" width="60" height="60"
                                  class="img-circle"><span class="text-bg-white font-medium  d-flex justify-content-center"><?php echo $username ?>
                                   (<?php echo $cash ?> GH₵)</span></a>
                              </div>
                              <hr>
                              <div id="right" class="d-flex justify-content-center">
                                  <li><a class="dropdown-item" href="profile.php">Profile</a></li>|
                                  <li><a data-bs-toggle="modal" data-bs-target="#logout-modal" role="button" class="dropdown-item"  >Logout</a></li>
                                  <?php } else{?>
                                  <li><a data-bs-toggle="modal" data-bs-target="#signup-modal" role="button" class="dropdown-item" href="#">Signup</a></li>
                                  <li><a data-bs-toggle="modal" data-bs-target="#login-modal" role="button" class="dropdown-item" href="#">Login</a></li>
                                  
                              </div>
                            
                           <?php } ?>
                            </ul>
                            </div>
                            </li>
                        <!-- ============================================================== -->
                        <!-- User profile and search -->
                        <!-- ============================================================== -->
                    </ul>
                </div>
            </nav>
        </header>

        
        <div class="page-breadcrumb bg-secondary">
          
        <?php if(isset($_SESSION['user_id'])){
?>
                    <div class="row align-items-center">
                        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                            <!-- Button to toggle offcanvas -->
                            <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample" aria-controls="offcanvasExample">
                            Financials
                            </button>
                        </div>

                        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
                              <div class="d-md-flex">
                                  <ol class="breadcrumb ms-auto">
                                      <li><span><a href="index.php" ><img height="50" width="50" src="plugins/images/exit.jpg" alt="Go to home page"></a></span></li>
                                  </ol>
                            </div>  
                          </div>
                        
                    </div>
                    <?php }else{ ?>
                    <div class="row align-items-center">
                        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                            <!-- Button to toggle offcanvas -->
                            <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample" aria-controls="offcanvasExample">
                              Stocks <span ><img height="20" width="20" src="plugins/images/locked.png" ></span>
                            </button>
                        </div>

                        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
                              <div class="d-md-flex">
                                  <ol class="breadcrumb ms-end">
                                      <li><span><a href="index.php" ><img height="50" width="50" src="plugins/images/exit.jpg" alt="Go to home page"></a></span></li>
                                  </ol>
                                  </div>
                          </div>
                        
                    </div>
                    <?php }?>
                    <!-- /.col-lg-12 -->
                </div>
                
        <?php if(isset($user_id)) {?>

            


                <ul class="nav nav-pills nav-fill mb-3" id="pills-tab" role="tablist">
                    <!-- or i should make it monday to friday then i write menu on top so morning afternoon evening maybe it can also calculate which fod yo can do with them and also for stocks we have perishable and non perishable expirey date etc you can update menu when you click on it -->
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="pills-chat-tab" data-bs-toggle="pill" data-bs-target="#pills-chat" type="button" role="tab" aria-controls="pills-chat" aria-selected="true">Chats</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-menu-tab" data-bs-toggle="pill" data-bs-target="#pills-menu" type="button" role="tab" aria-controls="pills-menu" aria-selected="true">Menu</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-stocks-tab" data-bs-toggle="pill" data-bs-target="#pills-stocks" type="button" role="tab" aria-controls="pills-stocks" aria-selected="false">Stocks</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-tasks-tab" data-bs-toggle="pill" data-bs-target="#pills-tasks" type="button" role="tab" aria-controls="pills-tasks" aria-selected="false">Tasks</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-finances-tab" data-bs-toggle="pill" data-bs-target="#pills-finances" type="button" role="tab" aria-controls="pills-finances" aria-selected="false">Finances</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-websites-tab" data-bs-toggle="pill" data-bs-target="#pills-websites" type="button" role="tab" aria-controls="pills-websites" aria-selected="false">Favourite websites</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-saturday-tab" data-bs-toggle="pill" data-bs-target="#pills-saturday" type="button" role="tab" aria-controls="pills-saturday" aria-selected="false">Saturday</button>
                </li>
                </ul>

        
<div class="tab-content" id="pills-tabContent">
  <div class="tab-pane fade show active" id="pills-chat" role="tabpanel" aria-labelledby="pills-chat-tab" tabindex="0">
    
                      <h3> Chats </h3>
            </div>

            <div class="tab-pane fade" id="pills-menu" role="tabpanel" aria-labelledby="pills-menu-tab" tabindex="0">

            <h3 class="text-primary text-center" >Menu</h3>


                    
          <table class="table table-bordered border-primary">
          <thead>
              <tr>
              <th scope="col">Days</th>
              <th scope="col">Morning</th>
              <th scope="col">Afternoon</th>
              <th scope="col">Evening</th>
              </tr>
          </thead>


          <?php
          // Days of the week and meal times
          $days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
          $times = ["morning", "afternoon", "evening"];
          ?>

          <tbody>
          <?php foreach ($days as $day): ?>
          <tr>
          <th scope="row"><?= htmlspecialchars($day) ?></th>
          <?php foreach ($times as $time): ?>
              <td class="text-center">
                  <?php
                  $query = "
                      SELECT dish_name FROM menu 
                      WHERE user_id = $user_id 
                      AND JSON_UNQUOTE(JSON_EXTRACT(dish_details, '$.day')) = '$day' 
                      AND JSON_UNQUOTE(JSON_EXTRACT(dish_details, '$.time')) = '$time'";

                  $result = $conn->query($query);

                  if ($result->num_rows > 0) {
                      while ($row = $result->fetch_assoc()) {
                          echo '<a href="#"  data-bs-toggle="modal"  data-bs-target="#update_menu_modal" data-bs-update-info="' . htmlspecialchars($day) . ',' . htmlspecialchars($time) . ',' . htmlspecialchars($row['dish_name']) . '"> ' 
                              . htmlspecialchars($row['dish_name']) . 
                          ' </a>';
                      }
                  } else {
                      echo '<a href="#" 
                          data-bs-toggle="modal" 
                          data-bs-target="#add_menu_modal" 
                          data-bs-add-info="' . htmlspecialchars($day) . ',' . htmlspecialchars($time) . '"> + </a>';
                  }
                  ?>
              </td>
          <?php endforeach; ?>
          </tr>
          <?php endforeach; ?>
          </tbody>


          </table>


          <!-- Update menu modal -->
          <div class="modal fade" id="update_menu_modal" tabindex="-1" aria-labelledby="updateMenuLabel" aria-hidden="true">
              <div class="modal-dialog">
                  <div class="modal-content">
                      <div class="modal-header">
                          <h5 class="modal-title" id="updateMenuLabel">Update Dish</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                          <form action="" method="POST">
                              <input type="hidden" name="day" id="update-modal-day">
                              <input type="hidden" name="time" id="update-modal-time">
                              <div class="mb-3">
                                  <label for="dish_name" class="form-label">Dish Name</label>
                                  <input type="text" class="form-control" name="dish_name" id="update-dish-name" required>
                              </div>
                              <div class="mb-3">
                                  <label for="dish_name" class="form-label">Dish Name</label>
                                  <input type="text" class="form-control" name="dish_name" id="update-dish-name" required>
                              </div>
                              <div class="mb-3">
                                  <label for="dish_name" class="form-label">Dish Name</label>
                                  <input type="text" class="form-control" name="dish_name" id="update-dish-name" required>
                              </div>
                              <div class="mb-3">
                                  <label for="dish_name" class="form-label">Dish Name</label>
                                  <input type="text" class="form-control" name="dish_name" id="update-dish-name" required>
                              </div>
                              <div class="mb-3">
                                  <label for="dish_name" class="form-label">Dish Name</label>
                                  <input type="text" class="form-control" name="dish_name" id="update-dish-name" required>
                              </div>
                              <button type="submit" class="btn btn-success w-100">Update Dish</button>
                          </form>
                      </div>
                  </div>
              </div>
          </div>


                          
          <!-- Add Menu Modal -->
          <div class="modal fade" id="add_menu_modal" tabindex="-1" aria-labelledby="addMenuLabel" aria-hidden="true">
          <div class="modal-dialog">
          <div class="modal-content">
          <div class="modal-header">
              <h5 class="modal-title" id="addMenuLabel">Add Menu</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <form action="" method="POST">
                  <input type="hidden" name="day" id="add-modal-day"> <!-- Corrected ID -->
                  <input type="hidden" name="time" id="add-modal-time"> <!-- Corrected ID -->
                  <input type="hidden" name="user" value="<?php echo $user_id; ?>">

                  <div class="mb-3">
                      <label for="dish_name" class="form-label">Dish Name</label>
                      <input type="text" class="form-control" name="dish_name" id="dish_name" required>
                  </div>

                  <input type="submit" class="btn btn-primary w-100" name="add_dish" value="Add Dish">
              </form>
          </div>
          </div>
          </div>
          </div>




          <script>
          const addMenuModal = document.getElementById('add_menu_modal');
          const updateMenuModal = document.getElementById('update_menu_modal');

          // Add Menu Modal: Populate hidden fields with day and time
          addMenuModal.addEventListener('show.bs.modal', function (event) {
          const button = event.relatedTarget;
          const info = button.getAttribute('data-bs-add-info').split(',');
          document.getElementById('add-modal-day').value = info[0];
          document.getElementById('add-modal-time').value = info[1];
          });
          console.log(info);

          // Update Menu Modal: Populate hidden fields and input with existing data
          updateMenuModal.addEventListener('show.bs.modal', function (event) {
          const button = event.relatedTarget;
          const info = button.getAttribute('data-bs-update-info').split(',');
          document.getElementById('update-modal-day').value = info[0];
          document.getElementById('update-modal-time').value = info[1];
          document.getElementById('update-dish-name').value = info[2];
          });



          </script>


  </div>

  <div class="tab-pane fade" id="pills-stocks" role="tabpanel" aria-labelledby="pills-stocks-tab" tabindex="0">
              <?php 
            $resultsinventoryquery = "SELECT * FROM inventory";
            $resultsinventory = $conn->query($resultsinventoryquery);

            if ($resultsinventory->num_rows > 0) {
                while ($row = $resultsinventory->fetch_assoc()) { ?>
                    
                    <h2 style='color: blue; margin-top: 15px;'>Inventory Items</h2>
                      
                    <ul style='list-style-type: none; padding-left: 20px; margin-top: 10px;'>
                        <li>
                            <a href="#"  data-bs-toggle="modal"  data-bs-target="#update_stock_modal"  data-bs-price="<?php echo htmlspecialchars($row['price_per_unit']); ?>" 
                            data-bs-quantity="<?php echo htmlspecialchars($row['total_quantity']); ?>"  data-bs-name="<?php echo htmlspecialchars($row['ingredient_name']); ?>" 
                              style='color: black; text-decoration: none;'>
                                <strong><?php echo htmlspecialchars($row['ingredient_name']); ?></strong><br>
                                <small>Quantity: <?php echo htmlspecialchars($row['total_quantity']); ?> ,</small>
                                <small>Price: <?php echo htmlspecialchars($row['price_per_unit']); ?></small>
                            </a>
                        </li>
                    </ul>



                                    

                <?php 
                } 
            } else {
                echo "<h2>No items found in the inventory.</h2>";
            }
            ?>


                
            <!-- Single Update Stock Modal (outside the loop) -->
            <div class="modal fade" id="update_stock_modal" tabindex="-1" aria-labelledby="updateStockLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="updateStockLabel">Update Stock</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" ></button>
                        </div>
                        <div class="modal-body">
                            <form action="" method="POST">
                                <div class="mb-3">
                                    <label for="stock_name" class="form-label">Stock Name</label>
                                    <input type="text" class="form-control" name="stock_name" id="update-stock-name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="stock_quantity" class="form-label">Quantity</label>
                                    <input type="number" class="form-control" name="stock_quantity" id="update-stock-quantity" required>
                                </div>
                                <button type="submit" class="btn btn-success w-100">Update Stock</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                // Get the Update Stock Modal
                const updateStockModal = document.getElementById('update_stock_modal');

                // When the modal is shown, populate it with data from the clicked link
                updateStockModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget; // Element that triggered the modal
                    const stockName = button.getAttribute('data-bs-name'); // Extract stock name
                    const stockQuantity = button.getAttribute('data-bs-quantity'); // Extract stock quantity

                    // Populate the modal's input fields
                    document.getElementById('update-stock-name').value = stockName;
                    document.getElementById('update-stock-quantity').value = stockQuantity;
                });
            </script>

            <fieldset class="border border-primary rounded-3 p-4 mx-auto my-5 w-100 w-md-75">
                <legend class="fw-bold text-primary">Add ingredients</legend>

                <form action="" method="post">
                <input type="hidden" name="user" id="user" value="<?php echo $user_id; ?>">

                <div class="col-12 mb-3">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-0">
                            <label for="name">
                                <img src="plugins/images/ingredient.jpg" alt="ingredient Icon" width="30" height="30">
                            </label>
                        </span>
                        <input type="text" name="name" id="name" class="form-control" 
                              placeholder="Enter ingredient Name" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-6">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-0">
                                <label for="price">
                                    <img src="plugins/images/price.jpg" alt="price Icon" width="30" height="30">
                                </label>
                            </span>
                            <input type="text" name="price" id="price" class="form-control" 
                                  placeholder="Enter price per unit">
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-0">
                                <label for="quantity">
                                    <img src="plugins/images/quantity.jpg" alt="quantity Icon" width="30" height="30">
                                </label>
                            </span>
                            <input type="number" name="quantity" id="quantity" class="form-control" 
                                  placeholder="Enter quantity">
                        </div>
                    </div>
                </div>
                
                <div class="col-12">
                    <input type="submit" class="btn btn-primary w-100" name="add_ingredient" value="Add Ingredient">
                </div>
            </form>

            </fieldset>
                                    
                 
  </div>
  <div class="tab-pane fade" id="pills-tasks" role="tabpanel" aria-labelledby="pills-tasks-tab" tabindex="0">
        <div class="accordion" id="accordionExample">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                          <h2 class="text-center">Pending Tasks</h2>
                          </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#accordionExample">
                          <div class="accordion-body">
                            <?php while ($row = $resultstasks->fetch_assoc()) {?>

                        <div id="tasks"  class="bg-dark d-flex align-items-center justify-content-between" >
                            <span >
                                <form action="plugins/actions/task_completion.php" method="post">
                                    <input type="hidden" name="task_id" value="<?php echo $row['id'];?>"> <!-- Example staff ID -->
                                    <input 
                                    style="display:none;"
                                        type="checkbox" 
                                        id="statusCheckboxunchecked - <?php echo $row['id'];?>"
                                        name="status" 
                                        value="COMPLETED" 
                                        onclick="this.form.submit();" 
                                    >
                                    <label for="statusCheckboxunchecked - <?php echo $row['id'];?>" class="custom-checkbox">
                                        <img src="plugins/images/unchecked.png" style="width: 30px; height: 30px; cursor: pointer;" alt="Unchecked" class="checkbox-image" />
                                        </label>
                                </form>
                            </span>

                            <div id="taskdescription" data-bs-toggle="modal" data-bs-target="#update-tasks-modal-<?php echo $row['id'];?>" role="button" class="mt-2" style="display:inline-block;">
                                <h4 class="text-light"><?php echo $row['task_name']?></h4>
                                <h5 class="text-secondary"><?php echo $row['task_description']?></h5>
                                <p class="text-secondary"><?php echo $row['deadline']. " - ". ($row['task_duration']/60)?>min</p>

                            </div>
                            <?php if($row['task_resource_filename']){ ?>
                              <span >
                                <a href="plugins/tasks/files/<?php echo $row['task_resource_filename'];?>" download="<?php echo $row['task_resource_filename'];?>">
                                  <img src="plugins/images/file.png" alt="download" style="width: 20px; height: 20px;">
                              </span>
                            
                            <?php } ?>

                            <span class="me-0">
                            <form action="plugins/actions/task_importance.php" method="post">
                                <input type="hidden" name="task_id" value="<?php echo $row['id']; ?>"> 

                                
                        <input type="hidden" name="importance" value="<?php if($row['task_importance'] == 'Important'){
                            echo 'Ordinary';} else{
                            echo  'Important';
                            }
                        ?>">

                          <img 
                              src="plugins/images/<?php echo ($row['task_importance'] == 'Important') ? 'whitestar.png' : 'starred.png'; ?>" 
                              alt="Mark as Important" 
                              style="width: 30px; height: 30px; cursor: pointer;" 
                              onclick="this.closest('form').submit();"
                          >
                            </form>

                            
                            </span>

                        </div>
                        <br>

                            <!-- this is the update tasks modal -->
                            <div class="modal fade" id="update-tasks-modal-<?php echo $row['id'];?>" tabindex="-1" aria-labelledby="update-tasks-modal-title-<?php echo $row['id'];?>" aria-hidden="true">
                              <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <h5 class="modal-title" id="update-tasks-modal-title-<?php echo $row['id'];?>">Update Tasks</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                  </div>

                                  <div class="modal-body">
                                    <form action="plugins/actions/update_tasks.php" method="post" enctype="multipart/form-data">

                                      <input type="hidden" name="task" value="<?php echo $row['id']; ?>">

                                      <h2 class="text-primary mb-3">Task - <?php echo $row['task_name']; ?></h2>

                                      <div class="row g-3">
                                        <div class="col-md-8">
                                          <div class="input-group">
                                            <span class="input-group-text">Description</span>
                                            <textarea name="description" class="form-control"><?php echo $row['task_description']; ?></textarea>
                                          </div>
                                        </div>

                                        <div class="col-md-4">
                                          <div class="input-group">
                                            <span class="input-group-text">Assignee</span>
                                            <select name="assignee" id="assignee" class="form-select" aria-label="assignee ">
                                                        <option value="" disabled>Assignee</option>
                                                        <?php
                                                            require("plugins/actions/config.php");
                                                            $assigneesql = "select * from users";
                                                            $results = $conn ->query($assigneesql);
                                                            if ($results -> num_rows > 0){
                                                                while($row = $results ->fetch_assoc())
                                                                    echo " <option value=' " . $row['id']. "'> ".$row['name']."</option>";
                                                            }else{
                                                                echo " <option value=' ' disabled selected >  No users Created </option>";
                                                          
                                                            }
                                        
                                                        ?>
                                                    </select>
                                          </div>
                                        </div>

                                        <div class="col-md-4">
                                          <div class="input-group">
                                            <span class="input-group-text">Deadline</span>
                                            <input type="datetime-local" name="deadline" class="form-control" >
                                          </div>
                                        </div>
                                          <script>
                                        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
                                        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
                                          </script>

                                        <div class="col-md-6">
                                          <div class="input-group">
                                            <span class="input-group-text" data-bs-toggle="tooltip" data-bs-placement="top" title="remind me" id="reminderLabel" style="cursor: pointer;">
                                              <label for="reminder" ><img src="plugins/images/remind.png" alt="reminder" style="width: 30px; height: 30px;"></label>
                                            </span>
                                            <input type="datetime-local" name="reminder" id="reminder" class="form-control" >
                                          </div>
                                        </div>

                                        <div class="col-md-6">
                                          <div class="input-group">
                                            <span class="input-group-text" data-bs-toggle="tooltip" data-bs-placement="top" title="repeat" id="repeatLabel" style="cursor: pointer;">
                                              <label for="repeat" ><img src="plugins/images/repeat.png" alt="repeat" style="width: 20px; height: 20px;"></label>
                                            </span>
                                            <input type="text" name="repeat" id="repeat" class="form-control" placeholder="(num of days),(DAY,WEEK,YEAR or MONTH)">
                                          </div>
                                        </div>

                                    

                                        <div class="col-md-8">
                                          <div class="input-group">
                                            <span class="input-group-text">Add file</span>
                                            <input type="file" name="file" class="form-control">
                                          </div>
                                          <small class="form-text text-muted">
                                            Supported file types: PDF, DOC, DOCX, JPG, JPEG, PNG.
                                          </small>
                                        </div>
                                      </div>

        
                                  </div>

                                  <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary" name="update">Update</button>
                                    </form>
                                  </div>
                                </div>
                              </div>
                            </div>


                        <?php }?>


                        <span><a data-bs-toggle="modal" data-bs-target="#add-tasks-modal" role="button" ><img height="20" width="20" src="plugins/images/add.png" alt="add tasks"></a></span>   

                        </div>
                        </div>                                      
                    </div>

                    <div class="accordion-item">
                      <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                        <h2 class="text-center">Completed Tasks</h2>
                        </button>
                      </h2>
                      <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                        <?php while ($row = $resultstasks_completed->fetch_assoc()) {?>

                          <div id="tasks" class="bg-dark d-flex align-items-center justify-content-between" >
                              <span >
                                  <form action="plugins/actions/task_completion.php" method="post">
                                      <input type="hidden" name="task_id" value="<?php echo $row['id'];?>"> <!-- Example staff ID -->
                                      <input 
                                      style="display:none;"
                                          type="checkbox" 
                                          name="status" 
                                          id="statusCheckboxchecked - <?php echo $row['id'];?>"
                                          value="PENDING" 
                                          onclick="this.form.submit();" 
                                      >
                                      <label for="statusCheckboxchecked - <?php echo $row['id'];?>" class="custom-checkbox">
                                            <img src="plugins/images/checked.png" style="width: 30px; height: 30px; cursor: pointer;" alt="Checked" class="checkbox-image checked" />
                                      </label>
                                  </form>
                              </span>

                              <div id="taskdescription" class="mt-2" style="display:inline-block;">
                                  <h4 class="text-light"><?php echo $row['task_name']?></h4>
                                  <h5 class="text-secondary"><?php echo $row['task_description']?></h5>
                                  <p class="text-secondary"><?php echo $row['deadline']. " - ". ($row['task_duration']/60)?>min</p>

                              </div>

                              <span class="me-0">
                              
                  <form action="plugins/actions/task_remove.php" method="post">
                                  <input type="hidden" name="task_id" value="<?php echo $row['id']; ?>"> 

                                  <img 
                                      src="plugins/images/remove.png"  
                                      alt="Delete task" 
                                      style="width: 30px; height: 30px; cursor: pointer;" 
                                      onclick="this.closest('form').submit();" 
                                  >
                              </form>


                              
                              </span>

                          </div>
                          <br>
                          <?php }?>
                        </div>
                      </div>
                    </div>
                  
                        <div class="accordion-item">
                    <h2 class="accordion-header">
                      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                      <h2 class="text-center">Get templates</h2>
                      </button>
                    </h2>
                    <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                      <div class="accordion-body">
                          
                          <ul>
                            <div class="bg-dark d-flex align-items-center">
                              <li class="m-2" type="none"> <a href="plugins/samples/task_sample.xlsx.csv" download="task_sample">Task upload sample <span><img src="plugins/images/download.png" alt="download" style="width: 20px; height: 20px;"></span></a></li>
                            </div>
                            <br>
                              <div class="bg-dark d-flex align-items-center">
                              <li class="m-2" type="none"> <a href="plugins/samples/timetable_sample.xlsx.csv" download="timetable_sample">Timetable upload sample <span><img src="plugins/images/download.png" alt="download" style="width: 20px; height: 20px;"></span></a></li>
                            </div>
                          </ul>
                          
                      </div>
                    </div>
                      </div>
                  </div>
  </div>
  <div class="tab-pane fade" id="pills-finances" role="tabpanel" aria-labelledby="pills-finances-tab" tabindex="0">
  <div class="accordion " id="accordionExample">
              <div class="accordion-item" >
                  <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                    <h2 class="text-center">Transactions</h2>
                    </button>
                  </h2>
                  <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#accordionExample">
                    <div class="accordion-body">
                    <div id="transaction_header" class="d-flex justify-content-between">
                      <?php
                        $previous_month = date('Y-m',strtotime('last month'));
                        $this_month = date('Y-m',strtotime('this month'));
                        ?>
                          
                          
                          <div class="d-flex justify-content-between" style="width: 100%;">
                              <div class="nav" id="nav-tab" role="tablist" style="margin: auto;">
                                  <!-- Previous Month Tab Button -->
                                  <button class="nav-link d-flex align-items-start" id="nav-previousmonth-tab" 
                                      data-bs-toggle="tab" data-bs-target="#previousmonth" 
                                      type="button" role="tab" aria-controls="previousmonth" 
                                      aria-selected="false"><span><<?php echo $previous_month; ?></span>
                                      
                                  </button>

                                  <!-- This Month Tab Button -->
                                  <button class="nav-link active d-flex align-items-center" id="nav-thismonth-tab" 
                                      data-bs-toggle="tab" data-bs-target="#thismonth" 
                                      type="button" role="tab" aria-controls="thismonth" 
                                      aria-selected="true">
                                      Transaction History(<?php echo $this_month; ?>)
                                  </button>
                              </div>
                          </div>


                        </div>
                      
                  
                    
                        <div class="row my-3 px-5 py-3" style="height: 40vh; overflow-y:scroll;">
                          <div class="tab-content" id="nav-tabContent">
                              <!-- Previous Month Tab -->
                              <div class="tab-pane fade" id="previousmonth" role="tabpanel" aria-labelledby="nav-previousmonth-tab">
                                  <div class="table-responsive">
                                      <h1 class="d-flex justify-content-center" >Last month's transactions</h1>
                                      <table class="table table-bordered">
                                          <thead class="table-dark">
                                              <tr>
                                                  <th scope="col">Category Name</th>
                                                  <th scope="col">Category</th>
                                                  <th scope="col">Total Amount</th>
                                                  <th scope="col">Transaction Date</th>
                                              </tr>
                                          </thead>
                                          <tbody>
                                              <?php 
                                              $previous_month = date('m', strtotime('last month'));
                                              while ($row = $resultstransaction->fetch_assoc()) {
                                                  $transaction_month = date('m', strtotime($row['transaction_time']));
                                                  if ($transaction_month == $previous_month) { ?>
                                                      <tr class="bg-<?php echo $row['category'] == 'income' ? 'success' : 'danger'; ?>">
                                                          <th class="text-bg-<?php if($row['category'] == "income"){echo 'success';}else{echo 'danger';}?>" scope="row"><?php echo $row['category_name']; ?></th>
                                                          <td class="text-bg-<?php if($row['category'] == "income"){echo 'success';}else{echo 'danger';}?>"><?php echo $row['category']; ?></td>
                                                          <td class="text-bg-<?php if($row['category'] == "income"){echo 'success';}else{echo 'danger';}?>"><?php echo $row['total_amount']; ?></td>
                                                          <td class="text-bg-<?php if($row['category'] == "income"){echo 'success';}else{echo 'danger';}?>"><?php echo $row['transaction_time']; ?></td>
                                                      </tr>
                                              <?php } } ?>
                                          </tbody>
                                      </table>
                                  </div>
                              </div>

                              <!-- This Month Tab -->
                              <div class="tab-pane fade show active" id="thismonth" role="tabpanel" aria-labelledby="nav-thismonth-tab">
                                  <div class="table-responsive">
                                      <h1 class="d-flex justify-content-center" >This month's transactions</h1>
                                      <table class="table table-bordered">
                                          <thead class="table-dark">
                                              <tr>
                                                  <th scope="col">Category Name</th>
                                                  <th scope="col">Category</th>
                                                  <th scope="col">Total Amount</th>
                                                  <th scope="col">Transaction Date</th>
                                              </tr>
                                          </thead>
                                          <tbody>
                                              <?php 
                                              $current_month = date('m', strtotime('this month'));
                                              // Reset the data pointer to fetch results again
                                              $resultstransaction->data_seek(0); 
                                              while ($row = $resultstransaction->fetch_assoc()) {
                                                  $transaction_month = date('m', strtotime($row['transaction_time']));
                                                  if ($transaction_month == $current_month) { ?>
                                                      <tr class="bg-<?php echo $row['category'] == 'income' ? 'success' : 'danger'; ?>">
                                                          <th class="text-bg-<?php if($row['category'] == "income"){echo 'success';}else{echo 'danger';}?>" scope="row"><?php echo $row['category_name']; ?></th>
                                                          <td class="text-bg-<?php if($row['category'] == "income"){echo 'success';}else{echo 'danger';}?>"><?php echo $row['category']; ?></td>
                                                          <td class="text-bg-<?php if($row['category'] == "income"){echo 'success';}else{echo 'danger';}?>"><?php echo $row['total_amount']; ?></td>
                                                          <td class="text-bg-<?php if($row['category'] == "income"){echo 'success';}else{echo 'danger';}?>"><?php echo $row['transaction_time']; ?></td>
                                                      </tr>
                                              <?php } } ?>
                                          </tbody>
                                      </table>
                                  </div>
                              </div>
                          </div>
                      </div>



                
                  <br>

                     

                  <span><a data-bs-toggle="modal" data-bs-target="#transactions-modal" role="button" ><img height="20" width="20" src="plugins/images/add.png" alt="add transactions"></a></span>   

                  </div>
                  </div>                                      
              </div>
              <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                    <h2 class="text-center">Resources</h2>
                    </button>
                  </h2>
                  <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                    <div class="accordion-body">
                        <div id="resource_header" class="d-flex justify-content-center align-items-center">
                          <h3 class="text-center">Resources History</h3>
                        </div>

                        <table class="table table-hover table-bordered">
                          <thead class="table-dark">
                            <tr>
                              <th scope="col">Category Name</th>
                              <th scope="col">Description</th>
                              <th scope="col">Total Amount</th>
                              <th scope="col">Cashflow</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php while ($row = $resultsresources->fetch_assoc()) { ?>
                              <tr 
                                data-bs-toggle="modal" 
                                data-bs-target="#update-resources-modal-<?php echo $row['resource_id']; ?>" 
                                role="button"
                                class="bg-<?php echo ($row['category'] == 'asset') ? 'success' : 'danger'; ?>"
                              >
                                <th class="text-bg-<?php echo ($row['category'] == 'asset') ? 'success' : 'danger'; ?>">
                                  <?php echo $row['item_name']; ?>
                                </th>
                                <td class="text-bg-<?php echo ($row['category'] == 'asset') ? 'success' : 'danger'; ?>">
                                  <?php echo $row['category']; ?>
                                </td>
                                <td class="text-bg-<?php echo ($row['category'] == 'asset') ? 'success' : 'danger'; ?>">
                                  <?php echo number_format($row['item_price'], 2); ?>
                                </td>
                                <td class="text-bg-<?php echo ($row['category'] == 'asset') ? 'success' : 'danger'; ?>">
                                  <?php echo $row['cashflow']; ?>
                                </td>
                              </tr>

                              <!-- Update Resources Modal -->
                              <div class="modal fade" 
                                  id="update-resources-modal-<?php echo $row['resource_id']; ?>" 
                                  tabindex="-1" 
                                  aria-labelledby="update-resources-modal-title-<?php echo $row['resource_id']; ?>" 
                                  aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                  <div class="modal-content">
                                    <div class="modal-header">
                                      <h5 class="modal-title" id="update-resources-modal-title-<?php echo $row['resource_id']; ?>">
                                        Update Resource
                                      </h5>
                                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>

                                    <div class="modal-body">
                                      <form action="" method="post">
                                        <input type="hidden" name="resource" value="<?php echo $row['resource_id']; ?>">

                                        <div class="row g-3">
                                          <div class="col-md-6">
                                            <div class="input-group">
                                              <span class="input-group-text">Resource</span>
                                              <input type="text" name="name" class="form-control" 
                                                    value="<?php echo $row['item_name']; ?>" placeholder="Enter resource name">
                                            </div>
                                          </div>

                                          <div class="col-md-12">
                                            <div class="input-group">
                                              <span class="input-group-text">Description</span>
                                              <textarea name="description" class="form-control">
                                                <?php echo $row['item_description']; ?>
                                              </textarea>
                                            </div>
                                          </div>

                                          <div class="col-md-12">
                                            <div class="input-group">
                                              <span class="input-group-text">Personal Notes</span>
                                              <textarea name="notes" class="form-control">
                                                <?php echo $row['personal_notes']; ?>
                                              </textarea>
                                            </div>
                                          </div>

                                          <div class="col-md-6">
                                            <div class="input-group">
                                              <span class="input-group-text">Item Price</span>
                                              <input type="text" name="price" class="form-control" 
                                                    value="<?php echo $row['item_price']; ?>" placeholder="Enter price">
                                            </div>
                                          </div>

                                          <div class="col-md-6">
                                            <div class="input-group">
                                              <span class="input-group-text">Cashflow</span>
                                              <input type="text" name="cashflow" class="form-control" 
                                                    value="<?php echo $row['cashflow']; ?>" placeholder="Enter cashflow">
                                            </div>
                                          </div>
                                        </div>

                                        <div class="modal-footer">
                                          <div class="d-flex justify-content-center w-100">
                                            <button type="button" class="btn btn-danger w-50" data-bs-dismiss="modal">
                                              Delete
                                            </button>
                                            <h3 class="mx-2">OR</h3>
                                            <button type="submit" class="btn btn-primary w-50" name="update_resources">
                                              Update
                                            </button>
                                          </div>
                                        </div>
                                      </form>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            <?php } ?>
                          </tbody>
                        </table>

                        <span>
                          <a data-bs-toggle="modal" data-bs-target="#resources-modal" role="button">
                            <img height="20" width="20" src="plugins/images/add.png" alt="Add Resources">
                          </a>
                        </span>
                      </div>

                  </div>                                      
              </div>

              <div class="accordion-item">
                <h2 class="accordion-header">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                  <h2 class="text-center">Budget</h2>
                  </button>
                </h2>
                <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                <div class="accordion-body">
                    <div id="budget_header" class="d-flex justify-content-between">
                      <?php
                        $previous_month = date('Y-m',strtotime('last month'));
                        $this_month = date('Y-m',strtotime('this month'));
                        ?>
                          
                          
                          
                          <div class="d-flex justify-content-between" style="width: 100%;">
                              <div class="nav" id="nav-tab" role="tablist" style="margin: auto;">
                                  <!-- Previous Month Tab Button -->
                                  <button class="nav-link d-flex align-items-start" id="nav-previousbudgetmonth-tab" 
                                      data-bs-toggle="tab" data-bs-target="#previousbudgetmonth" 
                                      type="button" role="tab" aria-controls="previousbudgetmonth" 
                                      aria-selected="false"><span><<?php echo $previous_month; ?></span>
                                      
                                  </button>

                                  <!-- This Month Tab Button -->
                                  <button class="nav-link active d-flex align-items-center" id="nav-thisbudgetmonth-tab" 
                                      data-bs-toggle="tab" data-bs-target="#thisbudgetmonth" 
                                      type="button" role="tab" aria-controls="thisbudgetmonth" 
                                      aria-selected="true">
                                      Budgets(<?php echo $this_month; ?>)
                                  </button>
                              </div>
                          </div>




                        </div>
                      
                  
                    
                        <div class="row my-3 px-5 py-3" style="height: 40vh; overflow-y:scroll;">
                          <div class="tab-content" id="nav-tabContent">
                              <!-- Previous Month Tab -->
                              <div class="tab-pane fade" id="previousbudgetmonth" role="tabpanel" aria-labelledby="nav-previousbudgetmonth-tab">
                                  <div class="table-responsive">
                                      <h1 class="d-flex justify-content-center" >Last month's budget</h1>
                                      <div id="budgets">
                                                          
                                      <?php 
                                              $previous_month = date('F', strtotime('first day of last month'));

                                              while ($row = $resultsbudget->fetch_assoc()) {
                                                  $budget_month = $row['budget_month'];
                                                  if ($budget_month === $previous_month) { 

                                        // Example Data (You can replace these with database values)
                                        $total_amount = $row['budget_amount'] ;  // The target amount (e.g., total amount to be saved)
                                        $current_value = $row['budget_amount'] - (!empty($row['budget_amount_remaining'])?$row['budget_amount_remaining'] : 0); // Current value achieved (e.g., saved amount)
                                                      
                                        // Calculate the percentage progress
                                        // Check if total goal is zero to avoid division by zero
                                        if ($total_goal > 0) {
                                          // Calculate the percentage progress
                                          $progress_percentage = ($current_value / $total_amount) * 100;
                                      } else {
                                          $progress_percentage = 0; // Set to 0% if the goal is 0
                                      }

                                        $progress_percentage = min($progress_percentage, 100); // Ensure it doesn't exceed 100%


                                            // Determine the progress bar color based on percentage
                                                if ($progress_percentage < 50) {
                                                  $progress_color = 'bg-success';  // Green
                                              } elseif ($progress_percentage <= 75) {
                                                  $progress_color = 'bg-warning';  // Yellow
                                              } else {
                                                  $progress_color = 'bg-danger';   // Red
                                              }
                                                                        ?>
                                                                <div class="container my-4">
                                            
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>(<?php echo $row['category_name']; ?>) Used: <?php echo $current_value; ?> GH₵ / <?php echo $total_amount; ?> GH₵</span>
                                                <span><?php echo round($progress_percentage, 2); ?>%</span>
                                            </div>

                                          

                                            <!-- Progress Bar -->
                                            <div class="progress" style="height: 30px;">
                                                <div class="progress-bar <?php echo $progress_color; ?>" 
                                                    role="progressbar" 
                                                    style="width: <?php echo $progress_percentage; ?>%;" 
                                                    aria-valuenow="<?php echo $progress_percentage; ?>" 
                                                    aria-valuemin="0" 
                                                    aria-valuemax="100">
                                                    <?php echo round($progress_percentage, 2); ?>%
                                                </div>
                                            </div>

                                            <!-- Add some styling to make it look better -->
                                            <div class="d-flex justify-content-center mt-2">
                                                <small>
                                                <?php echo $row['note']; ?>
                                                </small>
                                            </div>
                                        </div>
                                        <hr>
                                        <?php } } ?>

                                      </div>
                                      
                                     
                                  </div>
                              </div>

                              <?php
                                // Reset the result set pointer for reuse
                                $resultsbudget->data_seek(0);
                                ?>

                              <!-- This Month Tab -->
                              <div class="tab-pane fade show active" id="thisbudgetmonth" role="tabpanel" aria-labelledby="nav-thisbudgetmonth-tab">
                                  <div class="table-responsive">
                                      <h1 class="d-flex justify-content-center" >This month's budget</h1>
                                          

                                      <div id="budgets">
                                                          
                                      <?php 
                                              $current_month = date('F');

                                              while ($row = $resultsbudget->fetch_assoc()) {
                                                  $budget_month = $row['budget_month'];
  
                                                  if ($budget_month === $current_month) {

                                        // Example Data (You can replace these with database values)
                                        $total_amount = $row['budget_amount'] ;  // The target amount (e.g., total amount to be saved)
                                        $current_value = $row['budget_amount'] - (!empty($row['budget_amount_remaining'])?$row['budget_amount_remaining'] : 0); // Current value achieved (e.g., saved amount)
                                                      
                                        // Calculate the percentage progress
                                        // Check if total goal is zero to avoid division by zero
                                        if ($total_amount > 0) {
                                          // Calculate the percentage progress
                                          $progress_percentage = ($current_value / $total_amount) * 100;
                                      } else {
                                          $progress_percentage = 0; // Set to 0% if the goal is 0
                                      }
                                        $progress_percentage = min($progress_percentage, 100); // Ensure it doesn't exceed 100%

                                        // Determine the progress bar color based on percentage
                                                if ($progress_percentage < 50) {
                                                  $progress_color = 'bg-success';  // Green
                                              } elseif ($progress_percentage <= 75) {
                                                  $progress_color = 'bg-warning';  // Yellow
                                              } else {
                                                  $progress_color = 'bg-danger';   // Red
                                              }
                                                                        ?>
                                                                <div class="container my-4">
                                            
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>(<?php echo $row['category_name']; ?>) Used: <?php echo $current_value; ?> GH₵ / <?php echo $total_amount; ?> GH₵</span>
                                                <span><?php echo round($progress_percentage, 2); ?>%</span>
                                            </div>

                                          

                                            <!-- Progress Bar -->
                                            <div class="progress" style="height: 30px;">
                                                <div class="progress-bar <?php echo $progress_color; ?>"  
                                                    role="progressbar" 
                                                    style="width: <?php echo $progress_percentage; ?>%;" 
                                                    aria-valuenow="<?php echo $progress_percentage; ?>" 
                                                    aria-valuemin="0" 
                                                    aria-valuemax="100">
                                                    <?php echo round($progress_percentage, 2); ?>%
                                                </div>
                                            </div>

                                            <!-- Add some styling to make it look better -->
                                            <div class="d-flex justify-content-center mt-2">
                                                <small>
                                                <?php echo $row['note']; ?>
                                                </small>
                                            </div>
                                        </div>
                                        <hr>
                                        <?php } } ?>

                                      </div>

                                      
                                  </div>
                              </div>
                          </div>
                      </div>



                
                  <br>

                     
                     <span><a data-bs-toggle="modal" data-bs-target="#budget-modal" role="button" ><img height="20" width="20" src="plugins/images/add.png" alt="add budget"></a></span>   


                  </div>
                </div>
              </div>
            
                  <div class="accordion-item">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                <h2 class="text-center">Get business knowledge</h2>
                </button>
              </h2>
              <div id="collapseFour" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                <div class="accordion-body">

                <h3>Here business knowledges appear should we be able to add from here ?</h3>
                    
                    <ul>
                      <div class="d-flex align-items-center">
                        <li class="m-2" type="none"> <a target="_blank" href="https://www.investopedia.com/terms/i/indexfund.asp" >Index Funds</a></li>
                      </div>
                      <br>
                    <!--     <div class="bg-dark d-flex align-items-center">
                        <li class="m-2" type="none"> <a href="plugins/samples/timetable_sample.xlsx.csv" download="timetable_sample">Timetable upload sample <span><img src="plugins/images/download.png" alt="download" style="width: 20px; height: 20px;"></span></a></li>
                      </div>
                    </ul> -->
                    
                </div>
              </div>
                </div>
            </div>

  </div>
  <div class="tab-pane fade" id="pills-websites" role="tabpanel" aria-labelledby="pills-websites-tab" tabindex="0">
                            
  
          <h2 class="text-primary text-center" >Favourite Websites</h2>
                      <?php 
                      $resultswebsitesquery = "SELECT * FROM favourite_sites ORDER BY website_category";

                      $result = $conn->query($resultswebsitesquery);

                      $current_website = "";  // Track the current category
                      if ($result->num_rows > 0) {
                          while ($row = $result->fetch_assoc()) {
                              // Check if we're starting a new website group (new category)
                              if ($row['website_category'] != $current_website) {
                                  if ($current_website != "") echo "</ul><br>";  // Close the previous list
                                  $current_website = $row['website_category'];
                      
                                  // Display the category header with blue color and margin
                                  echo "<h3 style='color: blue; margin-top: 15px;'>" . htmlspecialchars($current_website) . "</h3>";
                                  
                                  // Add padding-left to indent the list
                                  echo "<ul style='list-style-type: none; padding-left: 20px; margin-top: 10px;'>";  
                              }
                      
                              // Display individual website information with spacing between items
                              echo "<li style='margin-bottom: 20px;'>";  // Gap between items
                              echo "<a href='" . htmlspecialchars($row['website_url']) . "' target='_blank' style='color: black; text-decoration: none;'>";
                              echo "<strong>" . htmlspecialchars($row['website_name']) . "</strong><br>";
                              echo "<small>" . htmlspecialchars($row['website_description']) . "</small>";
                              echo "</a>";
                              echo "</li>";
                          }
                          echo "</ul><br>";  // Close the last list
                      }
                      ?>

<fieldset class="border border-primary rounded-3 p-4 mx-auto my-5 w-75 w-md-50">
    <legend class="fw-bold text-primary">Add Favourite Website</legend>

    <form action="" method="post">
        <input type="hidden" name="assigner" id="assigner" value="<?php echo $user_id; ?>">

        <div class="col-12 mb-3">
            <div class="input-group">
                <span class="input-group-text bg-white border-0">
                    <label for="name">
                        <img src="plugins/images/webname.jpg" alt="Website Icon" width="30" height="30">
                    </label>
                </span>
                <input type="text" name="name" id="name" class="form-control" 
                       placeholder="Enter Website Name" required>
            </div>
        </div>

        <div class="col-12 mb-3">
            <div class="input-group">
                <span class="input-group-text bg-white border-0">
                    <label for="url">
                        <img src="plugins/images/url.jpg" alt="URL Icon" width="30" height="30">
                    </label>
                </span>
                <textarea name="url" id="url" class="form-control" 
                          placeholder="Type full website URL"></textarea>
            </div>
        </div>

        <div class="col-12 mb-3">
            <div class="input-group">
                <span class="input-group-text bg-white border-0">
                    <label for="description">
                        <img src="plugins/images/text.jpg" alt="text Icon" width="30" height="30">
                    </label>
                </span>
                <textarea name="description" id="description" class="form-control" 
                          placeholder="Type  website description"></textarea>
            </div>
        </div>

        <div class="col-12 mb-3">
            <div class="input-group">
                <span class="input-group-text">Category</span>
                <select name="category" id="category" class="form-select" aria-label="Category">
                    <option value="" disabled selected>Select category</option>
                    <?php
                    $categories = ['Sport', 'News', 'Business', 'Leisure', 'Games', 'School', 'Work'];
                    foreach ($categories as $category) {
                        echo "<option value=\"$category\">$category</option>";
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="col-12">
            <input type="submit" class="btn btn-primary w-100" name="add_website" value="Add Website">
        </div>

    </form>
</fieldset>
                        
                     
  </div>
  <div class="tab-pane fade" id="pills-saturday" role="tabpanel" aria-labelledby="pills-saturday-tab" tabindex="0">Saturday</div>
</div>
            
                            
            



            <!-- Offcanvas Sidebar -->
            <div class="offcanvas offcanvas-start w-75" style="background-color:bisque" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel">
                    <div class="offcanvas-header">
                      <h5 class="offcanvas-title" id="offcanvasExampleLabel">financial Statement</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body w-100">
                      <div class="d-flex justify-content-between " >
                        <div id="left" >
                              <div class="income " id="income">
                                <header class="bg-secondary d-flex w-100" >
                                  <h2 class="text-white">Income</h2>
                                </header>
                                
                                <main id="income" >
                                  <div class="d-flex justify-content-end"id="header">
                                    <h3 class="">Cashflow</h3>
                                    <hr>
                                  </div>
                                  
                                  <?php 
                                  
                                  if($resultsincome->num_rows > 0){
                                  while($row = $resultsincome -> fetch_assoc()){?>
                                    <div class="d-flex justify-content-between" id="income-<?php echo $row['transaction_id'] ?>">
                                    <h3 class=""><?php echo $row['category_name'];?>  </h3>
                                    <h3 class="d-flex justify-content-end text-success"><?php echo $row['total_amount'];?> GH₵</h3>
                                    
                                    </div>
                                    <hr style="color: black; border: 1px;">
                                  <?php }}?>
                        
                            
                                </main>
                              </div>

                            <div class="expense" id="expense">
                              <header class="bg-secondary" >
                                <h2 class="text-white">Expense</h2>
                              </header>
                              
                              <main id="expense-cashflow" >
                                <div class="d-flex justify-content-end" id="header">
                                  <h3 class="">Cashflow</h3>
                                  <hr>
                                </div>
                                <?php 
                                
                              if($resultsexpense->num_rows > 0){
                                while($row = $resultsexpense -> fetch_assoc()){?>
                                  <div class="d-flex justify-content-between" id="expense<?php echo $row['transaction_id'] ?>">
                                  <h3 class=""><?php echo $row['category_name'];?> </h3> 
                                  <h3 class="d-flex justify-content-end text-danger"><?php echo $row['total_amount'];?> GH₵</h3>
                                  
                                  </div>
                                  <hr style="color: black; border: 1px;">
                                <?php }}?>

                              

                                </main>
                            </div>



                        </div>
                       

                        <div id="right"  >
                                <div class="passive_income" id="passive_income">
                                  <header class="bg-secondary" >
                                    <h2 class="text-white">Financial Goal</h2>
                                    <h4 class="text-white d-flex justify-content-end"><?php echo $goal;?> GH₵</h4>
                                  </header>
                                  
                                  <main id="passive_income" >
                                    
                                      

                                      <?php
                                        // Example Data (You can replace these with database values)
                                        $total_goal = $goal;  // The target goal (e.g., total amount to be saved)
                                        $current_value = $passive_income; // Current value achieved (e.g., saved amount)

                                        // Check if total goal is zero to avoid division by zero
                                          if ($total_goal > 0) {
                                            // Calculate the percentage progress
                                            $progress_percentage = ($current_value / $total_goal) * 100;
                                        } else {
                                            $progress_percentage = 0; // Set to 0% if the goal is 0
                                        }

                                        // Calculate the percentage progress
                                        $progress_percentage = min($progress_percentage, 100); // Ensure it doesn't exceed 100%
                                        ?>
                                                                <div class="container my-4">
                                            
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Achieved: <?php echo $current_value; ?> GH₵ / <?php echo $total_goal; ?> GH₵</span>
                                                <span><?php echo round($progress_percentage, 2); ?>%</span>
                                            </div>

                                          

                                            <!-- Progress Bar -->
                                            <div class="progress" style="height: 30px;">
                                                <div class="progress-bar 
                                                    <?php echo $progress_percentage >= 100 ? 'bg-success' : 'bg-info'; ?>" 
                                                    role="progressbar" 
                                                    style="width: <?php echo $progress_percentage; ?>%;" 
                                                    aria-valuenow="<?php echo $progress_percentage; ?>" 
                                                    aria-valuemin="0" 
                                                    aria-valuemax="100">
                                                    <?php echo round($progress_percentage, 2); ?>%
                                                </div>
                                            </div>

                                            <!-- Add some styling to make it look better -->
                                            <div class="d-flex justify-content-center mt-2">
                                                <small>
                                                    <?php 
                                                    if ($progress_percentage >= 100) {
                                                        echo "Goal achieved! 🎉";
                                                    } else {
                                                        echo "Keep going! You're almost there!";
                                                    }
                                                    ?>
                                                </small>
                                            </div>
                                        </div>
                          <br>
                          <br>
                                    
                                  </main>
                                </div>

                              <div class="" id="balance_sheet">
                                <header class="d-flex justify-content-between" >
                                    <h2 class="text-white">Cash: </h2>
                                    <h2 class="text-white "><?php echo $cash; ?> GH₵</h2>
                                  
                                </header>
                                
                                <main id="expense" >

                                    <div class="d-flex justify-content-between" id="total_income">
                                    <h3 class="">Total_income:</h3>
                                    <h3 class="d-flex justify-content-end text-success"><?php echo $total_income;?> GH₵</h3>
                                    
                                    </div>

                                    <div class="d-flex justify-content-between" id="total_expense">
                                    <h3 class="">Total expense:</h3>
                                    <h3 class="d-flex justify-content-end text-danger"><?php echo $total_expense;?> GH₵</h3>
                                    
                                    </div>
                                    <hr style="color: black; border: 1px;">

                                    <div class="d-flex justify-content-between" id="total_balance">
                                    <h3 class="">Payday:</h3>
                                    <h3 class=""><?php echo $balance;?> GH₵</h3>
                                    
                                    </div>
                                    <hr style="color: black; border: 1px;">

                                  

                                  </main>
                              </div>


                            
                          </div>
                      
                      </div>
                      
                      
                      
                      
                      <div class="d-flex justify-content-between" >
                        <div id="left" >
                              <div class="assets" id="assets">
                                <header class="bg-secondary" >
                                  <h2 class="text-white">Assets</h2>
                                </header>
                                
                                <main id="assets" >




                                    <div class="d-flex justify-content-between" id="assetss?>">
                                    <h3 class="">Stocks/Funds/CDs</h3>
                                    <h3 class="d-flex justify-content-end">Shares/$</h3>
                                    
                                    </div>
                                    <hr style="color: black; border: 1px;">

                                  
                                  <?php 
                                  
                                  if($resultsassets->num_rows > 0){
                                  while($row = $resultsassets -> fetch_assoc()) {if($row['category_name'] != 'real estates'){?>
                                    <div class="d-flex justify-content-between" id="assets-<?php echo $row['resource_id']; ?>">
                                    <h3 class=""><?php echo $row['item_name'] ;?></h3>
                                    <h3 class="d-flex justify-content-end text-success"><?php echo $row['cashflow'] ;?></h3>
                                    
                                    </div>
                                    <hr style="color: black; border: 1px;">
                                  <?php }}}?>
                        
                                   
                                  <div class="d-flex justify-content-between" id="costs?>">
                                  <h3 class="">Real Estate/Business</h3>
                                  <h3 class="d-flex justify-content-end ">Cost</h3>
                                  
                                  </div>
                                  <hr style="color: black; border: 1px;">

                                  <?php 

                                  if($resultsassets->num_rows > 0){
                                    while($row = $resultsassets -> fetch_assoc()){if($row['category_name'] == 'real estates'){?>
                                  <div class="d-flex justify-content-between" id="cost-<?php echo $row['resource_id']; ?>">
                                  <h3 class=""><?php echo $row['item_name'] ;?></h3>
                                  <h3 class="d-flex justify-content-end text-danger"><?php echo $row['cashflow'] ;?></h3>
                                  
                                  </div>
                                  <hr style="color: black; border: 1px;">
                                  <?php }}}?>


                               

                              

                                </main>
                                </div>

                        </div>



                        <div id="right"  >


                        <div class="liabilities" id="liabilities">
                                <header class="bg-secondary" >
                                  <h2 class="text-white">Liabilities</h2>
                                </header>
                                
                                <main id="liabilities" >
                                  
                                  <?php 
                                  
                                  if($resultsliabilities->num_rows > 0){
                                  while($row = $resultsliabilities -> fetch_assoc()){if($row['category_name'] != 'real estates'){?>
                                    <div class="d-flex justify-content-between" id="liabilities-<?php echo $row['resource_id'] ?>">
                                    <h3 class=""><?php echo $row['item_name'] ;?></h3>
                                    <h3 class="d-flex justify-content-end text-danger"><?php echo $row['cashflow'] ;?></h3>
                                    
                                    </div>
                                    <hr style="color: black; border: 1px;">
                                  <?php }}}?>
                        
                                    

                                
                                  <div class="d-flex justify-content-between" id="liabilities">
                                  <h3 class="">Real Estate/Business</h3>
                                  <h3 class="">Liability</h3>
                                  
                                  </div>
                                  <hr style="color: black; border: 1px;">




                                <?php 
                             if($resultsliabilities->num_rows > 0){
                                while($row = $resultsliabilities -> fetch_assoc()){if($row['category_name'] == 'real estates'){?>
                                  <div class="d-flex justify-content-between" id="cost-<?php echo $row['resource_id']; ?>">
                                    <h3 class=""><?php echo $row['item_name'] ;?></h3>
                                    <h3 class="d-flex justify-content-end text-danger"><?php echo $row['cashflow'] ;?></h3>
                                  
                                  </div>
                                  <hr style="color: black; border: 1px;">
                                <?php }}} ?>

                              

                                </main>
                                </div>


                            
                          </div>
                      
                      </div>


                        
                        
                        

                      </div>
                      
                      
                      </div>


                      
    </div>


            


          <!-- this is the logout modal -->
          <div class="modal fade" id="logout-modal" tabindex="-1" aria-labelledby="logout-modal-title" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="logout-modal-title">Logout</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <p>This will log you out. Do you still want to proceed?</p>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  
                  <form action="plugins/actions/logout.php" method="post">
                  <button type="submit" class="btn btn-outline-danger" name="logout"  id="logout">Logout</button>
                  </form>
              </div>
            </div>
          </div>
        </div>



        <!-- this is the tasks modal -->
<div class="modal fade" id="add-tasks-modal" tabindex="-1" aria-labelledby="add-tasks-modal-title" aria-hidden="false">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="add-tasks-modal-title">Tasks</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                <form action="" method="post" style="width: 560px;">
        <input type="hidden" name="assigner" id="assigner" value = "<?php echo $user_id; ?>">

    <div class="col-8">
                <div class="input-group ">
                <span class="input-group-text"><label for="name">Enter Task Name</label></span>
                <input type="text" name="name" id="name" required>
                </div>
            
    </div>
        
        <br>

        <div class="col-8">
                <div class="input-group ">
                <span class="input-group-text"><label for="description">Description</label></span>
                <textarea name="description" id="description"></textarea>
                </div>
        </div>
        
        <br>
        
        <div class="col-8">
                <div class="input-group ">
                <span class="input-group-text"><label for="deadline"> Deadline</label></span>
                <input type="datetime-local" placeholder="Y/M/D H:m:s" name="deadline" id="deadline" required>
                </div>
        </div>
        
        <br>

        <div class="col-8">
                <div class="input-group ">
                <span class="input-group-text"><label for="duration">Duration</label></span>
                <input type="number" name="duration" id="duration"> <span class="input-group-text">Seconds</span>
                </div>
        </div>
        
        <br>
        <div class="col-8">
            <div class="input-group">
                <span class="input-group-text"><label for="file">Add file</label></span>
                <input type="file" class="form-control" name="file" id="file" >
            </div>
            <small class="form-text text-muted">Supported file types: PDF, DOC, DOCX, JPG, JPEG, PNG.</small>
        </div>

        
        <br>


                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  
                  <input class="btn btn-primary" type="submit" name="submit" id="submit">
    </form>
              </div>
            </div>
          </div>

</div>



              
         

        <?php } else{?>

          <!-- this is the Signup modal -->
         
          <div class="modal fade" id="signup-modal" tabindex="-1" aria-labelledby="signup-modal-title" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
              <div class="modal-content">
                <div class="modal-header">
                  <h2 class="modal-title" id="signup-modal-title">Signup</h2>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="plugins/actions/signup.php" method="post">
                <div class="modal-body">
                        <div class="row g-3">

                            <div class="col-12">

                                <input type="text" placeholder="full name" class="form-control" name="name" id="name" required>

                            </div>


                            <div class="col-6">
                            <div class="input-group ">
                            <span class="input-group-text">Enter Birthdate</span>
                            <input type="date" class="form-control" id="date_of_birth" name="birthdate" required >
                            </div>
                            <div class="invalid-feedback">
                            input your date of birth.
                            </div>
                            </div>


                            <div class="col-6">
                            <input type="text" class="form-control"placeholder="+233 2005005090" id="phone" name="phone" required>
                            <div class="invalid-feedback">
                            Please enter your phone number.
                            </div>
                            </div>


                            <div class="col-12">
                            <div class="input-group has-validation">
                            <span class="input-group-text">@</span>
                            <input type="text" class="form-control" id="username" placeholder="Username" required name="username">
                            <div class="invalid-feedback">
                            Your username is required.
                            </div>
                            </div>
                            </div>



                            <div class="col-12">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter a password" required>
                            <span class="input-group-text bg-transparent border-0" id="togglePassword">
                            <i class="bi bi-eye-slash" id="toggleIcon"></i>
                            </span>
                            <div class="invalid-feedback">
                            password is required
                            </div>
                            <div id="passwordHelpBlock" class="form-text">
                            <small>Your password must be 8-20 characters long, contain letters and numbers, and must not contain spaces, special characters, or emoji. </small>
                            </div>
                            </div>


                        
                        </div>
                <div class="modal-footer">
                  
                  <button class="w-100 btn btn-primary btn-lg"  name="sign_up">signup</button>
                </div> 
                    </div>
                  </form>
                  </div>
              </div>
            </div>
          <!-- this is the login modal -->
         
          <div class="modal fade" id="login-modal" tabindex="-1" aria-labelledby="login-modal-title" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered " style="width: 560vh;">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="login-modal-title">Login</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                <div id="login" class="" style="width: 60vh;">
                    <h2 class="text-center">Login Form</h2>
                    
                
            <form action="plugins/actions/login.php" method="post">
                <div class="row g-3">

                    

              <div class="col-12">
                  <div class="input-group has-validation">
                  <span class="input-group-text">@</span>
                  <input type="text" class="form-control" id="username" placeholder="Username" required name="username">
                  <div class="invalid-feedback">
                      Your username is required.
                  </div>
                  </div>
              </div>
    
              
           
              <div class="col-12">
                  <input type="password" class="form-control" id="password" name="password" placeholder="Enter a password" required>
                  <span class="input-group-text bg-transparent border-0" id="togglePassword">
                    <i class="bi bi-eye-slash" id="toggleIcon"></i>
                </span>
                  <div class="invalid-feedback">
                      password is required
                  </div>
                 
              </div>
                </div>
                <div class="modal-footer">
                  
                  <button class="w-100 btn btn-primary btn-lg"  name="login">login</button>
            </div> 

            </form> 
        </div>
              </div>
            </div>
          </div>
        </div>

        <?php } ?>

            <!-- footer -->
            <!-- ============================================================== -->
            <footer class="footer text-center bg-dark text-white py-3 w-100 position-absolute b+m-0 "> 2024 © Task manager brought to you by JopalBusinessCenter
                    <p>Theme was reproduced from <a
                    href="https://www.wrappixel.com/">wrappixel.com</a> with permission from the author.</p>

            </footer>
            <!-- ============================================================== -->
            <!-- End footer -->
            <!-- ============================================================== -->





      
</body>




<script src="plugins/bower_components/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap tether Core JavaScript -->
    <script src="plugins/js/bootstrap.bundle.min.js"></script>
    <script src="plugins/js/app-style-switcher.js"></script>
    <!--Wave Effects -->
    <script src="plugins/js/waves.js"></script>
    <!--Menu sidebar -->
    <script src="plugins/js/sidebarmenu.js"></script>
    <!--Custom JavaScript -->
    <script src="plugins/js/custom.js"></script>
</html>
                                              


