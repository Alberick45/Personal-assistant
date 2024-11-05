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
    VALUES ('$name', '$url', '$website_description', '$website_category', $website_user)";
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
                            Favourite Websites
                            </button>
                        </div>
                        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
                            <div class="d-md-flex">
                                <ol class="breadcrumb ms-auto">
                                    <li><a type="button" class="btn btn-danger  d-none d-md-block pull-right ms-3 hidden-xs hidden-sm waves-effect waves-light text-white" data-bs-toggle="modal" data-bs-target="#diary-modal" >Diary Entry</a></li>
                                </ol>
                                </div>
                        </div>
                    </div>
                    <?php }else{ ?>
                    <div class="row align-items-center">
                        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                            <!-- Button to toggle offcanvas -->
                            <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample" aria-controls="offcanvasExample">
                              Favourite Websites <span ><img height="20" width="20" src="plugins/images/locked.png" ></span>
                            </button>
                        </div>
                        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
                            <div class="d-md-flex">
                                <ol class="breadcrumb ms-auto">
                                    <li><a type="button" class="btn btn-danger  d-none d-md-block pull-right ms-3 hidden-xs hidden-sm waves-effect waves-light text-white" data-bs-toggle="modal" data-bs-target="#diary-modal" >Diary Entry<span ><img height="20" width="20" src="plugins/images/locked.png" ></span></a></li>
                                </ol>
                                </div>
                        </div>
                    </div>
                    <?php }?>
                    <!-- /.col-lg-12 -->
                </div>
                
        <?php if(isset($user_id)) {?>

        <div id="main_page" class="mt-5 ms-5 h-100 w-75"  >

            <h2 class="text-center">Apps</h2>

            <div id="apps" class=" me-5 d-flex justify-content-between">
                <div id="finances" class="border border-1 me-5 rounded p-4 w-25 d-flex justify-content-start">
                    <h3>
                        <a href="finance.php" class="d-flex align-items-center position-relative text-center text-decoration-none">
                            <img src="plugins/images/finance.jpg" alt="finance" width="60" height="60" class="rounded-circle">
                            <h4 class="mt-2">Finance</h4>
                        </a>
                        
                    </h3>
                </div>

                    <br>

                <div id="tasks" class="border border-1 rounded p-4 w-25  me-5">
                    <h3>
                        <a href="task.php" class="d-flex align-items-center position-relative text-decoration-none">
                            <img src="plugins/images/tasks.jpg" alt="task" width="60" height="60" class="me-3 rounded-circle">

                            <h4 class="mb-0">
                                Tasks
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    <?php echo $myday_no ?? 0; ?>
                                    <span class="visually-hidden">Tasks for today</span>
                                </span>
                            </h4>
                        </a>
                        
                    </h3>
                </div>

                       


                <div id="diet" class="border border-1 me-5  rounded p-4 w-25">
                    <h3>
                        <a href="diet.php" class="text-center d-flex align-items-center position-relative text-decoration-none">
                            <img src="plugins/images/diet.jpg" alt="diet" width="60" height="60" class="rounded-circle">
                            <h4 class="mt-2">Diet</h4>
                        </a>

                    </h3>
                </div>
                            <br>

                
                <div id="family" class="border border-1 rounded  p-4 w-25 ">
                    <h3>
                        <a href="family.php" class="text-center d-flex align-items-center position-relative text-decoration-none">
                            <img src="plugins/images/family.jpg" alt="family" width="60" height="60" class="rounded-circle">
                            <h4 class="mt-2">Family</h4>
                        </a>

                    </h3>
                </div>

                

            </div>
            
            
                            
            



            <!-- Offcanvas Sidebar -->
                  <div class="offcanvas offcanvas-start w-25" style="background-color:bisque" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel">
                    <div class="offcanvas-header">
                      <h5 class="offcanvas-title" id="offcanvasExampleLabel">Favourite Websites</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body w-100">
                      <div class="d-flex justify-content-between " >
                        
                      
                      </div>
                      
                      
                      
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
                                  echo "<h2 style='color: blue; margin-top: 15px;'>" . htmlspecialchars($current_website) . "</h2>";
                                  
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


              
                <!-- this is the Diary modal -->
                <div class="modal fade" id="diary-modal" tabindex="-1" aria-labelledby="diary-modal-title" aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="diary-modal-title">Diary</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div id="diary_header" class="d-flex justify-content-between">
                    <?php
                    // Get today's date in the format 'Y-m-d'
                    $this_day = date('Y-m-d');
                    ?>
                    <div class="d-flex justify-content-between" style="width: 100%;">
                        <h3 class="d-flex justify-content-center text-center text-primary"><?php echo date('D F, Y'); ?></h3>
                        <hr>
                    </div>
                </div>

                <div class="row my-3 px-5 py-3" style="height: 40vh; overflow-y:scroll;">
                    <?php
                    // Fetch entries from the database
                    while ($row = $resultsfetchdiary->fetch_assoc()) {
                        // Check if the entry date matches today's date in 'Y-m-d' format
                        if ($row['entry_date'] === $this_day) { ?>
                            <div>
                                <div id="entry.<?php echo $row['id']; ?>">
                                    <?php echo htmlspecialchars($row['diary_title']); ?> - 
                                    <?php echo htmlspecialchars($row['diary_entry']); ?>
                                </div>
                                <hr>
                            </div>
                        <?php }
                    } ?>
                    <div class="h-4 d-flex justify-content-between">
                        <form action="" id="entryForm" method="POST" class="d-flex w-100 gap-3">
                            <input type="hidden" name="date" value="<?php echo $this_day; ?>">
                            <input type="hidden" name="add_entry" value="1"> <!-- Hidden input for the image submit -->
                            <input type="text" name="title" id="title" 
                                   class="border-0 border-bottom rounded-0 form-control" 
                                   placeholder="Entry title" 
                                   style="box-shadow: none !important; outline: none;" required>
                            <input type="text" name="entry" id="entry" 
                                   class="border-0 border-bottom rounded-0 form-control" 
                                   placeholder="Your thoughts" 
                                   style="box-shadow: none !important; outline: none;" required>
                            <img src="plugins/images/add.png" alt="Add Entry" 
                                 style="height: 40px; width: 40px; cursor: pointer; object-fit: contain;"
                                 onclick="document.getElementById('entryForm').submit();">
                        </form>
                    </div>
                </div>

                <br>
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
            <footer class="footer text-center bg-dark text-white py-3 w-100 position-absolute bottom-0 "> 2024 © Task manager brought to you by JopalBusinessCenter
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

                                            