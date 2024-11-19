
<?php

require_once("plugins/actions/config.php");
require_once("plugins/actions/family_functions.php");  

session_start();

if (isset($_SESSION['user_id'])) {

  $user_id = $_SESSION['user_id'];
  
  // Retrieve user data
  $user_data = getUserData($user_id, $conn);
  $family_id = $user_data['family_id'];
  $username = $user_data['username'];
  $user_profile_pic = $user_data['profile_pic'];
  $cash = $user_data['balance'] ?? 0;
  $goal = $user_data['financial_goal'] ?? 0;
  $family_position = $user_data['family_position'] ?? 'Your role (e.g., parent, sibling)';

  $only_user_income = getTransactionSum( $user_id, 'income', $conn);
  $only_user_expense = getTransactionSum( $user_id, 'expense', $conn);
  $positive_user_cashflow = getCashflow($user_id, 'asset', $conn);
  $negative_user_cashflow = getCashflow($user_id, 'liability', $conn);
  $passive_user_income = $positive_user_cashflow - $negative_user_cashflow;
  $total_user_income = $passive_user_income + $only_user_income;
  $total_user_expense = $only_user_expense;
  
  // Calculate balance and update in database
  $user_balance = $total_user_income - $total_user_expense;
  updateUserBalance($user_id, $user_balance, $conn);

  if (isset($family_id)) {
    // Retrieve family data
    $family_details = getFamilyDetails($conn, $family_id);
    $family_name = $family_details['name'];
    $family_profile_pic = $family_details['profile_pic'];
    $cash = $family_details['balance'] ?? 0;
    $family_description = $family_details['family_description'] ?? 0;
    $goal = $family_details['financial_goal'] ?? 0;

    // Retrieve additional data for family financials, transactions, etc.
    $resultsBudget = getFinancialData($conn, 'budget', $family_id);
    $resultsResources = getFinancialData($conn, 'resources', $family_id);
    $resultsTransactions = getFinancialData($conn, 'transactions', $family_id);
    $only_income = getCategorySum($conn, $family_id, 'income');
    $only_expense = getCategorySum($conn, $family_id, 'expense');
    $positive_cashflow = getCashflowByCategory($conn, $family_id, 'asset');
    $negative_cashflow = getCashflowByCategory($conn, $family_id, 'liability');
    $passive_income = $positive_cashflow - $negative_cashflow;
    $total_income = $passive_income + $only_income;
    $total_expense = $only_expense;
    
    // Calculate balance and update in database
    updateBalance($conn, $family_id, $total_income, $total_expense);


   // Fetch all categories
$financial_categories = fetchFinancialCategories($conn, $family_id);


// Handling financial categories
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    // Retrieve form values
    $name = mysqli_real_escape_string($conn, $_POST['category_name']);
    $category_type = mysqli_real_escape_string($conn, $_POST['category_type']); // Asset, Liability, Income, Expense
    $category_level = $_POST['category_level']; // Parent or Child

    // Set parent_id based on category level
    $parent_id = ($category_level === 'child' && !empty($_POST['parent_category'])) 
                 ? mysqli_real_escape_string($conn, $_POST['parent_category']) 
                 : null;

    // Call function to add category
    $result = addFinancialCategory($conn, $name, $parent_id, $category_type, $family_id);

    // Provide success or error feedback
    if ($result) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?success=Category added successfully!");
    } else {
        header("Location: " . $_SERVER['PHP_SELF'] . "?error=Failed to add category.");
    }
    exit();
}



// Check if form is submitted to update a category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
    // Retrieve form values
    $id = mysqli_real_escape_string($conn, $_POST['category_id']);
    $name = mysqli_real_escape_string($conn, $_POST['category_name']);
    $parent_id = mysqli_real_escape_string($conn, $_POST['parent_category']);
    $category_type = mysqli_real_escape_string($conn, $_POST['category_type']);

    // Call function to update category
    $result = updateFinancialCategory($conn, $id, $name, $parent_id, $category_type, $family_id);

    // Provide success or error feedback
    if ($result) {
        header("Location: " . $_SERVER['PHP_SELF']);
    } else {
        header("Location: " . $_SERVER['PHP_SELF']);
    }
    exit();
}

