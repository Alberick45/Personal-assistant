
<?php
require("config.php");
session_start();
/* 
$staffid = $_SESSION['staff_id']; */
function adddishes() {
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['dishname'], $_POST['dishprice'], $_POST['dishcategory'])) {
            // Escape and assign POST data to variables
            $dishname = $conn->real_escape_string($_POST['dishname']);
            $dishprice = $conn->real_escape_string($_POST['dishprice']);
            $dishcategory = $conn->real_escape_string($_POST['dishcategory']);

            // Additional fields
            $dietary = isset($_POST['dietaryrestrictions']) ? $conn->real_escape_string($_POST['dietaryrestrictions']) : 'None';
            $preparation = isset($_POST['preparation']) ? $conn -> real_escape_string($_POST['preparation']) : 'None';

        // check if dish already exists
        $checksql = "select * from menu where dish_name = '$dishname'";
        $results = $conn -> query($checksql);
        if ($results -> num_rows > 0){
            echo "<script>
            alert('dish already exists');
            window.location.href = '../menu.php';
            </script>";
        } else {

            // SQL query for inserting dish details including additional fields
            $sql = "call add_meals('$dishname', '$dishprice', $dishcategory, '$preparation', '$dietary')";
            $conn->query($sql);

            $_SESSION['message'] = "successful";
            
        } }else {
            $_SESSION['message'] = "Please fill all fields.";
        }
    }
    header('Location: ../menu.php');
    $conn->close();
}

function add_ingredient() {
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $mealid= $conn->real_escape_string($_POST['meal_id']);
        if (isset($_POST['quantityused'], $_POST['ingredientused'])) {
            // Escape and assign POST data to variables
            $quantityused = $conn->real_escape_string($_POST['quantityused']);
            $ingredientused = $conn->real_escape_string($_POST['ingredientused']);


        

            // SQL query for inserting dish details including additional fields
            $sql = "call meal_ingredient_add($mealid,$ingredientused, $quantityused)";
            $conn->query($sql);

            
            
        } }else {
            $_SESSION['message'] = "Please fill all fields.";
        }
        header('Location: ../menu.php');
        $conn->close();
    }
    


            

      

function deletedish($mid) {
    global $conn ;
    $sql = "call delete_meal($mid)";
    $conn -> query($sql);
     header('Location: ../menu.php');
    
    $conn -> close();
}

function updatedish() {
    global $conn /* $staffid */;
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $dishid = $conn->real_escape_string($_POST['meal_id']);
            
            if (isset($_POST['dishname'])  && !empty($_POST['dishname'])) {
                $dishname = $conn->real_escape_string($_POST['dishname']) ;
                $sql = "call update_meals_text('dish_name','$dishname', $dishid)";
                $conn ->query($sql);
            }
            if (isset($_POST['dishprice'])  && !empty($_POST['dishprice'])) {
                $dishprice = $_POST['dishprice'];
                $sql = "call update_meals_decimal('dish_price','$dishprice',$dishid)";
                $conn ->query($sql);
            }
            if (isset($_POST['dishcategory'])  && !empty($_POST['dishcategory'])) {
                $dishcategory = $_POST['dishcategory'];
                $sql = "call update_meals_int ('dish_category', $dishcategory, $dishid)";
                $conn ->query($sql);
            }

                if (isset($_POST['preparation'])  && !empty($_POST['preparation'])) {
                    $preparation = $conn->real_escape_string($_POST['preparation']);
                    $sql = "call update_meals_text('preparation_process','$preparation',$dishid)";
                    $conn ->query($sql);
            }
                if (isset($_POST['dietaryrestrictions'])  && !empty($_POST['dietaryrestrictions'])) {
                    $dietaryrestrictions = $conn->real_escape_string($_POST['dietaryrestrictions']);
                    $sql = "call update_meals_text('dietary_restrictions','$dietaryrestrictions',$dishid)";
                    $conn ->query($sql);
             }
                header('Location: ../menu.php');
             $conn->close();
        } 
        
}
   


/* Function calls */
if (isset($_POST['dishlist'])) {
    if ($_POST['dishlist'] === "Add") {
        adddishes();
        exit();
    } elseif ($_POST['dishlist'] === "Update"){
            updatedish();
            exit();
        
    } elseif ($_POST['dishlist'] === "add_ingredient"){
        add_ingredient();
            exit();
        
    } 
    else {
        $_SESSION['message'] =  "Invalid function call: " . $_POST['dishlist'];
        header('Location: ../menu.php');
        
    }
} 

else {

    if (isset($_GET["action"])) {
    
        if (isset($_GET['mid']) && $_GET["action"] === "delete_dish") {
            $dish = htmlspecialchars($_GET['mid']);
            deletedish($dish);
             exit();
        }
    }else{
   $_SESSION['message'] =  "Form submission error.";
   header('Location: ../menu.php');
    }
}
























