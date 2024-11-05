<?php
session_start();
require_once("config.php");

if (isset($_POST['login'])) {
    // Assign form inputs to variables or default to NULL
    $username = !empty($_POST['username']) ? $_POST['username'] : null;
    $password = !empty($_POST['password']) ? $_POST['password'] : null;

    // Check password length
    if (strlen($password) < 8) {
        echo "<script>
                alert('Password must be at least 8 characters long');
                window.location.href = '../../index.php';
              </script>";
        exit();
    }

    // Prepare the SQL query to check if the user exists
    $checkUserQuery = "SELECT id, username, password,profile_pic FROM users WHERE username = ?";
    $stmt = $conn->prepare($checkUserQuery);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $results = $stmt->get_result();

    if ($results->num_rows > 0) {
        // User exists, fetch the data
        $row = $results->fetch_assoc();
        $user_id = $row['id'];
        $stored_password = $row['password'];

        // Verify the password
        if (password_verify($password, $stored_password)) {
            // Store session variables
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $row['username'];
            $_SESSION['profile_pic'] = $row['profile_pic'];
            $_SESSION['message'] = "User " . $row['username'] . " logged in successfully!";

            // Update last login time
            $updateQuery = "UPDATE users SET last_login = NOW() WHERE id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("i", $user_id);
            $updateStmt->execute();

            // Redirect to the dashboard or homepage
            header("Location: ../../index.php");
            exit();
        } else {
            echo "<script>
                    alert('Incorrect password');
                    window.location.href = '../../index.php';
                  </script>";
        }
    } else {
        echo "<script>
                alert('User not found');
                window.location.href = '../../index.php';
              </script>";
    }
}