// update family profile
if (isset($_POST['update_family'])) {
    // Get form inputs
    $family_name = $_POST['family_name'] ?? '';
    $family_goal = $_POST['family_goal'] ?? '';
    $family_description = $_POST['family_description'] ?? '';
    $family_profile_pic = $_FILES['family_profile_pic'];
    $old_family_profile_pic = $_POST['old_family_profile_pic'] ?? '';

    // Assume $conn is your database connection and $family_id is the ID of the family being updated
    $response = updateFamilyProfile($conn, $family_id, $family_name,  $family_goal, $family_description, $family_profile_pic, $old_family_profile_pic);

    // Redirect or show success/error message
    if (strpos($response, 'updated') !== false) {
        header("Location:  ". $_SERVER['PHP_SELF']. "?success=" . urlencode($response));
    } else {
        header("Location: ". $_SERVER['PHP_SELF']."?error=" . urlencode($response));
    }
    exit();
}


// family role

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    // Retrieve the form data
    $family_role = $_POST['family_role'] ?? '';

    // Call the function to update family position
    $response = updateFamilyPosition($conn, $user_id, $family_role);

    // Redirect or display success/error message
    if (strpos($response, 'updated successfully') !== false) {
        header("Location: family.php?success=" . urlencode($response));
    } else {
        header("Location: family.php?error=" . urlencode($response));
    }
    exit();
}

