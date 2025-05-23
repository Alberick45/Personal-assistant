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

<script src="plugins/js/reminders.js"></script>

<style>
  /* .navbar-toggler-icon {
    background-color: black; /* Or any other color 
  } */

   /* General Styles */
.page-breadcrumb.bg-secondary {
    background-color: #6c757d; /* Dark gray background */
    padding: 15px 0;
}

/* General Breadcrumb Styles */
.breadcrumb {
    display: flex;
    align-items: center;
    justify-content: flex-end; /* Align to the right */
    margin-bottom: 0;
    padding: 0;
}

.breadcrumb li {
    list-style-type: none;
}

.breadcrumb a {
    display: inline-block;
    padding: 10px;
    color: #007bff; /* Blue color for the link */
    text-decoration: none;
    transition: color 0.3s ease, transform 0.3s ease;
}

.breadcrumb a:hover {
    color: #0056b3; /* Darker blue when hovered */
    transform: scale(1.1); /* Slightly enlarge on hover */
}

.breadcrumb a img {
    transition: transform 0.3s ease;
}

/* Add a hover effect to the image */
.breadcrumb a:hover img {
    transform: rotate(10deg); /* Image rotation effect */
}

/* Responsive Design for smaller screens */
@media (max-width: 768px) {
    .breadcrumb {
        justify-content: center; /* Center the breadcrumb on small screens */
    }

    .breadcrumb a {
        padding: 5px; /* Reduce padding on small screens */
    }

    .breadcrumb a img {
        height: 40px; /* Reduce the size of the image on small screens */
        width: 40px;
    }
}


.page-breadcrumb .breadcrumb {
    color: white;
    margin-bottom: 0;
}


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

/* List Styling */
ul.list-unstyled {
    list-style-type: none;
    padding: 0;
    margin: 0;
}

hr.divider {
    border: 1px solid white;
    margin: 10px 0;
}

li a {
    display: flex;
    align-items: center;
    justify-content: space-between;
    text-decoration: none;
    color: white;
    transition: background-color 0.3s ease;
    padding: 15px;
    border-radius: 5px;
}

li a:hover {
    background-color: #007bff;
}

li a .disabled-link {
    cursor: not-allowed;
    opacity: 0.6;
}

li.disabled {
    color: gray;
}

/* Disable the 'locked' icon styling */
.disabled-link img {
    opacity: 0.5;
}

/* Disabled State Styles */
li.disabled a {
    pointer-events: none;
    color: #6c757d; /* Grey out disabled items */
}

li.disabled a span {
    opacity: 0.5; /* Dim the icons for disabled links */
}

a.dropdown-item{
    color:gray;
}

/* Icons and Text Styling */
li a span img {
    margin-right: 10px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .page-breadcrumb {
        padding: 10px 0;
    }
    .breadcrumb {
        text-align: center;
    }
}

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

        
        <div class="page-breadcrumb" style="background-color: brown;">
    <div class="row align-items-center">
        <div class="col-12 d-flex justify-content-end">
            <div class="d-md-flex">
                <!-- Adjust the breadcrumb to push it further to the right -->
                <ol class="breadcrumb mb-0 ms-auto">
                    <li>
                        <span>
                            <a href="index.php">
                                <img height="50" width="50" src="plugins/images/exit.jpg" alt="Go to home page">
                            </a>
                        </span>
                    </li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- The audio element that will play the ping sound -->
<audio id="notificationSound" src="plugins/sounds/ping.mp3" preload="auto"></audio>

<div id="reminder-message"></div>



<ul class="list-unstyled">
    <?php if (isset($_SESSION['user_id'])) { ?>
        <hr class="divider">
        <li>
            <a href="myday.php" class="text-light p-3 mt-3">
                <span>
                    <img height="20" width="20" src="plugins/images/myday.png" alt="My day">
                </span> My Day <span><?php echo $myday_no; ?></span>
            </a>
        </li>
        <hr class="divider">
        <li>
            <a href="important.php" class="text-light p-3 mt-3">
                <span>
                    <img height="20" width="20" src="plugins/images/starred.png" alt="important">
                </span> Important <span><?php echo $important_no; ?></span>
            </a>
        </li>
        <hr class="divider">
        <li>
            <a href="tasks.php" class="text-light p-3 mt-3">
                <span>
                    <img height="20" width="20" src="plugins/images/tasks.png" alt="tasks">
                </span> Tasks <span><?php echo $tasks_no; ?></span>
            </a>
        </li>
        <hr class="divider">
    <?php } else { ?>
        <hr class="divider">
        <h4 class="text-warning">Please login to continue</h4>
        <li class="disabled">
            <a href="#" class="text-light p-3 mt-3 disabled-link">
                <span>
                    <img height="20" width="20" src="plugins/images/myday.png" alt="My day">
                </span> My Day <span><?php echo $myday_no; ?></span>
            </a>
            <span>
                <img height="20" width="20" src="plugins/images/locked.png">
            </span>
        </li>
        <hr class="divider">
        <li class="disabled">
            <a href="#" class="text-light p-3 mt-3 disabled-link">
                <span>
                    <img height="20" width="20" src="plugins/images/starred.png" alt="important">
                </span> Important <span><?php echo $important_no; ?></span>
            </a>
            <span>
                <img height="20" width="20" src="plugins/images/locked.png">
            </span>
        </li>
        <hr class="divider">
        <li class="disabled">
            <a href="#" class="text-light p-3 mt-3 disabled-link">
                <span>
                    <img height="20" width="20" src="plugins/images/tasks.png" alt="tasks">
                </span> Tasks <span><?php echo $tasks_no; ?></span>
            </a>
            <span>
                <img height="20" width="20" src="plugins/images/locked.png">
            </span>
        </li>
        <hr class="divider">
    <?php } ?>
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

    
<script src="plugins/js/repeat_tasks.js"></script>
    <!--Menu sidebar -->
    <script src="plugins/js/sidebarmenu.js"></script>
    <!--Custom JavaScript -->
    <script src="plugins/js/custom.js"></script>
</html>