<?php 
session_start();
require("actions/config.php");

global $conn;
if (!isset($_SESSION['staff_id'])) {
    header("Location: signup-page.php?You are not logged in");
    echo "You are not logged in";
    exit();
}elseif(!isset($_SESSION["access_level"])) {
    header("Location: ../index.php?You are not authorized");
    echo "You are not authorized";
    exit();
} else {
    $staff_id = $_SESSION['staff_id'];
    $userdata = "SELECT * FROM staff WHERE staff_id = '$staff_id'";
    $result = $conn -> query($userdata);
    $row = $result -> fetch_assoc();
    $staff_id = $row["staff_id"];
    $position = $row["position_id"];
    $accesslevel = $row["access_level"];
    $user_name = $row['username'];
    if (isset($_SESSION['message'])) {
        echo $_SESSION['message'];
        unset($_SESSION['message']);
    }
    if (
      !(in_array($accesslevel, [3]) || 
      in_array($position, ["AC1-HC", "AC1-AC","AC1-S","AC1-B","AC2-AM"]))
    )  {
        header("Location: index.php?You are not authorized");
        echo "You are not authorized";
        exit();
    }
    // $profile_pic =$row["ru_pic"];
    if (isset($_SESSION['message'])) {
        echo $_SESSION['message'];
        unset($_SESSION['message']);
    }

  
}

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
    <title>global delight menu | <?php echo $user_name ?> </title>
    <link rel="canonical" href="https://www.wrappixel.com/templates/ample-admin-lite/" />
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="plugins/images/logo.jpg">
    <!-- Custom CSS -->
   <link href="css/style.min.css" rel="stylesheet">
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
    <div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full"
        data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">
        <!-- ============================================================== -->
        <!-- Topbar header - style you can find in pages.scss -->
        <!-- ============================================================== -->
        <header class="topbar" data-navbarbg="skin5">
            <nav class="navbar top-navbar navbar-expand-md navbar-dark">
                <div class="navbar-header" data-logobg="skin6">
                    <!-- ============================================================== -->
                    <!-- Logo -->
                    <!-- ============================================================== -->
                    <a class="navbar-brand" href="dashboard.php">
                        <!-- Logo text -->
                        <span class="logo-text">
                            <!-- dark Logo text -->
                            <img src="plugins/images/logo-text.jpg"  width="auto" height="50" alt="homepage" />
                        </span>
                    </a>
                    <!-- ============================================================== -->
                    <!-- End Logo -->
                    <!-- ============================================================== -->
                    <!-- ============================================================== -->
                    <!-- toggle and nav items -->
                    <!-- ============================================================== -->
                    <a class="nav-toggler waves-effect waves-light text-dark d-block d-md-none"
                        href="javascript:void(0)"><i class="ti-menu ti-close"></i></a>
                </div>
                <!-- ============================================================== -->
                <!-- End Logo -->
                <!-- ============================================================== -->
                <div class="navbar-collapse collapse bg-warning" id="navbarSupportedContent" >
                    <ul class="navbar-nav d-none d-md-block d-lg-none">
                        <li class="nav-item">
                            <a class="nav-toggler nav-link waves-effect waves-light text-white"
                                href="javascript:void(0)"><i class="ti-menu ti-close"></i></a>
                        </li>
                    </ul>
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
                            <a class="profile-pic" href="profile.php">
                                <img src="plugins/images/users/profilepic-1.jpeg" alt="user-img" width="36"
                                    class="img-circle"><span class="text-white font-medium"><?php echo $user_name ?></span></a>
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
        <!-- ============================================================== -->
        <!-- Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->
        <aside class="left-sidebar" data-sidebarbg="skin6">
            <!-- Sidebar scroll-->
            <div class="scroll-sidebar">
                <!-- Sidebar navigation-->
                <nav class="sidebar-nav">
                <ul id="sidebarnav">
                        <!-- User Profile-->

                        <?php 
                        require("actions/config.php");
                            if (
                                in_array($accesslevel, [2,3])
                            )  { ?>
    
                        <li class="sidebar-item pt-2">
                            <a class="sidebar-link waves-effect waves-dark sidebar-link" href="index.php"
                                aria-expanded="false">
                                <i class="far fa-clock" aria-hidden="true"></i>
                                <span class="hide-menu">Dashboard</span>
                            </a>
                        </li>

                        <?php 
                                }
                              ?>


                        <?php 
                        require("actions/config.php");
                            if (
                                in_array($accesslevel, [1,2,3])
                            )  { ?>
                            

                        <li class="sidebar-item">
                            <a class="sidebar-link waves-effect waves-dark sidebar-link" href="profile.php"
                                aria-expanded="false">
                                <i class="fa fa-user" aria-hidden="true"></i>
                                <span class="hide-menu">Profile</span>
                            </a>
                        </li>
                        <?php 
                            }
                            ?>



                        <?php 
                        require("actions/config.php");
                            if (
                                in_array($accesslevel, [3]) || 
                                in_array($position, ["AC1-H", "AC1-S","AC1-B"])
                            )  { ?>
                            
                        <li class="sidebar-item">
                            <a class="sidebar-link waves-effect waves-dark sidebar-link" href="customers.php"
                                aria-expanded="false">
                                <i class="fa fa-users" aria-hidden="true"></i>
                                <span class="hide-menu">Customers</span>
                            </a>
                        </li>
                        <?php 
                            }
                            ?>



                        <?php 
                        require("actions/config.php");
                            if (
                                in_array($position, ["AC1-H", "AC1-S","AC1-DA","AC2-FM","AC3-GM","AC3-AM"])
                            )  { ?>
                            
                        <li class="sidebar-item">
                            <a class="sidebar-link waves-effect waves-dark sidebar-link" href="orders.php"
                                aria-expanded="false">
                                <i class="fas fa-hourglass-start" aria-hidden="true"></i>
                                <span class="hide-menu">Orders</span>
                            </a>
                        </li>
                            <?php 
                                }
                              ?>



                        <?php 
                        require("actions/config.php");
                            if (
                                in_array($accesslevel, [3]) || 
                                in_array($position, ["AC1-HC", "AC1-AC","AC1-S","AC1-B","AC2-AM"])
                            )  { ?>
                            
                        <li class="sidebar-item">
                            <a class="sidebar-link waves-effect waves-dark sidebar-link" href="menu.php"
                                aria-expanded="false">
                                <i class="fas fa-utensils" aria-hidden="true"></i>
                                <span class="hide-menu">Menu</span>
                            </a>
                        </li>
                            <?php 
                                }
                              ?>



                        <?php 
                        require("actions/config.php");
                            if (
                                in_array($position, ["AC1-HC", "AC1-AC","AC1-SM","AC3-AM","AC3-GM"])
                            )  { ?>
                            
                        <li class="sidebar-item">
                            <a class="sidebar-link waves-effect waves-dark sidebar-link" href="inventory.php"
                                aria-expanded="false">
                                <i class="fas fa-box" aria-hidden="true"></i>
                                <span class="hide-menu">Inventory</span>
                            </a>
                        </li>
                            <?php 
                                }
                            ?>

                        <?php 
                        require("actions/config.php");
                            if (
                                in_array($accesslevel, [3])
                            )  { ?>
                            
                        <li class="sidebar-item">
                            <a class="sidebar-link waves-effect waves-dark sidebar-link" href="departments.php"
                                aria-expanded="false">
                                <i class="fas fa-box" aria-hidden="true"></i>
                                <span class="hide-menu">Departments</span>
                            </a>
                        </li>
                            <?php 
                                }
                            ?>



                        <?php 
                        require("actions/config.php");
                            if (
                                in_array($accesslevel, [3]) || 
                                in_array($position, ["AC2-FM"])
                            )  { ?>
                            
                        <li class="sidebar-item">
                            <a class="sidebar-link waves-effect waves-dark sidebar-link" href="transactions-history.php"
                                aria-expanded="false">
                                <i class="fas fa-money-bill-alt" aria-hidden="true"></i>
                                <span class="hide-menu">Transactions-history</span>
                            </a>
                        </li>

                            <?php 
                                }
                              ?>



                        <?php 
                        require("actions/config.php");
                            if (
                                in_array($accesslevel, [2,3])
                            )  { ?>
                            
                        <li class="sidebar-item">
                            <a class="sidebar-link waves-effect waves-dark sidebar-link" href="requests.php"
                                aria-expanded="false">
                                <i class="fas fa-comment-dots" aria-hidden="true"></i>
                                <span class="hide-menu">Requests</span>
                            </a>
                        </li>
                            <?php 
                                }
                              ?>




                        <?php 
                        require("actions/config.php");
                            if (
                                in_array($accesslevel, [3]) || 
                                in_array($position, ["AC1-H","AC2-AM"])
                            )  { ?>
                            
                        <li class="sidebar-item">
                            <a class="sidebar-link waves-effect waves-dark sidebar-link" href="review.php"
                                aria-expanded="false">
                                <i class="far fa-comment-alt" aria-hidden="true"></i>
                                <span class="hide-menu">Reviews</span>
                            </a>
                        </li>
                      <?php 
                                }
                              ?>




                        <?php 
                        require("actions/config.php");
                            if (
                                in_array($accesslevel, [3])
                            )  { ?>
                            
                        <li class="sidebar-item">
                            <a class="sidebar-link waves-effect waves-dark sidebar-link" href="staff.php"
                                aria-expanded="false">
                                <i class="fa fa-users" aria-hidden="true"></i>
                                <span class="hide-menu">Staff</span>
                            </a>
                        </li>
                        <?php 
                                }
                              ?>
                        <li class="text-center p-20 upgrade-btn">
                            <a data-bs-toggle="modal" data-bs-target="#logout-modal" role="button" 
                                class="btn d-grid btn-danger text-white" >
                                Logout</a>
                        </li>
                    </ul>

                </nav>
                <!-- End Sidebar navigation -->
            </div>
            <!-- End Sidebar scroll-->
        </aside>
        <!-- ============================================================== -->
        <!-- End Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->
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
                        <h4 class="page-title">Menu Table</h4>
                    </div>
                    <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
                        <div class="d-md-flex">
                            <ol class="breadcrumb ms-auto">
                                <li><a href="dashboard.php" class="fw-normal">Dashboard</a></li>
                                
                            </ol>
                                <a type="button" class="btn btn-danger  d-none d-md-block pull-right ms-3 hidden-xs hidden-sm waves-effect waves-light text-white" data-bs-toggle="modal" data-bs-target="#upload-Menu-modal" >Upload Dish</a>
                        </div>
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
                <div class="row">
                    <div class="col-sm-12">
                        <div class="white-box">
                            <h3 class="box-title">Menu Table</h3>
                            <div class="table-responsive">
                                <table class="table text-nowrap">
                                    <thead>
                                        <tr>
                                            <th class="border-top-0">#</th>
                                            <th class="border-top-0">Dish Name</th>
                                            <th class="border-top-0">Dish Price</th>
                                            <th class="border-top-0">Dish category</th>
                                            <th class="border-top-0">Dietary restrictions</th>
                                            <?php 
                        require("actions/config.php");
                            if (
                                in_array($position, ["AC1-HC", "AC1-AC"])
                            )  { ?>
                            
                                            <th class="border-top-0">Actions</th>
                                <?php 
                                }
                              ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                require("actions/config.php");
                
                
                $sql = "SELECT menu.dish_id, menu.dish_name, menu.dish_price,menu.dish_category , meal_category.category_name, menu.dietary_restrictions FROM menu left join meal_category on menu.dish_category =meal_category.category_id";
                $result = $conn->query($sql);
                
                $record = "";
                
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $dishid = htmlspecialchars($row["dish_id"]);
                        $dishname = htmlspecialchars($row["dish_name"]);
                        $dish_categoryid = htmlspecialchars($row["dish_category"]);
                        $dishprice = htmlspecialchars($row["dish_price"]);
                        $dishcategory = htmlspecialchars($row["category_name"]);
                        $dietaryrestrictions = htmlspecialchars($row["dietary_restrictions"]);
                        ?>

                        <tr>
                                <td> <?php echo $dishid; ?> </td>
                                <td> <?php echo $dishname; ?> </td>
                                <td> <?php echo $dishprice; ?> </td>
                                <td> <?php echo $dishcategory; ?> </td>
                                <td> <?php echo $dietaryrestrictions; ?> </td>
                                <?php 
                        require("actions/config.php");
                            if (
                                in_array($position, ["AC1-HC", "AC1-AC"])
                            )  { ?>
                            
                                <td>
                                <a class='btn btn-danger' href='actions/menu-operations.php?action=delete_dish&mid=<?php echo urlencode($dishid); ?>' role='button'>Delete</a> |
                                <a class='btn btn-warning'type='button' data-bs-toggle='modal' data-bs-target='#update-meal-modal-<?php echo $dishid; ?>' >Update</a> |
                                <a class='btn btn-warning'type='button' data-bs-toggle='modal' data-bs-target='#add-ingredient_used-modal-<?php echo $dishid; ?>' >Add ingredient</a>
                                </td>
                            </tr>
                            <?php 
                                }
                              ?>
                
                      <!-- this is the Update meals modal -->
            <div class="modal fade" id="update-meal-modal-<?php echo $dishid; ?>" tabindex="-1" aria-labelledby="update-meal-modal-title-<?php echo $dishid; ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="update-meal-modal-title-<?php echo $dishid; ?>">Update meal Details-<?php echo $dishid; ?></h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <form action="actions/menu-operations.php" method = 'POST' id="meal" class="needs-validation" novalidate>

                    <!-- Hidden input to store meal ID -->
                    <input type="hidden" name="meal_id" id="meal-id" value="<?php echo $dishid; ?>">

                    <input type="hidden" name="dishlist" value="Update">
                    <div class="row g-3">
                      <div class="col-6">
                        <input type="text" class="form-control" placeholder="<?php if (!empty($dishname)){echo $dishname; }else {echo 'dish name';}?>" aria-label="dish name" name="dishname" >
                      </div>
                      <div class="col-6">
                        <input type="text" class="form-control" placeholder="<?php if (!empty($dishprice)){ echo $dishprice; }else {echo 'dish price';} ?>" aria-label="dish price" name="dishprice">
                      </div>

                      <div class="col-4">
                        <select name="dishcategory" id="dishcategory" class="form-select" required>
                            <option value="dishcategory" disabled>dishcategory</option>
                            
                            <?php
                            require("actions/config.php");
                            $dishcatsql = "SELECT  category_id, category_name FROM meal_category";
                            $dishcatresult = $conn -> query($dishcatsql);

                            if ($dishcatresult -> num_rows > 0){
                                while ($row = $dishcatresult -> fetch_assoc()){
                                    $category_id = $row['category_id'];
                                    $category_name = $row['category_name'];
/* 
                                    echo "<option value=" . $category_id . ">" . $category_name . "</option>"; */

                                    $selected = ($category_id == $dish_categoryid) ? 'selected' : '';
                                          echo "<option value=\"$category_id\" $selected>$category_name</option>";  
                                }
                            }
                               

                            
                            ?>
                        </select>
                      </div>
                     
                  
                      <div class="col-12">
                      <textarea class="form-control" placeholder="<?php if (!empty($preparation)){echo $preparation; }else {echo 'preparation';}  ?>" id="preparation" name="preparation" ></textarea>

                         
                      </div>

                      <div class="col-12">
                          <input type="text" class="form-control"placeholder="<?php if (!empty($dietaryrestrictions)){echo $dietaryrestrictions; }else {echo 'dietaryrestrictions';}  ?>" id="dietaryrestrictions" name="dietaryrestrictions" >
                          
                      </div>


                    </div> 
                    <button type="submit" class="btn btn-outline-danger" name="Update_dish"  id="dish">Update</button>
                  </form>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                
                </div>
              </div>
            </div>
          </div>
  
         
                      <!-- this is the add ingredients modal -->
            <div class="modal fade" id="add-ingredient_used-modal-<?php echo $dishid; ?>" tabindex="-1" aria-labelledby="add-ingredient_used-modal-title-<?php echo $dishid; ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="add-ingredient_used-modal-title-<?php echo $dishid; ?>">Update meal preparation Details-<?php echo $dishid; ?></h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <form action="actions/menu-operations.php" method = 'POST' id="meal" class="needs-validation" novalidate>

                    <!-- Hidden input to store meal ID -->
                    <input type="hidden" name="meal_id" id="meal-id" value="<?php echo $dishid; ?>">

                    <input type="hidden" name="dishlist" value="add_ingredient">
                    <div class="row g-3">
                      

                      <div class="col-4">
                        <select name="ingredientused" id="ingredientused" class="form-select" required>
                            <option value="ingredientused" disabled>Ingredient used</option>
                            
                            <?php
                            require("actions/config.php");
                            $ingredientsql = "SELECT  inventory_id, ingredient_name FROM inventory";
                            $ingredientresult = $conn -> query($ingredientsql);

                            if ($ingredientresult -> num_rows > 0){
                                while ($row = $ingredientresult -> fetch_assoc()){
                                    $ingredient_id = $row['inventory_id'];
                                    $ingredient_name = $row['ingredient_name'];
                                    
                                    echo "<option value=\"$ingredient_id\" >$ingredient_name</option>";  
                                }
                            }else{
                                echo " <option value=' ' disabled selected >  No Ingredients available </option>";
                           
                            }
                               

                            
                            ?>
                        </select>
                      </div>
                     
                  

                      <div class="col-12">
                          <input type="number" class="form-control"placeholder="<?php echo 'quantity used';  ?>" id="quantityused" name="quantityused" required>
                          
                      </div>


                    </div> 
                    <button type="submit" class="btn btn-outline-danger" name="Update_ingredient"  id="ingredient">Add</button>
                  </form>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                
                </div>
              </div>
            </div>
          </div>
  
                <?php
                    }
                } else {
                    $record = "<tr><td colspan='4'>No dishes available</td></tr>";
                }
                
                $conn->close();
                echo $record;
                ?>
                    

                              </td>
                            </tr>
                        
                
                    </tbody>
            
                </table>
                <?php 
                        require("actions/config.php");
                            if (
                                in_array($position, ["AC1-HC", "AC1-AC"])
                            )  { ?>
                            
                                         <!-- Add Menu button that toggles to the add Menu modal -->
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-dish-modal"  >
              Add Dish
            </button>
                            <?php 
                                }
                              ?>
                            </div>
                        </div>
                    </div>
                </div>
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
            <!-- ============================================================== -->
            <!-- End Container fluid  -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- footer -->
            <!-- ============================================================== -->
            <footer class="footer text-center"> 2021 © WishMe brought to you by Group5 
                    <p>Content on this page is reproduced from <a
                    href="https://www.wrappixel.com/">wrappixel.com</a> with permission from the author.</p>

            </footer>
            <!-- ============================================================== -->
            <!-- End footer -->
            <!-- ============================================================== -->
        </div>
        <!-- ============================================================== -->
        <!-- End Page wrapper  -->
        <!-- ============================================================== -->
    </div>