// Check if category ID is passed for deletion
if (isset($_GET['delete_id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete_id']);

    // Call function to delete category
    $result = deleteFinancialCategory($conn, $id, $family_id);

    // Provide success or error feedback
    if ($result) {
        header("Location: " . $_SERVER['PHP_SELF']);
    } else {
        header("Location: " . $_SERVER['PHP_SELF']);
    }
    exit();
}


  // Handle add transaction form submission
  if (isset($_POST['add_transaction'])) {
      $transaction_user = $conn->real_escape_string($_POST['user']);
      $amount = !empty($_POST['amount']) ? $conn->real_escape_string($_POST['amount']) : '0.00';
      $time = !empty($_POST['time']) ? $conn->real_escape_string($_POST['time']) : date('Y/m/d H:i:s');
      $category = $conn->real_escape_string($_POST['category']);
      $note = $_POST['note'] ?? '';

      if (addTransaction($conn, $family_id, $category, $transaction_user, $amount, $time, $note)) {
        echo "<div class='alert alert-success'>Transaction added successfully!</div>";
          header("Location: " . $_SERVER['PHP_SELF']);
          exit();
      }
      exit();
  }

  // Handle add budget form submission
  if (isset($_POST['add_budget'])) {
      $budget_user = $conn->real_escape_string($_POST['user']);
      $amount = !empty($_POST['amount']) ? $conn->real_escape_string($_POST['amount']) : '0.00';
      $month_input = $_POST['month'] ?? '';
      $month = $month_input ? date('F', strtotime($month_input)) : date('F');
      $category = $conn->real_escape_string($_POST['category']);
      $cat_note = $_POST['note'] ?? '';

      if (addBudget($conn, $family_id, $category, $budget_user, $amount, $month, $cat_note)) {
          echo "Budget added successfully!";
          header("Location: " . $_SERVER['PHP_SELF']);
          exit();
      }
      exit();
  }

  // Handle add resource form submission
  if (isset($_POST['add_resource'])) {
      $resource_user = $conn->real_escape_string($_POST['user']);
      $item_name = !empty($_POST['name']) ? $conn->real_escape_string($_POST['name']) : 'unknown';
      $amount = !empty($_POST['amount']) ? $conn->real_escape_string($_POST['amount']) : '0.00';
      $time = !empty($_POST['time']) ? $conn->real_escape_string($_POST['time']) : date('Y/m/d H:i:s');
      $category = $conn->real_escape_string($_POST['category']);
      $cat_note = $_POST['note'] ?? '';

      if (addResource($conn, $family_id, $category, $resource_user, $item_name, $amount, $time, $cat_note)) {
          echo "Resource added successfully!";
          header("Location: " . $_SERVER['PHP_SELF']);
          exit();
      }
      exit();
  }




  // Handle update resource form submission
  if (isset($_POST['update_resources'])) {
      $resource_id = $_POST['resource'];
      $resource = getResourceDetails($conn, $resource_id);

      $name = $_POST['name'] ?? $resource['item_name'];
      $description = $_POST['description'] ?? $resource['item_description'];
      $notes = $_POST['notes'] ?? $resource['personal_notes'];
      $price = $_POST['price'] ?? $resource['item_price'];
      $cashflow = $_POST['cashflow'] ?? $resource['cashflow'];

      if (updateResource($conn, $resource_id, $name, $description, $notes, $price, $cashflow)) {
          echo "Resource updated successfully!";
          header("Location: " . $_SERVER['PHP_SELF']);
          exit();
      }
      exit();
  }




  if (isset($_POST['add_dish'])) {
    $day = !empty($_POST['day']) ? $conn->real_escape_string($_POST['day']) : null;
    $time = !empty($_POST['time']) ? $conn->real_escape_string($_POST['time']) : null;
    $dish_name = !empty($_POST['dish_name']) ? $conn->real_escape_string($_POST['dish_name']) : '';

    // Dish details array (without encoding, since addDish will encode it)
    $dish_details = [
        'dish_category' => '',
        'preparation_process' => '',
        'dietary_restrictions' => '',
        'ingredients_used' => [
            'name' => '',
            'quantity' => ''
        ],
        'day' => $day,
        'time' => $time
    ];

    // Attempt to add the dish and check the result
    if (addDish($conn, $family_id, $dish_name, $day, $time, $dish_details)) {
        // Redirect on success
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    exit(); 
}




// Example usage of addIngredient function
if (isset($_POST['add_ingredient'])) {
  if (addIngredient($conn, $family_id, $_POST['name'], $_POST['price'], $_POST['quantity'])) {
      header("Location: " . $_SERVER['PHP_SELF']);
      exit();
  } else {
      echo "Error adding ingredient.";
  }
  exit();
}

// Example usage of addDiaryEntry function
if (isset($_POST['add_entry']) && isset($_POST['entry'])) {
  if (addDiaryEntry($conn, $family_id, $_POST['title'], $_POST['entry'], $_POST['date'])) {
      header("Location: " . $_SERVER['PHP_SELF']);
      exit();
  } else {
      echo "Error adding diary entry.";
  }
  exit();
}

// Example usage of addFavoriteWebsite function
if (isset($_POST['add_website']) && isset($_POST['url'])) {
  if (addFavoriteWebsite($conn, $family_id, $_POST['name'], $_POST['url'], $_POST['description'], $_POST['category'])) {
      header("Location: " . $_SERVER['PHP_SELF']);
      exit();
  } else {
      echo "Error adding favorite website.";
  }
  exit();
}



// Handle task submission form
if (isset($_POST['submit'])) {
  $task_name = $_POST['name'] ?? null;
  $task_description = $_POST['description'] ?? null;
  $task_deadline = $_POST['deadline'] ?? null;
  $task_duration = $_POST['duration'] ?? null;
  $task_assigner = $_POST['assigner'] ?? null;
  $file_upload_path = null;

  // Handle file upload
  if (isset($_FILES['file']['name']) && !empty($_FILES['file']['name'])) {
      $file_name = $_FILES['file']['name'];
      $tmp_name = $_FILES['file']['tmp_name'];
      $error = $_FILES['file']['error'];

      if ($error === 0) {
          $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
          $allowed_exts = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];

          if (in_array($file_ext, $allowed_exts)) {
              $new_file_name = uniqid('', true) . '.' . $file_ext . '-' . $family_id;
              $file_upload_path = 'plugins/tasks/file/' . $new_file_name;

              if (!move_uploaded_file($tmp_name, $file_upload_path)) {
                  echo "Error moving uploaded file.";
              }
          } else {
              echo "Invalid file type.";
              exit();
          }
      } else {
          echo "File upload error!";
          exit();
      }
      exit();
  }

  // Use existing addTask function to add task
  if (addTask($conn, $family_id, $task_name, $task_description, $task_deadline, $task_duration,  $task_assigner, $file_upload_path)) {
      echo "Task added successfully!";
      header("Location: " . $_SERVER['PHP_SELF']);
      exit();
  } else {
      echo "Error adding task.";
  }
  exit();
}





// for  tasks  No
$query = "SELECT count(*) as number_tasks FROM tasks WHERE task_status = 'PENDING' and (task_assigner = $family_id OR task_assignee = $family_id)";

$resultstasks_no = $conn->query($query);
if ($resultstasks_no -> num_rows > 0 ){
    $row = $resultstasks_no -> fetch_assoc();
    $tasks_no = $row['number_tasks'];

}


