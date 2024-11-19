<?php
session_start();

require_once("plugins/actions/config.php");

if(isset($_SESSION['user_id'])){
// so here you see all you can change then side we have te picture and nderneath we could have pending tasks for next two days  alsoo on the profile pic at top right it is a dropdown with signup/login and dlogout so if logged in the sinup or signin and the signup and signin are modalss so tomorow also for updating tasks we will have tasks.php where update works for each stuff

global $conn;
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?You are not logged in");
    echo "You are not logged in";
    exit();
} else {
$user_id = $_SESSION['user_id'];

if(isset($_SESSION['message'])){
    echo $_SESSION['message'];
    unset($_SESSION['message']);
}
    

}
// For income  
$query = "SELECT * FROM financial_category WHERE category_user_id = $user_id AND category_type = 'Child' AND category = 'income'";
$resultsincome = $conn->query($query);

// For income  parent
$queryparent = "SELECT * FROM financial_category WHERE category_user_id = $user_id AND category_type = 'Parent' AND category = 'income' ";
$resultsincomeparent = $conn->query($queryparent);

// For income  parent
$queryparent = "SELECT * FROM financial_category WHERE category_user_id = $user_id AND category_type = 'Parent' AND category = 'income' ";
$resultsincomeparent = $conn->query($queryparent);


if (isset($_POST['submit_income_cat'])) {
    // Retrieve and sanitize input values
    $cat_user = $conn->real_escape_string($_POST['user']);
    $cat_type = $conn->real_escape_string($_POST['cat_type']);
    $cat_name = !empty($_POST['name']) ? $conn->real_escape_string($_POST['name']) : 'unknown';
    $category = !empty($_POST['category']) ? $conn->real_escape_string($_POST['category']) : 'income';

    // Check if a parent category is selected (only relevant for 'Child' type)
    $parent_category_id = !empty($_POST['parent_category']) ? intval($_POST['parent_category']) : 'NULL';

    // SQL query with parent_category_id handling
    $queryincome = "INSERT INTO financial_category 
                    (category_name, category, category_type, category_user_id, parent_category_id) 
                    VALUES ('$cat_name', '$category', '$cat_type', '$cat_user', $parent_category_id)";

    // Execute the query and check for errors
    if ($conn->query($queryincome)) {
        echo "Category added successfully!";
    } else {
        echo "Error: " . $conn->error;
    }
    // Redirect to the same page to prevent form resubmission on refresh
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}



// for  expense  
$query = "SELECT * FROM financial_category WHERE category = 'expense' and category_user_id = $user_id AND category_type = 'Child'";

$resultsexpense = $conn->query($query);

// For expense  parent
$queryparent = "SELECT * FROM financial_category WHERE category_user_id = $user_id AND category_type = 'Parent' AND category = 'expense' ";
$resultsexpenseparent = $conn->query($queryparent);


if (isset($_POST['submit_expense_cat'])) {
    // Retrieve and sanitize input values
    $cat_user = $conn->real_escape_string($_POST['user']);
    $cat_type = $conn->real_escape_string($_POST['cat_type']);
    $cat_name = !empty($_POST['name']) ? $conn->real_escape_string($_POST['name']) : 'unknown';
    $category = !empty($_POST['category']) ? $conn->real_escape_string($_POST['category']) : 'expense';

    // Check if a parent category is selected (only relevant for 'Child' type)
    $parent_category_id = !empty($_POST['parent_category']) ? intval($_POST['parent_category']) : 'NULL';

    // SQL query with parent_category_id handling
    $queryexpense = "INSERT INTO financial_category 
                    (category_name, category, category_type, category_user_id, parent_category_id) 
                    VALUES ('$cat_name', '$category', '$cat_type', '$cat_user', $parent_category_id)";

    // Execute the query and check for errors
    if ($conn->query($queryexpense)) {
        echo "Category added successfully!";
    } else {
        echo "Error: " . $conn->error;
    }
    // Redirect to the same page to prevent form resubmission on refresh
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
}


// for  asset  
$query = "SELECT * FROM financial_category WHERE category = 'asset' and category_user_id = $user_id AND category_type = 'Child'";

$resultsasset = $conn->query($query);
// For income  parent
$queryparent = "SELECT * FROM financial_category WHERE category_user_id = $user_id AND category_type = 'Parent' AND category = 'asset' ";
$resultsassetparent = $conn->query($queryparent);


if (isset($_POST['submit_asset_cat'])) {
    // Retrieve and sanitize input values
    $cat_user = $conn->real_escape_string($_POST['user']);
    $cat_type = $conn->real_escape_string($_POST['cat_type']);
    $cat_name = !empty($_POST['name']) ? $conn->real_escape_string($_POST['name']) : 'unknown';
    $category = !empty($_POST['category']) ? $conn->real_escape_string($_POST['category']) : 'asset';

    // Check if a parent category is selected (only relevant for 'Child' type)
    $parent_category_id = !empty($_POST['parent_category']) ? intval($_POST['parent_category']) : 'NULL';

    // SQL query with parent_category_id handling
    $queryasset = "INSERT INTO financial_category 
                    (category_name, category, category_type, category_user_id, parent_category_id) 
                    VALUES ('$cat_name', '$category', '$cat_type', '$cat_user', $parent_category_id)";

    // Execute the query and check for errors
    if ($conn->query($queryasset)) {
        echo "Category added successfully!";
    } else {
        echo "Error: " . $conn->error;
    }
    // Redirect to the same page to prevent form resubmission on refresh
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
}


