
<?php
require("config.php");
session_start();

$staffid = $_SESSION['staff_id'];
function addingredients() {
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['ingredientname'], $_POST['ingredientprice'], $_POST['ingredientquantity'])) {
            // Escape and assign POST data to variables
            $ingredientname = $conn->real_escape_string($_POST['ingredientname']);
            $ingredientprice = $conn->real_escape_string($_POST['ingredientprice']);
            $ingredientquantity = $conn->real_escape_string($_POST['ingredientquantity']);


        // check if dish already exists
        $checksql = "select * from inventory where ingredient_name = '$ingredientname'";
        $results = $conn -> query($checksql);
        if ($results -> num_rows > 0){
            echo "<script>
            alert('dish already exists');
            window.location.href = '../inventory.php';
            </script>";
        } else {

            // SQL query for inserting dish details including additional fields
            $sql = "call add_ingredient('$ingredientname', '$ingredientprice', $ingredientquantity)";
            $conn->query($sql);

            
            
        } }else {
            $_SESSION['message'] = "Please fill all fields.";
        }
    }
    header('Location: ../inventory.php');
    $conn->close();
}



      
/* Function calls */
if (isset($_POST['ingredientlist'])) {
    if ($_POST['ingredientlist'] === "Add") {
        addingredients();
        exit();
    } 
    else {
        $_SESSION['message'] =  "Invalid function call: " . $_POST['ingredientlist'];
        header('Location: ../inventory.php');
        
    }
} 

else {
   $_SESSION['message'] =  "Form submission error.";
   header('Location: ../inventory.php');
}




