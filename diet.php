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




// for total income sum
$total_income =$passive_income + $only_income;

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
                            Stocks
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



            <!-- Offcanvas Sidebar -->
                  <div class="offcanvas offcanvas-start w-25" style="background-color:bisque" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel">
                    <div class="offcanvas-header">
                      <h5 class="offcanvas-title" id="offcanvasExampleLabel">Stocks</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body w-100">
                      <div class="d-flex justify-content-between " >
                        
                      
                      </div>
                      
                      
                      
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
                                              


