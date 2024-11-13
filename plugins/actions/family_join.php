<?php
session_start();
require_once("config.php");

if (isset($_POST['submit_join'])) {
    $user_id = $_POST['user'];
    $name = $_POST['name'];
    $passcode = $_POST['passcode'];

    // Check if the family exists and the passcode matches
    $checkFamilyQuery = "SELECT id, family_security_code FROM family WHERE name = ?";
    $stmt = $conn->prepare($checkFamilyQuery);
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Family exists, now check if the passcode is correct
        $stmt->bind_result($family_id, $stored_security_code);
        $stmt->fetch();
        
        if (password_verify($passcode, $stored_security_code)) {  // Assuming passcodes are hashed
            // Update the user's family_id in the users table
            $updateUserFamilyQuery = "UPDATE users SET family_id = ? WHERE id = ?";
            $stmt = $conn->prepare($updateUserFamilyQuery);
            $stmt->bind_param('ii', $family_id, $user_id);
            $stmt->execute();
            $stmt->close();

            // Optionally, add the user to a 'family_members' table if necessary
            $insertFamilyMemberQuery = "INSERT INTO family_members (family_id, user_id) VALUES (?, ?)";
            $stmt = $conn->prepare($insertFamilyMemberQuery);
            $stmt->bind_param('ii', $family_id, $user_id);
            $stmt->execute();
            $stmt->close();

            // Success message
            $_SESSION['message'] = "You have successfully joined the family!";
            $_SESSION['family'] = $family_id;
            header("Location: ../../profile.php");
            exit();
        } else {
            // Incorrect passcode
            echo "<script>
                    alert('Incorrect passcode');
                    window.location.href = '../../profile.php';
                  </script>";
        }
    } else {
        // Family not found
        echo "<script>
                alert('Family not found');
                window.location.href = '../../profile.php';
              </script>";
    }
}
?>