// for  liability  
$query = "SELECT * FROM financial_category WHERE category = 'liability' and category_user_id = $user_id";

$resultsliability= $conn->query($query);

$query = "SELECT * FROM financial_category WHERE category_user_id = $user_id AND category_type = 'Child' AND category = 'liability'";
$resultsliability = $conn->query($query);

// For liability  parent
$queryparent = "SELECT * FROM financial_category WHERE category_user_id = $user_id AND category_type = 'Parent' AND category = 'liability' ";
$resultsliabilityparent = $conn->query($queryparent);


if (isset($_POST['submit_liability_cat'])) {
    // Retrieve and sanitize input values
    $cat_user = $conn->real_escape_string($_POST['user']);
    $cat_type = $conn->real_escape_string($_POST['cat_type']);
    $cat_name = !empty($_POST['name']) ? $conn->real_escape_string($_POST['name']) : 'unknown';
    $category = !empty($_POST['category']) ? $conn->real_escape_string($_POST['category']) : 'liability';

    // Check if a parent category is selected (only relevant for 'Child' type)
    $parent_category_id = !empty($_POST['parent_category']) ? intval($_POST['parent_category']) : 'NULL';

    // SQL query with parent_category_id handling
    $queryliability = "INSERT INTO financial_category 
                    (category_name, category, category_type, category_user_id, parent_category_id) 
                    VALUES ('$cat_name', '$category', '$cat_type', '$cat_user', $parent_category_id)";

    // Execute the query and check for errors
    if ($conn->query($queryliability)) {
        echo "Category added successfully!";
    } else {
        echo "Error: " . $conn->error;
    }
   // Redirect to the same page to prevent form resubmission on refresh
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
}




// for  user 
$query = "SELECT * FROM users WHERE id = $user_id";

$results = $conn->query($query);

if ($results -> num_rows > 0){
    while($row = $results->fetch_assoc()){
        $username = $row['username'];
        $profile_pic = $row['profile_pic'];
        $fullname = $row['name'];
        $birthdate = $row['birthdate'];
        $phone = $row['phone'];
        $goal = $row['financial_goal'];
    }
}

if(isset($_SESSION['message'])){
    echo $_SESSION['message'];
    unset($_SESSION['message']);
}
}else{
  Header("Location: index.php?You are not logged in");
}


// for  family 
/* $query = "SELECT * FROM family WHERE id = $familyid";

$results = $conn->query($query);

if ($results -> num_rows > 0){
    while($row = $results->fetch_assoc()){
        $username = $row['username'];
        $profile_pic = $row['profile_pic'];
        $fullname = $row['name'];
        $birthdate = $row['birthdate'];
        $phone = $row['phone'];
        $goal = $row['financial_goal'];
    }
}
 */

?>

<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
   <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="keywords"
        content="wrappixel, admin dashboard, html css dashboard, web dashboard, bootstrap 5 admin, bootstrap 5, css3 dashboard, bootstrap 5 dashboard, Ample lite admin bootstrap 5 dashboard, frontend, responsive bootstrap 5 admin template, Ample admin lite dashboard bootstrap 5 dashboard template">
    <meta name="description"
        content="Ample Admin Lite is powerful and clean admin dashboard template, inpired from Bootstrap Framework">
    <meta name="robots" content="noindex,nofollow">
    <title>Financial manager profile | <?php echo $username ?></title>
    <link rel="canonical" href="https://www.wrappixel.com/templates/ample-admin-lite/" />
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="plugins/images/jop.png">
    <!-- Custom CSS -->
   <link href="plugins/css/style.min.css" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
</head>

