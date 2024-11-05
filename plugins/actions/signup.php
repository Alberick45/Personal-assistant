<?php
session_start(); // Start the session
require_once("config.php");

if (isset($_POST['sign_up'])) {
    // Assign form inputs to variables or default to NULL
    $full_name = !empty($_POST['name']) ? $_POST['name'] : null;
    $birthdate = !empty($_POST['birthdate']) ? $_POST['birthdate'] : null;
    $phone = !empty($_POST['phone']) ? $_POST['phone'] : null;
    $username = !empty($_POST['username']) ? $_POST['username'] : null;
    $password = !empty($_POST['password']) ? $_POST['password'] : null;

    // Check if the password length is at least 8 characters
    if (strlen($password) < 8) {
        echo "<script>
                alert('Password must be at least 8 characters long');
                window.location.href = '../../index.php';
              </script>";
        exit();
    }

    // Check if the username already exists
    $checkUserQuery = "SELECT id FROM users WHERE username = ?";
    $stmt = $conn->prepare($checkUserQuery);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Username is taken
        echo "<script>
                alert('Username already taken');
                window.location.href = '../../index.php';
              </script>";
        $stmt->close();
    } else {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Prepare the query to insert the new user
        $insertQuery = "INSERT INTO users (name, birthdate, phone, username, password, last_login) 
                        VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("sssss", $full_name, $birthdate, $phone, $username, $hashedPassword);

        // Execute the insert query
        if ($stmt->execute()) {
            // Retrieve the newly inserted user ID
            $userIdQuery = "SELECT id,profile_pic FROM users WHERE username = ?";
            $stmt = $conn->prepare($userIdQuery);
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $user_id = $row["id"];
                $profile_pic = $row["profile_pic"];

                // Store session variables
                $_SESSION["user_id"] = $user_id;
                $_SESSION["username"] = $username;
                $_SESSION["profile_pic"] = $profile_pic;
                $_SESSION['message'] = "New user " . $username . " created successfully!";

                // Redirect to the index page
                header("Location: ../../index.php");
                exit();
            }
        } else {
            // Handle SQL execution errors
            echo "Error: " . $stmt->error;
        }
    }
}