// for  tasks 
$resultstasks = getTasksByStatus($conn, $family_id, 'PENDING');


$resultsincome = getFinancialData($conn, 'transactions', $family_id, 'income');
$resultsexpense = getFinancialData($conn, 'transactions', $family_id, 'expense');
$resultsassets = getFinancialData($conn, 'resources', $family_id, 'assets');
$resultsliabilities = getFinancialData($conn, 'resources', $family_id, 'liabilities');



// for completed  tasks 

$resultstasks_completed = getTasksByStatus($conn, $family_id, 'COMPLETED');

  }

// Display flash message
displaySessionMessage();



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


<style>
    
/* Root Theme Colors */
:root {
    --primary-color: #007bff; /* Blue */
    --secondary-color: #6c757d; /* Gray */
    --background-color: #f8f9fa; /* Light Background */
    --accent-color: #28a745; /* Green */
    --hover-color: #0056b3; /* Darker Blue */
    --text-color: #343a40; /* Dark Gray */
}

/* General Body Styles */
body {
    font-family: 'Roboto', sans-serif;
    background-color: var(--background-color);
    color: var(--text-color);
    margin: 0;
    padding: 0;
    overflow-x: hidden;
}

/* Navbar */
.navbar {
    background-color: var(--primary-color);
    color: white;
    padding: 1rem;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    position: sticky;
    top: 0;
    z-index: 1000;
    animation: slideIn 1s ease-out;
}

.navbar a {
    color: white;
    text-decoration: none;
    margin: 0 1rem;
}

.navbar a:hover {
    color: var(--hover-color);
    transition: color 0.3s;
}

/* Buttons */
.btn {
    background-color: var(--accent-color);
    border: none;
    color: white;
    padding: 0.8rem 1.5rem;
    border-radius: 5px;
    font-size: 1rem;
    cursor: pointer;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    transition: transform 0.2s, background-color 0.3s;
}

.btn:hover {
    background-color: var(--hover-color);
    transform: translateY(-3px);
}

/* Cards */
.card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    padding: 1.5rem;
    margin: 1rem 0;
    animation: fadeIn 1s ease-in-out;
}

.card h3 {
    color: var(--primary-color);
}

/* Animations */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideIn {
    from {
        transform: translateY(-100%);
    }
    to {
        transform: translateY(0);
    }
}

/* Footer */
.footer {
    background-color: var(--secondary-color);
    color: white;
    text-align: center;
    padding: 1rem;
    margin-top: 2rem;
    animation: fadeIn 1.5s;
}

/* Content Sections */
.section {
    padding: 2rem;
    margin: 1rem 0;
}

.section h2 {
    color: var(--primary-color);
    animation: fadeIn 0.5s ease-out;
}

/* Input Fields */
input, textarea {
    width: 100%;
    padding: 0.8rem;
    margin: 1rem 0;
    border: 1px solid var(--secondary-color);
    border-radius: 5px;
    transition: border 0.3s;
}

input:focus, textarea:focus {
    border-color: var(--primary-color);
    outline: none;
}

/* Media Queries for Responsiveness */
@media (max-width: 768px) {
    .navbar a {
        margin: 0 0.5rem;
    }
    .btn {
        width: 100%;
        padding: 1rem;
    }
}