<body>
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
                <div class="navbar-collapse collapse " id="navbarSupportedContent" >
                   
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
                            <a class="btn btn-secondary dropdown-toggle profile-pic" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="plugins/images/users/<?php echo $profile_pic?>" alt="user-img" width="45" height="45"
                            class="img-circle"><span class="text-white font-medium"><?php echo $username ?></span>
                            </a>

                            <ul class="dropdown-menu">
                            <?php if(isset($user_id)) {?>
                            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                            <li><a data-bs-toggle="modal" data-bs-target="#logout-modal" role="button" class="dropdown-item"  >Logout</a></li>
                            <?php } else{?>
                            <li><a data-bs-toggle="modal" data-bs-target="#signup-modal" role="button" class="dropdown-item" href="#">Signup</a></li>
                            <li><a data-bs-toggle="modal" data-bs-target="#login-modal" role="button" class="dropdown-item" href="#">Login</a></li>
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
        <!-- ============================================================== -->
        <!-- End Topbar header -->
        <!-- ============================================================== -->
      
        <!-- Page wrapper  -->
        <!-- ============================================================== -->
        <div class="page-wrapper">
            <!-- ============================================================== -->
            <!-- Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <div class="page-breadcrumb bg-white">
                <div class="row align-items-center">
                    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                        <h4 class="page-title"><span><a href="index.php" ><img height="50" width="50" src="plugins/images/house1.png" alt="Go to home page"></a></span> Profile page</h4>
                    </div>
                    
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- ============================================================== -->
            <!-- End Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- Container fluid  -->
            <!-- ============================================================== -->
            <div class="container-fluid">
                <!-- ============================================================== -->
                <!-- Start Page Content -->
                <!-- ============================================================== -->
                <!-- Row -->
                <div class="row">
                    <!-- Column -->
                    <div class="col-lg-4 col-xlg-3 col-md-12">
                        <div class="white-box">
                            <div class=" user-bg"> <img width="100%" alt="user" src="plugins/images/users/<?php echo $profile_pic?>">
                                <div class="overlay-box">
                                    <div class="user-content">
                                        <a href="javascript:void(0)"><img src="plugins/images/users/<?php echo $profile_pic?>"
                                                class="thumb-lg img-circle" alt="img"></a>
                                        <h4 class="text-white mt-2"><?php echo $username ?></h4>
                                        <h5 class="text-white mt-2"><?php echo $phone?></h5>
                                    </div>
                                </div>
                            </div>
                            <div class="user-btm-box mt-5 d-md-flex justify-content-between">
                                

                                
                            
                                <div class="col-md-4 col-sm-4 text-center">
                                    <a  data-bs-target="#categories-modal" data-bs-toggle="modal" type="button" class="btn btn-success"><h3>Manage categories</h3> </a>
                                </div>

                                <div class="col-md-4 col-sm-4 text-center">
                                    <a  data-bs-target="#family-modal" data-bs-toggle="modal" type="button" class="btn btn-success"><h3>Family Settings</h3> </a>
                                </div>
                            

                                


                            </div>
                        </div>
                    </div>
                    <!-- Column -->
                    <!-- Column -->
                    <div class="col-lg-8 col-xlg-9 col-md-12">
                        <div class="card">
                            <div class="card-body">
                            <form class="form-horizontal form-material" action="plugins/actions/profile_management.php" method="POST"
                            enctype="multipart/form-data">

                            <h4 class="display-4  fs-1">Edit Profile</h4><br>
                            <!-- error -->
                            <?php if(isset($_GET['error'])){ ?>
                            <div class="alert alert-danger" role="alert">
                            <?php echo $_GET['error']; ?>
                            </div>
                            <?php } ?>
                            
                            <!-- success -->
                            <?php if(isset($_GET['success'])){ ?>
                            <div class="alert alert-success" role="alert">
                            <?php echo $_GET['success']; ?>
                            </div>
                            <?php } ?>

                            <input type="hidden" name="user_id" id="user-id" value="<?php echo $user_id; ?>">

                            <input type="hidden" name="userlist" value="Update">

                                    <div class="form-group mb-4">
                                        <label class="col-md-12 p-0">Full Name</label>
                                        <div class="col-md-12 border-bottom p-0">
                                            <input type="text" name="full_name" placeholder="<?php echo $fullname  ?>"
                                                class="form-control p-0 border-0"> 
                                        </div>
                                    </div>
    
                                    <div class="form-group mb-4">
                                        <label class="col-md-12 p-0">Phone No</label>
                                        <div class="col-md-12 border-bottom p-0">
                                            <input type="text" name="phone_number" placeholder="<?php echo $phone?>"
                                                class="form-control p-0 border-0">
                                        </div>
                                    </div>
    
                          
                                    <div class="form-group mb-4">
                                        <div class="input-group ">
                                            <span class="input-group-text">Enter Birthdate</span>
                                            <input type="date" class="form-control" id="date_of_birth" name="birthdate"  >
                                        </div>
                                        <div class="invalid-feedback">
                                            input your date of birth.
                                        </div>
                                    </div>

                              

                                    
                                    <div class="form-group mb-4">
                                        <label class="col-md-12 p-0">UserName</label>
                                        <div class="col-md-12 border-bottom p-0">
                                            <input type="text" name="username" placeholder="<?php if (!empty($username)){echo $username; }else {echo 'user name';}  ?>"
                                                class="form-control p-0 border-0"> 
                                        </div>
                                    </div>

                                    <div class="form-group mb-4">
                                        <label class="col-md-12 p-0">Financial Goal</label>
                                        <div class="col-md-12 border-bottom p-0">
                                            <input type="number" name="goal" placeholder="<?php if (!empty($goal)){echo $goal; }else {echo 'type a target cash goal';}  ?> GH₵"
                                                class="form-control p-0 border-0"> 
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Profile Picture</label>
                                        <input type="file" 
                                            class="form-control"
                                            name="pp">
                                        <input type="text"
                                            hidden="hidden" 
                                            name="old_pp"
                                            value="<?=$profile_pic?>" >
                                    </div>

                                    <div class="form-group mb-4">
                                        <div class="col-sm-12">
                                            <button class="btn btn-success" type="submit">Update Profile</button>
                                        </div>
                                    </div>
                        
                    </div>
    





    
</form>

                            </div>
                        </div>
                    </div>
                    <!-- Column -->
                </div>
                <!-- Row -->
                <!-- ============================================================== -->
                <!-- End PAge Content -->
                <!-- ============================================================== -->
                <!-- ============================================================== -->
                <!-- Right sidebar -->
                <!-- ============================================================== -->
                <!-- .right-sidebar -->
                <!-- ============================================================== -->
                <!-- End Right sidebar -->
                <!-- ============================================================== -->
            </div>

        
            <?php if(isset($user_id)) {?>
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
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  
                  <button class="w-100 btn btn-primary btn-lg"  name="sign_up">signup</button>
                </div> 
                    </div>
                  </form>
                  </div>
              </div>
            </div>
          <!-- this is the login modal -->
         
          <div class="modal fade" id="login-modal" tabindex="-1" aria-labelledby="login-modal-title" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="login-modal-title">Login</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                <div id="login" class="" style="width: 560px;">
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
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  
                  <button class="w-100 btn btn-primary btn-lg"  name="login">login</button>
            </div> 

            </form> 
        </div>
              </div>
            </div>
          </div>
        </div>

        <?php } ?>



         <!-- this is the categories manager modal -->
        <div class="modal fade" id="categories-modal" tabindex="-1" aria-labelledby="categories-modal-title" aria-hidden="false">
                    <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                        <h5 class="modal-title" id="categories-modal-title">categories</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">

                            <div class="nav" id="nav-tab" role="tablist">
                                <button class=" nav-item btn-success text-white  rounded my-2 active" id="nav-income-tab" data-bs-toggle="tab" data-bs-target="#nav-income" type="button" role="tab" aria-controls="nav-income" aria-selected="true"  style="border:0px; margin-right:5vw;">income</button>
                                <button class="nav-item btn-warning my-2 rounded text-white" id="nav-expense-tab" data-bs-toggle="tab" data-bs-target="#expenses" type="button" role="tab" aria-controls="nav-expense" aria-selected="false"style="border:0px; margin-right:5vw;">Expense</button>
                                <button class="nav-item btn-warning my-2 rounded text-white" id="nav-asset-tab" data-bs-toggle="tab" data-bs-target="#asset" type="button" role="tab" aria-controls="nav-asset" aria-selected="false"style="border:0px; margin-right:5vw;">Assets</button>
                                <button class="nav-item btn-warning my-2 rounded text-white" id="nav-liability-tab" data-bs-toggle="tab" data-bs-target="#liability" type="button" role="tab" aria-controls="nav-liability" aria-selected="false"style="border:0px; margin-right:5vw;">Liabilities</button>
                               
                            </div>

                            <!-- this is where the user gets access to all the contents of the page and make changes-->
        <div class="tab-content" id="nav-tabContent">
          
        <!-- this is the income tab -->
        <div class="tab-pane fade show active" id="nav-income" role="tabpanel" aria-labelledby="nav-income-tab">
            <?php while($row = $resultsincomeparent->fetch_assoc()){
                $parentcategory = $row['category_id'];?>
                <div id="parent-<?php echo $parentcategory;?>">
                    <h3 class="text-primary"><?php echo $row['category_name'];?></h3>

                    <?php while($row = $resultsincome->fetch_assoc()){
                        if($row['parent_category_id'] == $parentcategory){
                    $childcategory = $row['category_id'];?>
                    <div id="child-<?php echo $childcategory;?> data-bs-toggle='modal' 
                                    data-bs-target='#update-category-modal-<?php echo $row['category_id']; ?>' role='button' 
                                    class='mt-2' 
                                    style='display: inline-block;'">
                        
                    <h3 class="text-secondary"><?php echo $row['category_name'];?></h3>

                    </div>
                    <?php }}?>
                </div><br>
            <?php } ?>

        <span class="d-flex justify-content-end"><a data-bs-toggle="modal" data-bs-target="#income-add-modal" role="button" ><img height="20" width="20" src="plugins/images/add.png" alt="add income category"></a></span>   

        </div>
          
          <!-- this is the expense section -->
          <div class="tab-pane p-5 " id="expenses" role="tabpanel" aria-labelledby="nav-expense-tab">
            
             <?php while($row = $resultsexpenseparent->fetch_assoc()){
                $parentcategory = $row['category_id'];?>
                <div id="parent-<?php echo $parentcategory;?>">
                    <h3 class="text-primary"><?php echo $row['category_name'];?></h3>

                    <?php while($row = $resultsexpense->fetch_assoc()){
                        if($row['parent_category_id'] == $parentcategory){
                    $childcategory = $row['category_id'];?>
                    <div id="child-<?php echo $childcategory;?> data-bs-toggle='modal' 
                                    data-bs-target='#update-category-modal-<?php echo $row['category_id']; ?>' role='button' 
                                    class='mt-2' 
                                    style='display: inline-block;'">
                        
                    <h3 class="text-secondary"><?php echo $row['category_name'];?></h3>

                    </div>
                    <?php }}?>
                </div><br>
            <?php } ?>

        <span class="d-flex justify-content-end"><a data-bs-toggle="modal" data-bs-target="#expense-add-modal" role="button" ><img height="20" width="20" src="plugins/images/add.png" alt="add expense category"></a></span>   

          </div>

          
      
          <!-- this is the asset section -->
          <div class="tab-pane p-5 " id="asset" role="tabpanel" aria-labelledby="nav-asset-tab">
             <?php while($row = $resultsassetparent->fetch_assoc()){
                $parentcategory = $row['category_id'];?>
                <div id="parent-<?php echo $parentcategory;?>">
                    <h3 class="text-primary"><?php echo $row['category_name'];?></h3>

                    <?php while($row = $resultsasset->fetch_assoc()){
                        if($row['parent_category_id'] == $parentcategory){
                    $childcategory = $row['category_id'];?>
                    <div id="child-<?php echo $childcategory;?> data-bs-toggle='modal' 
                                    data-bs-target='#update-category-modal-<?php echo $row['category_id']; ?>' role='button' 
                                    class='mt-2' 
                                    style='display: inline-block;'">
                        
                    <h3 class="text-secondary"><?php echo $row['category_name'];?></h3>

                    </div>
                    <?php }}?>
                </div><br>
            <?php } ?>

        <span class="d-flex justify-content-end"><a data-bs-toggle="modal" data-bs-target="#asset-add-modal" role="button" ><img height="20" width="20" src="plugins/images/add.png" alt="add expense category"></a></span>   


          </div>
      
          <!-- this is the liability section -->
          <div class="tab-pane p-5 " id="liability" role="tabpanel" aria-labelledby="nav-liability-tab">
            
             <?php while($row = $resultsliabilityparent->fetch_assoc()){
                $parentcategory = $row['category_id'];?>
                <div id="parent-<?php echo $parentcategory;?>">
                    <h3 class="text-primary"><?php echo $row['category_name'];?></h3>

                    <?php while($row = $resultsliability->fetch_assoc()){
                        if($row['parent_category_id'] == $parentcategory){
                    $childcategory = $row['category_id'];?>
                    <div id="child-<?php echo $childcategory;?> data-bs-toggle='modal' 
                                    data-bs-target='#update-category-modal-<?php echo $row['category_id']; ?>' role='button' 
                                    class='mt-2' 
                                    style='display: inline-block;'">
                        
                    <h3 class="text-secondary"><?php echo $row['category_name'];?></h3>

                    </div>
                    <?php }}?>
                </div><br>
            <?php } ?>

        <span class="d-flex justify-content-end"><a data-bs-toggle="modal" data-bs-target="#liability-add-modal" role="button" ><img height="20" width="20" src="plugins/images/add.png" alt="add expense category"></a></span>   


          </div>
      
        </div>



                        
        
        
        
                    </div>
                </div>

                    </div>



            <!-- this is the add income category modal -->
            <div class="modal fade" id="income-add-modal" tabindex="-1" aria-labelledby="income-add-modal-title" aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="income-add-modal-title">Categories - Income</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="post" style="width: 560px;">
                    <input type="hidden" name="user" id="user" value="<?php echo $user_id; ?>">
                    <input type="hidden" name="category" id="category" value="income">

                    <div class="col-8">
                        <div class="input-group">
                            <span class="input-group-text">
                                <label for="name">Enter Category Name</label>
                            </span>
                            <input type="text" name="name" id="name" required>
                        </div>
                    </div>
                    <br>

                    <div class="col-8">
                        <div class="input-group">
                            <span class="input-group-text">
                                <label for="cat-type">Select Category Type</label>
                            </span>
                            <select id="cat-type" required class="form-select" name="cat_type" onchange="toggleParentCategoryincome(this.value)">
                                <?php
                                $categories = ["Parent", "Child"];
                                foreach ($categories as $cat) {
                                    echo "<option value=\"$cat\">$cat</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <br>

                    <!-- Parent Category Selection (Shown only if 'Child' is selected) -->
                    <div class="col-8" id="parent-category-container-income" style="display: none;">
                        <div class="input-group">
                            <span class="input-group-text">
                                <label for="parent-category">Select Parent Category</label>
                            </span>
                            <select id="parent-category" class="form-select" name="parent_category">
                                <option value="">-- Select Parent Category --</option>
                                <?php
                                // Fetch all parent categories from the database
                                $parentCategoriesQuery = "SELECT category_id, category_name FROM financial_category WHERE category_type = 'Parent' AND category_user_id = $user_id  AND category = 'income'";
                                $parentCategories = $conn->query($parentCategoriesQuery);

                                while ($parent = $parentCategories->fetch_assoc()) {
                                    echo "<option value=\"{$parent['category_id']}\">{$parent['category_name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <br>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <input class="btn btn-primary" type="submit" name="submit_income_cat" id="submit">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

            <!-- this is the add expense category modal -->
            <div class="modal fade" id="expense-add-modal" tabindex="-1" aria-labelledby="expense-add-modal-title" aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="expense-add-modal-title">Categories - expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="post" style="width: 560px;">
                    <input type="hidden" name="user" id="user" value="<?php echo $user_id; ?>">
                    <input type="hidden" name="category" id="category" value="expense">

                    <div class="col-8">
                        <div class="input-group">
                            <span class="input-group-text">
                                <label for="name">Enter Category Name</label>
                            </span>
                            <input type="text" name="name" id="name" required>
                        </div>
                    </div>
                    <br>

                    <div class="col-8">
                        <div class="input-group">
                            <span class="input-group-text">
                                <label for="cat-type">Select Category Type</label>
                            </span>
                            <select id="cat-type" required class="form-select" name="cat_type" onchange="toggleParentCategoryexpense(this.value)">
                                <?php
                                $categories = ["Parent", "Child"];
                                foreach ($categories as $cat) {
                                    echo "<option value=\"$cat\">$cat</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <br>

                    <!-- Parent Category Selection (Shown only if 'Child' is selected) -->
                    <div class="col-8" id="parent-category-container-expense" style="display: none;">
                        <div class="input-group">
                            <span class="input-group-text">
                                <label for="parent-category">Select Parent Category</label>
                            </span>
                            <select id="parent-category" class="form-select" name="parent_category">
                                <option value="">-- Select Parent Category --</option>
                                <?php
                                // Fetch all parent categories from the database
                                $parentCategoriesQuery = "SELECT category_id, category_name FROM financial_category WHERE category_type = 'Parent' AND category_user_id = $user_id  AND category = 'expense'";
                                $parentCategories = $conn->query($parentCategoriesQuery);

                                while ($parent = $parentCategories->fetch_assoc()) {
                                    echo "<option value=\"{$parent['category_id']}\">{$parent['category_name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <br>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <input class="btn btn-primary" type="submit" name="submit_expense_cat" id="submit">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

            <!-- this is the add asset category modal -->
            <div class="modal fade" id="asset-add-modal" tabindex="-1" aria-labelledby="asset-add-modal-title" aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="asset-add-modal-title">Categories - asset</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="post" style="width: 560px;">
                    <input type="hidden" name="user" id="user" value="<?php echo $user_id; ?>">
                    <input type="hidden" name="category" id="category" value="asset">

                    <div class="col-8">
                        <div class="input-group">
                            <span class="input-group-text">
                                <label for="name">Enter Category Name</label>
                            </span>
                            <input type="text" name="name" id="name" required>
                        </div>
                    </div>
                    <br>

                    <div class="col-8">
                        <div class="input-group">
                            <span class="input-group-text">
                                <label for="cat-type">Select Category Type</label>
                            </span>
                            <select id="cat-type" required class="form-select" name="cat_type" onchange="toggleParentCategoryasset(this.value)">
                                <?php
                                $categories = ["Parent", "Child"];
                                foreach ($categories as $cat) {
                                    echo "<option value=\"$cat\">$cat</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <br>

                    <!-- Parent Category Selection (Shown only if 'Child' is selected) -->
                    <div class="col-8" id="parent-category-container-asset" style="display: none;">
                        <div class="input-group">
                            <span class="input-group-text">
                                <label for="parent-category">Select Parent Category</label>
                            </span>
                            <select id="parent-category" class="form-select" name="parent_category">
                                <option value="">-- Select Parent Category --</option>
                                <?php
                                // Fetch all parent categories from the database
                                $parentCategoriesQuery = "SELECT category_id, category_name FROM financial_category WHERE category_type = 'Parent' AND category_user_id = $user_id  AND category = 'asset'";
                                $parentCategories = $conn->query($parentCategoriesQuery);

                                while ($parent = $parentCategories->fetch_assoc()) {
                                    echo "<option value=\"{$parent['category_id']}\">{$parent['category_name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <br>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <input class="btn btn-primary" type="submit" name="submit_asset_cat" id="submit">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

            <!-- this is the add liability category modal -->
            <div class="modal fade" id="liability-add-modal" tabindex="-1" aria-labelledby="liability-add-modal-title" aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="liability-add-modal-title">Categories - liability</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="post" style="width: 560px;">
                    <input type="hidden" name="user" id="user" value="<?php echo $user_id; ?>">
                    <input type="hidden" name="category" id="category" value="liability">

                    <div class="col-8">
                        <div class="input-group">
                            <span class="input-group-text">
                                <label for="name">Enter Category Name</label>
                            </span>
                            <input type="text" name="name" id="name" required>
                        </div>
                    </div>
                    <br>

                    <div class="col-8">
                        <div class="input-group">
                            <span class="input-group-text">
                                <label for="cat-type">Select Category Type</label>
                            </span>
                            <select id="cat-type" required class="form-select" name="cat_type" onchange="toggleParentCategoryliability(this.value)">
                                <?php
                                $categories = ["Parent", "Child"];
                                foreach ($categories as $cat) {
                                    echo "<option value=\"$cat\">$cat</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <br>

                    <!-- Parent Category Selection (Shown only if 'Child' is selected) -->
                    <div class="col-8" id="parent-category-container-liability" style="display: none;">
                        <div class="input-group">
                            <span class="input-group-text">
                                <label for="parent-category">Select Parent Category</label>
                            </span>
                            <select id="parent-category" class="form-select" name="parent_category">
                                <option value="">-- Select Parent Category --</option>
                                <?php
                                // Fetch all parent categories from the database
                                $parentCategoriesQuery = "SELECT category_id, category_name FROM financial_category WHERE category_type = 'Parent' AND category_user_id = $user_id  AND category = 'liability'";
                                $parentCategories = $conn->query($parentCategoriesQuery);

                                while ($parent = $parentCategories->fetch_assoc()) {
                                    echo "<option value=\"{$parent['category_id']}\">{$parent['category_name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <br>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <input class="btn btn-primary" type="submit" name="submit_liability_cat" id="submit">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- script for income -->

<script>
    // Show/hide the parent category selection based on the category type
    function toggleParentCategoryincome(value) {
        const parentCategoryContainer = document.getElementById('parent-category-container-income');
        parentCategoryContainer.style.display = value === 'Child' ? 'block' : 'none';
    }
</script>



            <!-- this is the add expense category modal -->
<div class="modal fade" id="expense-add-modal" tabindex="-1" aria-labelledby="expense-add-modal-title" aria-hidden="false">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="expense-add-modal-title">categories - Expense</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                <form action="" method="post" style="width: 560px;">
                  <input type="hidden" name="user" id="user" value = "<?php echo $row['category_user_id']; ?>">
                  <input type="hidden" name="category" id="category" value = "expense">

              <div class="col-8">
                          <div class="input-group ">
                          <span class="input-group-text"><label for="name">Enter Category Name</label></span>
                          <input type="text" name="submit_expense_cat" id="name" required>
                          </div>
                      
              </div>
                  
                  <br>

                  <div class="col-8">
                          <div class="input-group ">
                          <span class="input-group-text"><label for="cat-type">select category type</label></span>
                            <select id="cat-type"  required class="form-select" name="cat-type" class="">
                                    <?php
                                    $categories = ["Parent","Child"];
                                    foreach ($categories as $cat) {
                                        echo "<option value=\"$cat\" >$cat</option>";
                                    }
                                    ?>
                            </select>
                          </div>
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


<!-- script for expense -->

<script>
    // Show/hide the parent category selection based on the category type
    function toggleParentCategoryexpense(value) {
        const parentCategoryContainer = document.getElementById('parent-category-container-expense');
        parentCategoryContainer.style.display = value === 'Child' ? 'block' : 'none';
    }
</script>

            <!-- this is the add asset category modal -->
<div class="modal fade" id="asset-add-modal" tabindex="-1" aria-labelledby="asset-add-modal-title" aria-hidden="false">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="asset-add-modal-title">categories - asset</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                <form action="" method="post" style="width: 560px;">
                  <input type="hidden" name="user" id="user" value = "<?php echo $row['category_user_id']; ?>">
                  <input type="hidden" name="category" id="category" value = "asset">

              <div class="col-8">
                          <div class="input-group ">
                          <span class="input-group-text"><label for="name">Enter Category Name</label></span>
                          <input type="text" name="name" id="name" required>
                          </div>
                      
              </div>
                  
                  <br>

                  <div class="col-8">
                          <div class="input-group ">
                          <span class="input-group-text"><label for="cat-type">select category type</label></span>
                          <select id="cat-type"  required class="form-select" name="cat-type" class="">
                                    <?php
                                    $categories = ["Parent","Child"];
                                    foreach ($categories as $cat) {
                                        echo "<option value=\"$cat\" >$cat</option>";
                                    }
                                    ?>
                        </select>
                          </div>
                  </div>
                  
             
                  
                  <br>

                


                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  
                  <input class="btn btn-primary" type="submit" name="submit_asset_cat" id="submit">
    </form>
              </div>
            </div>
          </div>

</div>
<!-- script for assect -->

<script>
    // Show/hide the parent category selection based on the category type
    function toggleParentCategoryasset(value) {
        const parentCategoryContainer = document.getElementById('parent-category-container-asset');
        parentCategoryContainer.style.display = value === 'Child' ? 'block' : 'none';
    }
</script>


            <!-- this is the add liabilities category modal -->
<div class="modal fade" id="liability-add-modal" tabindex="-1" aria-labelledby="liability-add-modal-title" aria-hidden="false">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="liability-add-modal-title">categories - Liability</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                <form action="" method="post" style="width: 560px;">
                  <input type="hidden" name="user" id="user" value = "<?php echo $row['category_user_id']; ?>">
                  <input type="hidden" name="category" id="category" value = "liability">

              <div class="col-8">
                          <div class="input-group ">
                          <span class="input-group-text"><label for="name">Enter Category Name</label></span>
                          <input type="text" name="name" id="name" required>
                          </div>
                      
              </div>
                  
                  <br>

                  <div class="col-8">
                          <div class="input-group ">
                          <span class="input-group-text"><label for="cat-type">select category type</label></span>
                          <select id="cat-type"  required class="form-select" name="cat-type" class="">
                                    <?php
                                    $categories = ["Parent","Child"];
                                    foreach ($categories as $cat) {
                                        echo "<option value=\"$cat\" >$cat</option>";
                                    }
                                    ?>
                        </select>
                          </div>
                  </div>
                  
             
                  
                  <br>

                


                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  
                  <input class="btn btn-primary" type="submit" name="submit_liability_cat" id="submit">
    </form>
              </div>
            </div>
          </div>

</div>

<!-- script for income -->

<script>
    // Show/hide the parent category selection based on the category type
    function toggleParentCategoryliability(value) {
        const parentCategoryContainer = document.getElementById('parent-category-container-liability');
        parentCategoryContainer.style.display = value === 'Child' ? 'block' : 'none';
    }
</script>
            <!-- ============================================================== -->
            <!-- End Container fluid  -->
            <!-- ============================================================== -->
            
      
        <!-- ============================================================== -->
        <!-- End Page wrapper  -->
        <!-- ============================================================== -->
                    </div>



         <!-- this is the family Settings modal -->
        <div class="modal fade" id="family-modal" tabindex="-1" aria-labelledby="family-modal-title" aria-hidden="false">
                    <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                        <h5 class="modal-title" id="family-modal-title">Family Settings</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">

                            <div class="nav" id="nav-tab" role="tablist">
                                <button class=" nav-item btn-success text-white  rounded my-2 active" id="nav-family-join-tab" data-bs-toggle="tab" data-bs-target="#nav-family-join" type="button" role="tab" aria-controls="nav-family-join" aria-selected="true"  style="border:0px; margin-right:5vw;">Join Family</button>
                                <button class="nav-item btn-warning my-2 rounded text-white" id="nav-expense-tab" data-bs-toggle="tab" data-bs-target="#family-create" type="button" role="tab" aria-controls="nav-family-create" aria-selected="false"style="border:0px; margin-right:5vw;">Create a family</button>
                               
                            </div>

                            <!-- this is where the user gets access to all the contents of the page and make changes-->
        <div class="tab-content" id="nav-tabContent">
          
        <!-- this is the family-join tab -->
        <!-- Family Join Tab -->
<div class="tab-pane fade show active" id="nav-family-join" role="tabpanel" aria-labelledby="nav-family-join-tab">
    <form action="plugins/actions/family_join.php" method="post" style="width: 560px;">
        <input type="hidden" name="user" id="user" value="<?php echo $user_id; ?>">

        <div class="col-8">
            <div class="input-group">
                <span class="input-group-text">
                    <label for="name">Enter Family Name</label>
                </span>
                <input type="text" name="name" id="name" required>
            </div>
        </div>
        <br>

        <div class="col-8">
            <div class="input-group">
                <span class="input-group-text">
                    <label for="passcode">Enter Family Passcode</label>
                </span>
                <input type="text" name="passcode" id="passcode" required>
            </div>
        </div>
        <br>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <input class="btn btn-primary" type="submit" name="submit_join" id="submit">
        </div>
    </form>
</div>

          
          <!-- this is the family-create section -->
          <!-- Family Create Tab -->
<div class="tab-pane p-5" id="family-create" role="tabpanel" aria-labelledby="nav-family-create-tab">
    <form action="plugins/actions/family_create.php" method="post" style="width: 560px;">
        <input type="hidden" name="user" id="user" value="<?php echo $user_id; ?>">

        <div class="col-8">
            <div class="input-group">
                <span class="input-group-text">
                    <label for="name">Enter Family Name</label>
                </span>
                <input type="text" name="name" id="name" required>
            </div>
        </div>
        <br>

        <div class="col-8">
            <div class="input-group">
                <span class="input-group-text">
                    <label for="security_code">Enter Family Security Code</label>
                </span>
                <input type="text" name="security_code" id="security_code" required>
            </div>
        </div>
        <br>

        <div class="col-8">
            <div class="input-group">
                <span class="input-group-text">
                    <label for="description">Enter Family Description</label>
                </span>
                <input type="text" name="description" id="description" required>
            </div>
        </div>
        <br>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <input class="btn btn-primary" type="submit" name="family_create" id="submit">
        </div>
    </form>
</div>


          
      
        </div>



                        
        
        
        
                    </div>
                </div>

                    </div>



         

 





       



            <!-- ============================================================== -->
            <!-- End Container fluid  -->
            <!-- ============================================================== -->
            
      
        <!-- ============================================================== -->
        <!-- End Page wrapper  -->
        <!-- ============================================================== -->
                    </div>



                    
                    </div>
                    </div>
    <!-- ============================================================== -->
    <!-- End Wrapper -->
    <!-- ============================================================== -->

    <!-- ============================================================== -->
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

            
    <!-- ============================================================== -->
    <!-- All Jquery -->
    <!-- ============================================================== -->
    <script src="plugins/bower_components/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap tether Core JavaScript -->
    <script src="plugins/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="plugins/js/app-style-switcher.js"></script>
    <!--Wave Effects -->
    <script src="plugins/js/waves.js"></script>
    <!--Menu sidebar -->
    <script src="plugins/js/sidebarmenu.js"></script>
    <!--Custom JavaScript -->
    <script src="plugins/js/custom.js"></script>
</body>

</html>