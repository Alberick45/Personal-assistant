<?php 

require("config.php");
session_start();

// Ensure the connection is established
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $id = $conn->real_escape_string($_POST['user_id']); // Change 'id' to 'user_id' to match the input name
    $old_pp = $_POST['old_pp'];

    // Update full name
    if (isset($_POST['full_name']) && !empty($_POST['full_name'])) {
        $updated_name = $conn->real_escape_string($_POST['full_name']);
        $sql = "UPDATE users SET name = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('si', $updated_name, $id);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Update phone number
    if (isset($_POST['phone_number']) && !empty($_POST['phone_number'])) {
        $updated_phone_number = $conn->real_escape_string($_POST['phone_number']);
        $sql = "UPDATE users SET phone = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('si', $updated_phone_number, $id);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Update birthdate (assuming it's an age in the database)
    if (isset($_POST['birthdate']) && !empty($_POST['birthdate'])) {
        $updated_birthdate = $conn->real_escape_string($_POST['birthdate']);
        // Convert to timestamp if you store as timestamp or date format
        $updated_birthdate = strtotime($updated_birthdate);
        $sql = "UPDATE users SET age = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('si', $updated_birthdate, $id);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Update username
    if (isset($_POST['username']) && !empty($_POST['username'])) {
        $username = $conn->real_escape_string($_POST['username']);
        $sql = "UPDATE users SET username = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('si', $username, $id);
            $stmt->execute();
            $stmt->close();
        }
    }
    // Update financial goal
    if (isset($_POST['goal']) && !empty($_POST['goal'])) {
        $goal = $conn->real_escape_string($_POST['goal']);
        $sql = "UPDATE users SET financial_goal = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('si', $goal, $id);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Update profile picture
    if (isset($_FILES['pp']['name']) && !empty($_FILES['pp']['name'])) {
        $img_name = $_FILES['pp']['name'];
        $tmp_name = $_FILES['pp']['tmp_name'];
        $error = $_FILES['pp']['error'];

        if ($error === 0) {
            $img_ex = pathinfo($img_name, PATHINFO_EXTENSION);
            $img_ex_to_lc = strtolower($img_ex);

            $allowed_exs = ['jpg', 'jpeg', 'png'];
            if (in_array($img_ex_to_lc, $allowed_exs)) {
                $new_img_name = uniqid($id, true) . '.' . $img_ex_to_lc;
                $img_upload_path = '../images/users/' . $new_img_name;

                // Delete old profile pic
                $old_pp_des = "../images/users/$old_pp";
                if (file_exists($old_pp_des)) {
                    unlink($old_pp_des); // Deletes old profile picture
                }

                // Move the new profile picture
                move_uploaded_file($tmp_name, $img_upload_path);

                // Update the Database
                $sql = "UPDATE users SET profile_pic=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param('si', $new_img_name, $id);
                    $stmt->execute();
                    $stmt->close();
                }

                header("Location: ../../profile.php?success=Your account has been updated successfully");
                exit;
            } else {
                $em = "You can't upload files of this type";
                header("Location: ../../profile.php?error=$em");
                exit;
            }
        } else {
            $em = "Unknown error occurred!";
            header("Location: ../../profile.php?error=$em");
            exit;
        }
    }

    // Redirect to profile page if no updates were made
    header('Location: ../../profile.php');
    $conn->close();
}
?>