</style>
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
        <header class="topbar bg-warning" data-navbarbg="skin5">
            <nav class="navbar top-navbar navbar-expand-md navbar-dark">
                <div class="navbar-header" data-logobg="skin6">
                  
                    <!-- ============================================================== -->
                    <!-- toggle and nav items -->
                    <!-- ============================================================== -->

                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    
                </div>
                <!-- ============================================================== -->
                <!-- End Logo -->
                <!-- ============================================================== -->
                <div class="navbar-collapse collapse" id="navbarSupportedContent" >
                   
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
                            <img src="plugins/images/users/<?php echo $user_profile_pic?>" alt="user-img" width="45" height="45"
                            class="img-circle"><span class="text-white font-medium"><?php echo $username ?></span>
                            </a>

                            <ul class="dropdown-menu dropdown-menu-end">
                              <?php if(isset($user_id)) {?>
                              <div id="left" class="d-flex justify-content-center">
                                    <a class="btn profile-pic-enlarged" href="#">
                                  <img src="plugins/images/users/<?php echo $user_profile_pic?>" alt="user-img" width="60" height="60"
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

        
        <div class="page-breadcrumb" style="background-color:brown !important;">
    
    <?php if(isset($family_id)): ?>
    
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <!-- Left Section: Financials Button -->
            <div class="mb-2">
                <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample" aria-controls="offcanvasExample">
                    Financials
                </button>
            </div>
            
            <!-- Right Section: Home Icon -->
            <div class="mb-2">
                <a href="index.php">
                    <img height="50" width="50" src="plugins/images/exit.jpg" alt="Go to home page">
                </a>
            </div>
        </div>
        
    <?php else: ?>
    
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <!-- Left Section: Stocks Button with Locked Icon -->
            <div class="mb-2">
                <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample" aria-controls="offcanvasExample">
                    Stocks <img height="20" width="20" src="plugins/images/locked.png">
                </button>
            </div>
            
            <!-- Right Section: Home Icon -->
            <div class="mb-2">
                <a href="index.php">
                    <img height="50" width="50" src="plugins/images/exit.jpg" alt="Go to home page">
                </a>
            </div>
        </div>
        
    <?php endif; ?>
    
</div>

                
        <?php if(isset($family_id)) {?>

            


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
                    <button class="nav-link" id="pills-famprofile-tab" data-bs-toggle="pill" data-bs-target="#pills-famprofile" type="button" role="tab" aria-controls="pills-famprofile" aria-selected="false">Family Profile</button>
                </li>
                </ul>

        
<div class="tab-content" id="pills-tabContent">
  <div class="tab-pane fade show active" id="pills-chat" role="tabpanel" aria-labelledby="pills-chat-tab" tabindex="0">
    
                      <h3> Chats </h3>

                      <!--Start of Tawk.to Script-->
                    <script type="text/javascript">
                    var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
                    (function(){
                    var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
                    s1.async=true;
                    s1.src='https://embed.tawk.to/67362d6b4304e3196ae29d02/1iclq2vo2';
                    s1.charset='UTF-8';
                    s1.setAttribute('crossorigin','*');
                    s0.parentNode.insertBefore(s1,s0);
                    })();
                    </script>
                    <!--End of Tawk.to Script--></th>
            </div>

            <div class="tab-pane fade" id="pills-menu" role="tabpanel" aria-labelledby="pills-menu-tab" tabindex="0">
    <h3 class="text-primary text-center">Menu</h3>

    <table class="table table-bordered border-primary">
    <thead>
        <tr>
            <th scope="col">Days</th>
            <th scope="col">Morning</th>
            <th scope="col">Afternoon</th>
            <th scope="col">Evening</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // Days of the week and meal times
        $days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
        $times = ["morning", "afternoon", "evening"];

        foreach ($days as $day): ?>
            <tr>
                <th scope="row"><?= htmlspecialchars($day) ?></th>
                <?php foreach ($times as $time): ?>
                    <td class="text-center">
                        <?php displayDishes($conn, $family_id, $day, $time); ?>
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
                        <input type="hidden" name="day" id="add-modal-day">
                        <input type="hidden" name="time" id="add-modal-time">
                        <input type="hidden" name="user" value="<?php echo $family_id; ?>">

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
                <input type="hidden" name="user" id="user" value="<?php echo $family_id; ?>">

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
                                              while ($row = $resultsTransactions->fetch_assoc()) {
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
                                              $resultsTransactions->data_seek(0); 
                                              while ($row = $resultsTransactions->fetch_assoc()) {
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
                            <?php while ($row = $resultsResources->fetch_assoc()) { ?>
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
                                  <?php echo $row['item_description']; ?>
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

                                              while ($row = $resultsBudget->fetch_assoc()) {
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
                                $resultsBudget->data_seek(0);
                                ?>

                              <!-- This Month Tab -->
                              <div class="tab-pane fade show active" id="thisbudgetmonth" role="tabpanel" aria-labelledby="nav-thisbudgetmonth-tab">
                                  <div class="table-responsive">
                                      <h1 class="d-flex justify-content-center" >This month's budget</h1>
                                          

                                      <div id="budgets">
                                                          
                                      <?php 
                                              $current_month = date('F');

                                              while ($row = $resultsBudget->fetch_assoc()) {
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

         <!-- Transactions Modal -->
<div class="modal fade" id="transactions-modal" tabindex="-1" aria-labelledby="transactions-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="transactions-modal-title">Transactions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Tab Navigation -->
                <div class="nav" id="nav-tab" role="tablist">
                    <button class="nav-item btn-warning my-2 rounded text-white active" id="nav-expense-tab" data-bs-toggle="tab" data-bs-target="#add_expense" type="button" role="tab" aria-controls="add_expense" aria-selected="true">Expense</button>
                    <button class="nav-item btn-warning my-2 rounded text-white" id="nav-income-tab" data-bs-toggle="tab" data-bs-target="#add_income" type="button" role="tab" aria-controls="add_income" aria-selected="false">Income</button>
                </div>

                <hr>

                <!-- Tab Content -->
                <div class="tab-content" id="nav-tabContent">
                    <!-- Expense Form -->
                    <div class="tab-pane fade show active" id="add_expense" role="tabpanel" aria-labelledby="nav-expense-tab">
                        <?php echo renderTransactionForm('expense', $user_id, $conn); ?>
                    </div>
                    
                    <!-- Income Form -->
                    <div class="tab-pane fade" id="add_income" role="tabpanel" aria-labelledby="nav-income-tab">
                        <?php echo renderTransactionForm('income', $user_id, $conn); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Resources Modal -->
<div class="modal fade" id="resources-modal" tabindex="-1" aria-labelledby="resources-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resources-modal-title">Resources</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Tab Navigation -->
                <div class="nav" id="nav-tab" role="tablist">
                    <button class="nav-item btn-warning my-2 rounded text-white" id="nav-asset-tab" data-bs-toggle="tab" data-bs-target="#asset" type="button" role="tab" aria-controls="asset" aria-selected="true">Assets</button>
                    <button class="nav-item btn-warning my-2 rounded text-white" id="nav-liability-tab" data-bs-toggle="tab" data-bs-target="#liability" type="button" role="tab" aria-controls="liability" aria-selected="false">Liabilities</button>
                </div>

                <hr>

                <!-- Tab Content -->
                <div class="tab-content" id="nav-tabContent">
                    <!-- Asset Form -->
                    <div class="tab-pane fade show active" id="asset" role="tabpanel" aria-labelledby="nav-asset-tab">
                        <?php echo renderResourceForm('asset', $user_id, $conn); ?>
                    </div>
                    
                    <!-- Liability Form -->
                    <div class="tab-pane fade" id="liability" role="tabpanel" aria-labelledby="nav-liability-tab">
                        <?php echo renderResourceForm('liability', $user_id, $conn); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Budget Modal -->
<div class="modal fade" id="budget-modal" tabindex="-1" aria-labelledby="budget-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="budget-modal-title">Budgets</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="post" style="width: 560px;">
                    <input type="hidden" name="family_id" value="<?php echo $family_id; ?>">

                    <!-- Amount Input -->
                    <div class="col-8">
                        <div class="input-group">
                            <span class="input-group-text"><label for="amount">Enter Amount (GH₵)</label></span>
                            <input type="number" name="amount" id="amount" required>
                        </div>
                    </div>

                    <br>

                    <!-- Category Select -->
                    <div class="col-8">
                        <div class="input-group">
                            <span class="input-group-text"><label for="category">Select Category</label></span>
                            <select id="category" class="form-select" name="category" required>
                                <option value="">-- Select Category --</option>
                                <?php
                                // Fetch child categories of type "expense" for this family
                                $expenseCategories = fetchExpenseCategories($conn, $family_id);
                                foreach ($expenseCategories as $category) {
                                    echo "<option value=\"{$category['category_id']}\">{$category['category_name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <br>

                    <!-- Month Input -->
                    <div class="col-8">
                        <div class="input-group">
                            <span class="input-group-text"><label for="month">Month</label></span>
                            <input type="month" name="month" id="month" required>
                        </div>
                    </div>

                    <br>

                    <!-- Note Input -->
                    <div class="col-8">
                        <div class="input-group">
                            <span class="input-group-text"><label for="note">Note</label></span>
                            <textarea name="note" id="note"></textarea>
                        </div>
                    </div>

                    <br>

                    <!-- Submit Button -->
                    <div class="d-flex justify-content-center">
                        <input type="submit" class="btn btn-primary" id="add_budget" name="add_budget" value="Add Budget">
                    </div>
                </form>
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
        <input type="hidden" name="assigner" id="assigner" value="<?php echo $family_id; ?>">

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
  <div class="tab-pane fade" id="pills-famprofile" role="tabpanel" aria-labelledby="pills-famprofile-tab" tabindex="0">
    <div class="container-fluid">
        <div class="row">
            <!-- Family Profile Picture and Name Section -->
            <div class="col-lg-4 col-xlg-3 col-md-12">
                <div class="white-box">
                    <div class="user-bg">
                        <img width="100%" alt="family profile" src="plugins/images/family/<?php echo $family_profile_pic; ?>">
                        <div class="overlay-box">
                            <div class="user-content">
                                <a href="javascript:void(0)">
                                    <img src="<?php echo $family_profile_pic; ?>" class="thumb-lg img-circle" alt="family profile picture">
                                </a>
                                <h4 class="text-white mt-2"><?php echo $family_name; ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="user-btm-box mt-5 d-md-flex justify-content-between">
                        <div class="col-md-6 text-center">
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#financial-categories-modal">Manage Financial Categories</button>
                        </div>
                        <div class="col-md-6 text-center">
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#family-settings-modal">Family Settings</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Update Family Profile Form -->
            <div class="col-lg-8 col-xlg-9 col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form class="form-horizontal form-material" action="" method="POST" enctype="multipart/form-data">
                            <h4 class="display-4 fs-1">Edit Family Profile</h4><br>

                            <!-- Error/Success Messages -->
                            <?php if(isset($_GET['error'])) { ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $_GET['error']; ?>
                            </div>
                            <?php } ?>

                            <?php if(isset($_GET['success'])) { ?>
                            <div class="alert alert-success" role="alert">
                                <?php echo $_GET['success']; ?>
                            </div>
                            <?php } ?>

                            <!-- Family Name -->
                            <div class="form-group mb-4">
                                <label class="col-md-12 p-0">Family Name</label>
                                <div class="col-md-12 border-bottom p-0">
                                    <input type="text" name="family_name" placeholder="<?php echo $family_name; ?>" class="form-control p-0 border-0">
                                </div>
                            </div>

                            <!-- Financial Goal -->
                            <div class="form-group mb-4">
                                <label class="col-md-12 p-0">Family Financial Goal</label>
                                <div class="col-md-12 border-bottom p-0">
                                    <input type="number" name="family_goal" placeholder="<?php echo $goal; ?> GH₵" class="form-control p-0 border-0">
                                </div>
                            </div>
                            <!-- Family Description -->
                            <div class="form-group mb-4">
                                <label class="col-md-12 p-0">Family Description</label>
                                <div class="col-md-12 border-bottom p-0">
                                    <input type="text" name="family_description" placeholder="<?php echo $family_description; ?> " class="form-control p-0 border-0">
                                </div>
                            </div>

                            <!-- Profile Picture Upload -->
                            <div class="mb-3">
                                <label class="form-label">Family Profile Picture</label>
                                <input type="file" class="form-control" name="family_profile_pic">
                                <input type="hidden" name="old_family_profile_pic" value="<?php echo $family_profile_pic; ?>">
                            </div>

                            <!-- Submit Button -->
                            <div class="form-group mb-4">
                                <div class="col-sm-12">
                                    <input class="btn btn-success" type="submit" name ="update_family" >
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

   <!-- Financial Categories Modal -->
<div class="modal fade" id="financial-categories-modal" tabindex="-1" aria-labelledby="financialCategoriesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="financialCategoriesLabel">Manage Financial Categories</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            <form action="" method="POST">
    <!-- Category Level (Parent or Child) -->
    <div class="form-group mb-4">
        <label for="category-level">Category Level</label><br>
        <input type="radio" name="category_level" value="parent" id="category-parent" checked onclick="toggleParentDropdown()">
        <label for="category-parent">Parent</label>
        <input type="radio" name="category_level" value="child" id="category-child" onclick="toggleParentDropdown()">
        <label for="category-child">Child</label>
    </div>

    <!-- Category Name -->
    <div class="form-group mb-4">
        <label for="category-name">Category Name</label>
        <input type="text" name="category_name" id="category-name" class="form-control" required>
    </div>

    <!-- Category Type -->
    <div class="form-group mb-4">
        <label for="category-type">Category Type</label>
        <select name="category_type" id="category-type" class="form-control" onchange="filterParentCategories()">
            <option value="asset">Asset</option>
            <option value="liability">Liability</option>
            <option value="income">Income</option>
            <option value="expense">Expense</option>
        </select>
    </div>

    <!-- Parent Category Selection (Visible only if 'Child' is selected) -->
    <div class="form-group mb-4" id="parent-category-group" style="display: none;">
        <label for="parent-category">Parent Category</label>
        <select name="parent_category" id="parent-category" class="form-control">
            <option value="">None</option>
            <?php
            // PHP to load initial options
            foreach($financial_categories as $financial_category) {
                echo "<option value='{$financial_category['category_id']}' data-category='{$financial_category['category']}'>
                        {$financial_category['category_name']}
                      </option>";
            }
            ?>
        </select>
    </div>

    <!-- Add Category Button -->
    <input type="submit" class="btn btn-primary" name="add_category" value="Add Category">
</form>

<script>
// Show/hide the Parent Category dropdown based on selected category level
function toggleParentDropdown() {
    const isChild = document.getElementById('category-child').checked;
    document.getElementById('parent-category-group').style.display = isChild ? 'block' : 'none';
}

// Filter parent categories based on selected category type
function filterParentCategories() {
    const selectedType = document.getElementById('category-type').value;
    const options = document.getElementById('parent-category').options;

    for (let i = 0; i < options.length; i++) {
        const option = options[i];
        if (option.getAttribute('data-category') === selectedType || option.value === '') {
            option.style.display = 'block';
        } else {
            option.style.display = 'none';
        }
    }
}
</script>



    <!-- Family Settings Modal -->
    <div class="modal fade" id="family-settings-modal" tabindex="-1" aria-labelledby="familySettingsLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="familySettingsLabel">Family Settings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="POST">
                        <!-- Role in Family -->
                        <div class="form-group mb-4">
                            <label for="family-role">Define Your Role in the Family</label>
                            <input type="text" name="family_role" id="family-role" placeholder="<?php if(isset($family_position)){echo $family_position;} ?>" class="form-control">
                        </div>
                        <!-- Save Settings Button -->
                        <input type="submit" class="btn btn-primary" name ="save_settings">
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


        </div>      
    </div>
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
                        <div class="income" id="income">
                            <?php generateSection($resultsincome, 'Income', 'text-success'); ?>
                        </div>

                        <div class="expense" id="expense">
                            <?php generateSection($resultsexpense, 'Expense', 'text-danger'); ?>
                        </div>



                        </div>
                       

                        <div id="right"  >
                        <div class="progress-container">
                          <header class="bg-secondary">
                              <h2 class="text-white">Financial Goal</h2>
                              <h4 class="text-white d-flex justify-content-end"><?php echo $goal; ?> GH₵</h4>
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
                      
                      
                      
                      
                      <div class="d-flex justify-content-between">
    <div id="left">
        <!-- Assets - Non-Real Estates -->
        <div class="assets" id="assets">
            <?php generateSection($resultsassets, "Assets", "text-success", "non-real estates"); ?>
        </div>

        <!-- Assets - Real Estates -->
        <div class="assets" id="assets-real-estate">
            <?php generateSection($resultsassets, "Real Estate Assets", "text-danger", "real estates"); ?>
        </div>
    </div>

    <div id="right">
        <!-- Liabilities - Non-Real Estates -->
        <div class="liabilities" id="liabilities">
            <?php generateSection($resultsliabilities, "Liabilities", "text-danger", "non-real estates"); ?>
        </div>

        <!-- Liabilities - Real Estates -->
        <div class="liabilities" id="liabilities-real-estate">
            <?php generateSection($resultsliabilities, "Real Estate Liabilities", "text-danger", "real estates"); ?>
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
        <input type="hidden" name="assigner" id="assigner" value = "<?php echo $family_id; ?>">

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
            <footer class="footer text-center bg-dark text-white py-3 w-100 position-relative bottom-0 d-sm-block d-lg-none"> 2024 © Task manager brought to you by JopalBusinessCenter
                    <p>Theme was reproduced from <a
                    href="https://www.wrappixel.com/">wrappixel.com</a> with permission from the author.</p>

            </footer>

            <footer class="footer text-center bg-dark text-white py-3 w-100 position-absolute bottom-0 d-sm-none d-lg-block"> 2024 © Task manager brought to you by JopalBusinessCenter
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
                                              


