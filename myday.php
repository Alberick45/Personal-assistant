<?php

require_once("plugins/actions/config.php");
require_once("plugins/actions/functions.php");  

session_start();

if (isset($_SESSION['user_id'])) {

    $user_id = $_SESSION['user_id'];
    $user_data = getUserData($user_id, $conn);

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

    // Handle Task Form Submission
    if (isset($_POST['submit'])) {
        $task_name = $_POST['name'] ?? null;
        $task_description = $_POST['description'] ?? null;
        $task_deadline = $_POST['deadline'] ?? null;
        $task_duration = $_POST['duration'] ?? null;
        $task_assigner = $_POST['assigner'] ?? null;
        $file_name = $_POST['file'] ?? null;

        try {
            addTask($task_name, $task_deadline, $task_duration, $task_description, $task_assigner, $user_id, $_FILES['file'] ?? null, $conn);
            echo "New task created successfully!";
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }

        // Redirect to avoid form resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }


    
if (isset($_POST['upload_tasks']) && isset($_FILES['task_file'])) {
  $user_id = $_SESSION['user_id']; // Assuming user ID is stored in session
  $task_file = $_FILES['task_file'];

  // Call the function to handle the CSV import
  $result_message = importTasksFromCSV($conn, $task_file, $user_id);

  // Redirect with appropriate message
  $location = $_SERVER['PHP_SELF'] . '?' . (strpos($result_message, 'successfully') !== false ? "success" : "error") . "=" . urlencode($result_message);
  header("Location: $location");
  exit();
} else {
  echo "No file was uploaded.";
}


if (isset($_POST['upload_timetable']) && isset($_FILES['timetable_file'])) {
  $user_id = $_SESSION['user_id']; // Assuming user ID is stored in session
  $timetable_file = $_FILES['timetable_file'];

  // Call the function to handle the CSV import
  $result_message = importTimetableFromCSV($conn, $timetable_file, $user_id);

  // Redirect with the appropriate message
  $location = $_SERVER['PHP_SELF'] . '?' . (strpos($result_message, 'successfully') !== false ? "success" : "error") . "=" . urlencode($result_message);
  header("Location: $location");
  exit();
} else {
  echo "No file was uploaded.";
}
    // Get My Day Tasks and Counts
    $resultsmyday_tasks = getTasksByStatusAndDate($user_id, 'PENDING',$conn);
    $myday_no = getTaskCountByStatus($user_id, 'PENDING', 'CURDATE()', $conn);

    // Get Completed Tasks for Today
    $resultstasks_completed = getTasksByStatusAndDate($user_id, 'COMPLETED',  $conn);


    // Display session message if exists
    displaySessionMessage();

} else {
    header("Location: task.php?You are not logged in");
    exit();
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

<body>
  <!-- The audio element that will play the ping sound -->
<audio id="notificationSound" src="plugins/sounds/ping.mp3" preload="auto"></audio>

<div id="reminder-message"></div>

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
        <!-- Left Section with Title and Date -->
        <div class="col-lg-3 col-md-4 col-sm-4 col-12">
            <h4 class="page-title">
                <span>
                    <a href="task.php">
                        <img height="50" width="50" src="plugins/images/house1.png" alt="Go to home page">
                    </a>
                </span> 
                Today (<?php echo date("D M, Y") ?>)
            </h4>
        </div>

        <!-- Right Section with Buttons -->
        <div class="col-lg-9 col-md-8 col-sm-8 col-12 d-flex justify-content-end">
            <div class="d-flex flex-wrap justify-content-end gap-2">
                <a type="button" class="btn btn-danger waves-effect waves-light text-white"
                   data-bs-toggle="modal" data-bs-target="#timetable-modal">
                    Upload Timetable
                </a>
                <a type="button" class="btn btn-danger waves-effect waves-light text-white"
                   data-bs-toggle="modal" data-bs-target="#task-upload-modal">
                    Upload Task
                </a>
            </div>
        </div>
    </div>
    <!-- /.col-lg-12 -->
</div>


<div class="accordion" id="accordionExample">
<div class="accordion-item">
<h2 class="accordion-header">
  <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
  <h2 class="text-center">Pending Tasks</h2>
  </button>
</h2>
<div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#accordionExample">
  <div class="accordion-body">
    <?php while ($row = $resultsmyday_tasks->fetch_assoc()) {?>

<div id="tasks"  class="bg-dark d-flex align-items-center  justify-content-between" >
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
          <img src="plugins/images/file.png" alt="download" style="width: 20px; height: 20px;"></a>
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

      <div class="col-md-6">
        <div class="input-group">
          <span class="input-group-text" id="reminderLabel-<?php echo $row['id'];?>" style="cursor: pointer;">
            <label for="reminder"><img src="plugins/images/remind.png" alt="reminder" style="width: 30px; height: 30px;"></label>
          </span>
          <input type="datetime-local" name="reminder" id="reminder-<?php echo $row['id'];?>" class="form-control">
        </div>
      </div>

      <div class="col-md-6">
        <div class="input-group">
          <span class="input-group-text" id="repeatLabel-<?php echo $row['id'];?>" style="cursor: pointer;">
            <label for="repeat"><img src="plugins/images/repeat.png" alt="repeat" style="width: 20px; height: 20px;"></label>
          </span>
          <input type="text" name="repeat" id="repeat-<?php echo $row['id'];?>" class="form-control" placeholder="(num of days),(DAY,WEEK,YEAR or MONTH)">
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

        <div id="tasks" class="bg-dark d-flex align-items-center  justify-content-between" >
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
                <h2 class="text-center">Upload template files</h2>
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




<!-- later we can upload documents and profile pictures that ec an use so store name of location in database and the actua file store in a 
 folder called files with user id and another id for that file   so can update to add files,change status decripton duration and deadline  and can delete a task and for users can update birthdate phone and profile pic-->
  








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
                <input type="number" name="duration" id="duration" value="1"> <span class="input-group-text">Seconds</span>
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

  <!-- this is upload timetables -->
<div class="modal fade" id="timetable-modal" aria-hidden="false">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Upload TImetable</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
        
        <form action="" method="POST" id="add_timetable" class="needs-validation" novalidate enctype="multipart/form-data">
            <div class="input-group mb-3">
                <input type="file" class="form-control" id="inputGroupFile02" name="timetable_file">
                <label class="input-group-text" for="inputGroupFile02">Upload Timetable here</label>
            </div>

            

            <button class="w-100 btn btn-primary btn-lg" name="upload_timetable">Upload</button>
        </form>

        


          </div>
       
      </div>
    </div>
  </div>


   <!-- this is upload tasks -->
<div class="modal fade" id="task-upload-modal" aria-hidden="false">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel2">Upload Tasks</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
        
        <form action="" method="POST" id="add_task" class="needs-validation" novalidate enctype="multipart/form-data">
            <div class="input-group mb-3">
                <input type="file" class="form-control" id="inputGroupFile02" name="task_file">
                <label class="input-group-text" for="inputGroupFile02">Upload Task here</label>
            </div>

           
            <button class="w-100 btn btn-primary btn-lg" name="upload_task">Upload</button>
        </form>

       


          </div>
       
      </div>
    </div>
  </div>

</body>




<script src="plugins/js/repeat_tasks.js"></script>

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
</html>`