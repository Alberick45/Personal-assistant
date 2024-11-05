<?php

require_once("plugins/actions/config.php");
session_start();

if(isset($_SESSION['user_id'])){


$user_id = $_SESSION['user_id'];


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




// for important tasks 
// $query = "SELECT count(*) as number_important FROM tasks WHERE task_status = 'PENDING' AND task_importance = 'Important' and task_assigner = $user_id";

// $resultsimportant= $conn->query($query);
// if ($resultsimportant -> num_rows > 0 ){
//     $row = $resultsimportant -> fetch_assoc();
//     $important_no = $row['number_important'];

// }
// for  tasks 
// $query = "SELECT count(*) as number_tasks FROM tasks WHERE task_assigner = $user_id";

// $resultstasks = $conn->query($query);
// if ($resultstasks -> num_rows > 0 ){
//     $row = $resultstasks -> fetch_assoc();
//     $tasks_no = $row['number_tasks'];

// }


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
                              financial Statement
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
                              financial Statement<span ><img height="20" width="20" src="plugins/images/locked.png" ></span>
                            </button>
                        </div>
                        
                    </div>
                    <?php }?>
                    <!-- /.col-lg-12 -->
                </div>
                
        <?php if(isset($user_id)) {?>


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


                        <!-- this is the transactions modal -->
        <div class="modal fade" id="transactions-modal" tabindex="-1" aria-labelledby="transactions-modal-title" aria-hidden="false">
                    <div class="modal-dialog modal-dialog-centered">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title" id="transactions-modal-title">Transactions</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">

                        <div class="nav" id="nav-tab" role="tablist">
                                        <button class="nav-item btn-warning my-2 rounded text-white active" id="nav-expense-tab" data-bs-toggle="tab" data-bs-target="#add_expense" type="button" role="tab" aria-controls="nav-expense" aria-selected="false"style="border:0px; margin-right:5vw;">Expense</button>
                                        <button class="nav-item btn-warning my-2 rounded text-white" id="nav-income-tab" data-bs-toggle="tab" data-bs-target="#add_income" type="button" role="tab" aria-controls="nav-income" aria-selected="false"style="border:0px; margin-right:5vw;">Income</button>
                                
                        </div>

                      <hr>
                                    <div class="tab-content" id="nav-tabContent">
                  
              
                  <!-- this is the expense section -->
                  <div class="tab-pane p-5 " id="add_expense" role="tabpanel" aria-labelledby="nav-expense-tab">
                    
                    <form action="" method="post" style="width: 560px;">
                          <input type="hidden" name="user" id="user" value = "<?php echo $user_id; ?>">

                          

                          <div class="col-8">
                                      <div class="input-group">
                                      <span class="input-group-text"><label for="amount">Enter Amount(GH)</label></span>
                                      <input type="number" name="amount" id="amount" required >
                                      </div>
                                  
                          </div>
                              <br>
                          <div class="col-8">
                              <div class="input-group">
                                        <span class="input-group-text">
                                            <label for="category">Select  Category</label>
                                        </span>
                                        <select id="category" class="form-select" name="category">
                                            <option value="">-- Select  Category --</option>
                                            <?php
                                            // Fetch all parent categories from the database
                                            $expenseCategoriesQuery = "SELECT category_id, category_name FROM financial_category WHERE category_type = 'Child' AND category_user_id = $user_id  AND category = 'expense'";
                                            $expenseCategories = $conn->query($expenseCategoriesQuery);

                                            while ($acategorys = $expenseCategories->fetch_assoc()) {
                                                echo "<option value=\"{$acategorys['category_id']}\">{$acategorys['category_name']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                              </div>


                          <br>
                          
                          <div class="col-8">
                                  <div class="input-group ">
                                  <span class="input-group-text"><label for="time">TIme</label></span>
                                  <input type="datetime-local" placeholder="Y/M/D H:m:s" name="time" id="time" >
                                  </div>
                          </div>
                          
                          
                          <br>
                          <div class="col-8">
                                  <div class="input-group ">
                                  <span class="input-group-text"><label for="note">Note</label></span>
                                  <textarea name="note" id="note"></textarea>
                                  </div>
                          </div>
                          <br>
                        
                          
                          
                          <input type="submit" class="d-flex justify-content-center btn btn-primary" id = "add_transaction" name="add_transaction">
                        
                    </form>


                        </div>
                  
              
                  <!-- this is the income section -->
                  <div class="tab-pane p-5 " id="add_income" role="tabpanel" aria-labelledby="nav-income-tab">
                    
                  <form action="" method="post" style="width: 560px;">
                          <input type="hidden" name="user" id="user" value = "<?php echo $user_id; ?>">

                      <div class="col-8">
                                  <div class="input-group ">
                                  <span class="input-group-text"><label for="amount">Enter Amount(GH)</label></span>
                                  <input type="number" name="amount" id="amount" required >
                                  </div>
                              
                      </div>
                          <br>
                      <div class="col-8">
                          <div class="input-group">
                                    <span class="input-group-text">
                                        <label for="category">Select  Category</label>
                                    </span>
                                    <select id="category" class="form-select" name="category" required>
                                        <option value="">-- Select  Category --</option>
                                        <?php
                                        // Fetch all parent categories from the database
                                        $incomeCategoriesQuery = "SELECT category_id, category_name FROM financial_category WHERE category_type = 'Child' AND category_user_id = $user_id  AND category = 'income'";
                                        $incomeCategories = $conn->query($incomeCategoriesQuery);

                                        while ($bcategorys = $incomeCategories->fetch_assoc()) {
                                            echo "<option value=\"{$bcategorys['category_id']}\">{$bcategorys['category_name']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                          </div>


                          <br>
                          
                          <div class="col-8">
                                  <div class="input-group ">
                                  <span class="input-group-text"><label for="time">TIme</label></span>
                                  <input type="datetime-local" placeholder="Y/M/D H:m:s" name="time" id="time" >
                                  </div>
                          </div>
                          
                          
                          <br>
                          <div class="col-8">
                                  <div class="input-group ">
                                  <span class="input-group-text"><label for="note">Note</label></span>
                                  <textarea name="note" id="note"></textarea>
                                  </div>
                          </div>
                          
                          
                          <br>
                          <input type="submit" class="d-flex justify-content-center btn btn-primary" id = "add_transaction" name="add_transaction">
                        
                    </form>
                        </div>

                  </div>
                        
                      </div>
                    </div>
                  </div>

        </div>
                <!-- this is the resources modal -->
        <div class="modal fade" id="resources-modal" tabindex="-1" aria-labelledby="resources-modal-title" aria-hidden="false">
                    <div class="modal-dialog modal-dialog-centered">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title" id="resources-modal-title">Resources</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">

                        <div class="nav" id="nav-tab" role="tablist">
                                        <button class="nav-item btn-warning my-2 rounded text-white" id="nav-asset-tab" data-bs-toggle="tab" data-bs-target="#asset" type="button" role="tab" aria-controls="nav-asset" aria-selected="false"style="border:0px; margin-right:5vw;">Assets</button>
                                        <button class="nav-item btn-warning my-2 rounded text-white" id="nav-liability-tab" data-bs-toggle="tab" data-bs-target="#liability" type="button" role="tab" aria-controls="nav-liability" aria-selected="false"style="border:0px; margin-right:5vw;">Liabilities</button>
                                      
                        </div>

                      <hr>
                                    <div class="tab-content" id="nav-tabContent">
                  
              
                  <!-- this is the asset section -->
                  <div class="tab-pane p-5 " id="asset" role="tabpanel" aria-labelledby="nav-asset-tab">
                    
                  <form action="" method="post" style="width: 560px;">
                          <input type="hidden" name="user" id="user" value = "<?php echo $user_id; ?>">

                          <div class="col-8">
                                    <div class="input-group">
                                    <span class="input-group-text"><label for="name">Enter Item Name</label></span>
                                    <input type="text" name="name" id="name" required >
                                    </div>
                                
                        </div>

                        <br>

                      <div class="col-8">
                                  <div class="input-group ">
                                  <span class="input-group-text"><label for="amount">Enter Amount(GH)</label></span>
                                  <input type="number" name="amount" id="amount" required >
                                  </div>
                              
                      </div>
                          <br>
                      <div class="col-8">
                          <div class="input-group">
                                    <span class="input-group-text">
                                        <label for="category">Select  Category</label>
                                    </span>
                                    <select id="category" class="form-select" name="category">
                                        <option value="">-- Select  Category --</option>
                                        <?php
                                        // Fetch all parent categories from the database
                                        $incomeCategoriesQuery = "SELECT category_id, category_name FROM financial_category WHERE category_type = 'Child' AND category_user_id = $user_id  AND category = 'asset'";
                                        $incomeCategories = $conn->query($incomeCategoriesQuery);

                                        while ($ccategorys = $incomeCategories->fetch_assoc()) {
                                            echo "<option value=\"{$ccategorys['category_id']}\">{$ccategorys['category_name']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                          </div>


                          <br>
                          
                          <div class="col-8">
                                  <div class="input-group ">
                                  <span class="input-group-text"><label for="time">TIme</label></span>
                                  <input type="datetime-local" placeholder="Y/M/D H:m:s" name="time" id="time" >
                                  </div>
                          </div>
                          
                          
                          <br>
                          <div class="col-8">
                                  <div class="input-group ">
                                  <span class="input-group-text"><label for="note">Note</label></span>
                                  <textarea name="note" id="note"></textarea>
                                  </div>
                          </div>
                          
                        <br>
                        <input type="submit" class="d-flex justify-content-center btn btn-primary" id = "add_resource" name="add_resource">
                        
                    </form>


                        </div>


                  <!-- this is the liability section -->
                  <div class="tab-pane p-5 " id="liability" role="tabpanel" aria-labelledby="nav-liability-tab">
                    
                  <form action="" method="post" style="width: 560px;">
                          <input type="hidden" name="user" id="user" value = "<?php echo $user_id; ?>">


                          <div class="col-8">
                                    <div class="input-group">
                                    <span class="input-group-text"><label for="name">Enter Item Name</label></span>
                                    <input type="text" name="name" id="name" required >
                                    </div>
                                
                        </div>

                        <br>
                        
                      <div class="col-8">
                                  <div class="input-group ">
                                  <span class="input-group-text"><label for="amount">Enter Amount(GH)</label></span>
                                  <input type="number" name="amount" id="amount" required >
                                  </div>
                              
                      </div>
                        <br> 
                      <div class="col-8">
                          <div class="input-group">
                                    <span class="input-group-text">
                                        <label for="category">Select  Category</label>
                                    </span>
                                    <select id="category" class="form-select" name="category">
                                        <option value="">-- Select  Category --</option>
                                        <?php
                                        // Fetch all parent categories from the database
                                        $liabilityCategoriesQuery = "SELECT category_id, category_name FROM financial_category WHERE category_type = 'Child' AND category_user_id = $user_id  AND category = 'liability'";
                                        $liabilityCategories = $conn->query($liabilityCategoriesQuery);

                                        while ($dcategorys = $liabilityCategories->fetch_assoc()) {
                                            echo "<option value=\"{$dcategorys['category_id']}\">{$dcategorys['category_name']}</option>";
                                        }
                                        ?>
                                    </select>
                            </div>
                          </div>


                          <br>
                          
                          <div class="col-8">
                                  <div class="input-group ">
                                  <span class="input-group-text"><label for="time">TIme</label></span>
                                  <input type="datetime-local" placeholder="Y/M/D H:m:s" name="time" id="time" >
                                  </div>
                          </div>
                          
                          
                          <br>
                          <div class="col-8">
                                  <div class="input-group ">
                                  <span class="input-group-text"><label for="note">Note</label></span>
                                  <textarea name="note" id="note"></textarea>
                                  </div>
                          </div>
                          
                        <br>
                        <input type="submit" class="d-flex justify-content-center btn btn-primary" id = "add_resource" name="add_resource">
                        
                    </form>

                        </div>
                        
              
                </div>
                        
                      </div>
                    </div>
                  </div>

        </div>


                <!-- this is the budget modal -->
        <div class="modal fade" id="budget-modal" tabindex="-1" aria-labelledby="budget-modal-title" aria-hidden="false">
                    <div class="modal-dialog modal-dialog-centered">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title" id="budget-modal-title">Budgets</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>



                        <div class="modal-body">

                        <form action="" method="post" style="width: 560px;">
                          <input type="hidden" name="user" id="user" value = "<?php echo $user_id; ?>">

                          

                          <div class="col-8">
                                      <div class="input-group">
                                      <span class="input-group-text"><label for="amount">Enter Amount(GH)</label></span>
                                      <input type="number" name="amount" id="amount" required >
                                      </div>
                                  
                          </div>
                              <br>
                          <div class="col-8">
                              <div class="input-group">
                                        <span class="input-group-text">
                                            <label for="category">Select  Category</label>
                                        </span>
                                        <select id="category" class="form-select" name="category">
                                            <option value="">-- Select  Category --</option>
                                            <?php
                                            // Fetch all parent categories from the database
                                            $expenseCategoriesQuery = "SELECT category_id, category_name FROM financial_category WHERE category_type = 'Child' AND category_user_id = $user_id  AND category = 'expense'";
                                            $expenseCategories = $conn->query($expenseCategoriesQuery);

                                            while ($acategorys = $expenseCategories->fetch_assoc()) {
                                                echo "<option value=\"{$acategorys['category_id']}\">{$acategorys['category_name']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                              </div>


                          <br>
                          
                          <div class="col-8">
                                  <div class="input-group ">
                                  <span class="input-group-text"><label for="month">Month</label></span>
                                  <input type="month" placeholder="M" name="month" id="month" >
                                  </div>
                          </div>
                          
                          
                          <br>
                          <div class="col-8">
                                  <div class="input-group ">
                                  <span class="input-group-text"><label for="note">Note</label></span>
                                  <textarea name="note" id="note"></textarea>
                                  </div>
                          </div>
                          <br>
                        
                          
                          
                          <input type="submit" class="d-flex justify-content-center btn btn-primary" id = "add_budget" name="add_budget">
                        
                    </form>

                  </div>
                        
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
            <footer class="footer text-center bg-dark text-white py-3 w-100 position-relative bottom-0 "> 2024 © Task manager brought to you by JopalBusinessCenter
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


<?php
                                                    