<!-- =============================================== -->
  <!-- =================START OF Menu UPLOAD MODAL============== -->
  <!-- =============================================== -->
    <div class="modal fade" id="upload-Menu-modal" tabindex="-1" aria-labelledby="uploadMenuexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="uploadMenuexampleModalLabel">Upload Dishes</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form action="actions/Dishes_upload.php"  id="signin" method="POST" class="needs-validation" novalidate enctype="multipart/form-data">
          <div class="input-group mb-3">
            <input type="file" class="form-control" id="inputGroupFile02" name="customer_file" accept=".csv" required>
            <label class="input-group-text" for="inputGroupFile02">Upload Dishes csv here</label>
          </div>

              <br>
              <button class="w-100 btn btn-primary btn-lg" name="sign_in" >Upload</button>
            </div>    
          </form>
          
          </div>
       
      </div>
    </div>
  </div>



            <!-- this is the Add Dishes modal -->
            <div class="modal fade" id="add-dish-modal" tabindex="-1" aria-labelledby="dish-modal-title" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="dish-modal-title">Add Dishes</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <form action="actions/menu-operations.php" method = 'POST' id="Dish" class="needs-validation" novalidate>
                    <input type="hidden" name="dishlist" value="Add">
                    <div class="row g-3">
                      <div class="col-6">
                        <input type="text" class="form-control" placeholder="Dish name" aria-label="Dish name" name="dishname" required>
                      </div>
                      <div class="col-6">
                        <input type="number" class="form-control" placeholder="Dish Price" aria-label="Dish Price" name="dishprice" value="0.00" required>
                      </div>
                      <div class="col-6">
                        <select name="dishcategory" id="" class="form-select" aria-label="Dish category" required>
                            <option value="" disabled>Categories</option>
                            <?php
                            require("actions/config.php");
                            $dishcatsql = "SELECT  category_id, category_name FROM meal_category";
                            $dishcatresult = $conn -> query($dishcatsql);

                            if ($dishcatresult -> num_rows > 0){
                                while ($row = $dishcatresult -> fetch_assoc()){
                                    $category_id = $row['category_id'];
                                    $category_name = $row['category_name'];
                                    echo " <option value='$category_id'> $category_name</option>";
                                }
                            }else{
                                echo " <option value=' ' disabled >  No Categories available </option>";
                           
                            }
                            ?>
                        </select>
                      </div>
                      <div class="col-6">
                        <textarea class="form-control" placeholder="preparation" aria-label="preparation" name="preparation"></textarea>

                      </div>
                      <div class="col-6">
                        <input type="text" class="form-control" placeholder="Dietary restrictions" aria-label="Dietary restrictions" name="dietaryrestrictions">
                      </div>
                    

                    </div> 
                    <button type="submit" class="btn btn-outline-danger" name="Add_dish"  id="Dish">Add</button>
                  </form>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                
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
                  
                  <form action="actions/logout.php" method="post">
                  <button type="submit" class="btn btn-outline-danger" name="logout"  id="logout">Logout</button>
                  </form>
              </div>
            </div>
          </div>
        </div>
  <!-- =============================================== -->
  <!-- =================END OF Menu UPLOAD MODAL============== -->
  <!-- =============================================== -->

    <!-- ============================================================== -->
    <!-- End Wrapper -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- All Jquery -->
    <!-- ============================================================== -->
    <script src="plugins/bower_components/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap tether Core JavaScript -->
    <script src="bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/app-style-switcher.js"></script>
    <!--Wave Effects -->
    <script src="js/waves.js"></script>
    <!--Menu sidebar -->
    <script src="js/sidebarmenu.js"></script>
    <!--Custom JavaScript -->
    <script src="js/custom.js"></script>

</body>

</html>