<?php

require_once("plugins/actions/config.php");
require_once("plugins/actions/functions.php");  // Include the functions file
session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Get task-related counts and user data
    $myday_no = getMyDayCount($user_id, $conn);
    $user_data = getUserData($user_id, $conn);
    $important_no = getImportantTaskCount($user_id, $conn);
    $tasks_no = getTotalTaskCount($user_id, $conn);

    // Extract user data values
    $username = $user_data['username'];
    $profile_pic = $user_data['profile_pic'];
    // Get data
    $only_income = getTransactionSum($user_id, 'income', $conn);
    $positive_cashflow = getCashflow($user_id, 'asset', $conn);
    $negative_cashflow = getCashflow($user_id, 'liability', $conn);

    // Fetch total expense and total for selected categories
    $only_expense = getTransactionSum($user_id, 'expense', $conn);
    $total_expense = $only_expense + $positive_cashflow + $negative_cashflow;

    // Calculate total values
    $passive_income = $positive_cashflow - $negative_cashflow;
    $total_income = $passive_income + $only_income;

    $cash = $user_data['balance'];
    // Calculate balance
    $balance = $total_income - $total_expense;
    updateUserBalance($user_id, $balance, $conn);

    // Display session message if exists
    displaySessionMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tasks Manager</title>
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
<style>
  /* .navbar-toggler-icon {
    background-color: black; /* Or any other color 
  } */
</style>
</head>

<body class="bg-dark">
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
          
                      <div class="row align-items-center">
                         
  
                          <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
                              <div class="d-md-flex">
                                  <ol class="breadcrumb ms-end">
                                      <li><span><a href="index.php" ><img height="50" width="50" src="plugins/images/exit.jpg" alt="Go to home page"></a></span></li>
                                  </ol>
                                  </div>
                          </div>
                         
                      </div>
                      
                  </div>


        <ul>
          <?php if(isset($_SESSION['user_id'])){
?>
            <hr style="color: white; border: 1px;">
            <li><a href="myday.php" class="text-light p-3 mt-3"><span ><img height="20" width="20" src="plugins/images/myday.png" alt="My day"></span>  My Day <span><?php echo $myday_no;?></span></a></li>
            <hr style="color: white; border: 1px;">
            <li><a href="important.php" class="text-light p-3 mt-3"><span><img height="20" width="20" src="plugins/images/starred.png" alt="important"></span>  Important <span><?php echo $important_no;?></span></a> </li>
            <hr style="color: white; border: 1px;">
            <li><a href="tasks.php" class="text-light  p-3 mt-3"><span><img height="20" width="20" src="plugins/images/tasks.png" alt="tasks"></span>  Tasks <span><?php echo $tasks_no;?></span></a> </li>
            <hr style="color: white; border: 1px;">
<?php }else{ ?>
  <hr style="color: white; border: 1px;">
    <h4 class="text-warning">Please login to continue</h4>
            <li class="disabled"><a href="#" class="text-light p-3 mt-3 disabled-link" ><span ><img height="20" width="20" src="plugins/images/myday.png" alt="My day" ></span>  My Day <span><?php echo $myday_no;?></span></a>  <span ><img height="20" width="20" src="plugins/images/locked.png" ></span></li>
            <hr style="color: white; border: 1px;">
            <li class="disabled"><a href="#" class="text-light p-3 mt-3 disabled-link" ><span><img height="20" width="20" src="plugins/images/starred.png" alt="important" ></span>  Important <span><?php echo $important_no;?></span></a>   <span ><img height="20" width="20" src="plugins/images/locked.png" ></span></li>
            <hr style="color: white; border: 1px;">
            <li class="disabled"><a href="#" class="text-light  p-3 mt-3 disabled-link" ><span><img height="20" width="20" src="plugins/images/tasks.png" alt="tasks" ></span>  Tasks <span><?php echo $tasks_no;?></span></a>   <span ><img height="20" width="20" src="plugins/images/locked.png" ></span></li>
            <hr style="color: white; border: 1px;">
<?php }?>
        </ul>
       

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
    <script src="plugins/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="plugins/js/app-style-switcher.js"></script>
    <!--Wave Effects -->
    <script src="plugins/js/waves.js"></script>
    <!--Menu sidebar -->
    <script src="plugins/js/sidebarmenu.js"></script>
    <!--Custom JavaScript -->
    <script src="plugins/js/custom.js"></script>
</html>