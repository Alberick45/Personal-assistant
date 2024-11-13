<?php
session_start(); // Start the session
require_once("config.php");

if (isset($_POST['family_create'])) {
    // Assign form inputs to variables or default to NULL
    $user_id = !empty($_POST['user']) ? $_POST['user'] : null;
    $name = !empty($_POST['name']) ? $_POST['name'] : null;
    $creation_date = !empty($_POST['creation_date']) ? $_POST['creation_date'] : null;
    $family_security_code = !empty($_POST['security_code']) ? password_hash($_POST['security_code'],PASSWORD_BCRYPT) : null;
    $family_description = !empty($_POST['description']) ? $_POST['description'] : null;

    // Check if the security code length is at least 8 characters
    if (strlen($family_security_code) < 8) {
        echo "<script>
                alert('Security code must be at least 8 characters long');
                window.location.href = '../../profile.php';
              </script>";
        exit();
    }

    // Check if the family name already exists
    $checkFamilyQuery = "SELECT id FROM family WHERE name = ?";
    $stmt = $conn->prepare($checkFamilyQuery);
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Family name already taken
        echo "<script>
                alert('Family name already taken. You can try adding a location to differentiate it.');
                window.location.href = '../../profile.php';
              </script>";
        $stmt->close();
    } else {
        // Prepare the query to insert the new family
        $insertFamilyQuery = "INSERT INTO family (name, creation_date, family_security_code, family_description) 
                                VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insertFamilyQuery);
        $stmt->bind_param("ssss",  $name, $creation_date, $family_security_code, $family_description);

        // Execute the insert query
        if ($stmt->execute()) {
            // Family created successfully
            $_SESSION['message'] = "Family " . $name . " created successfully!";
            header("Location: ../../family.php");
            exit();
        } else {
            // Handle SQL execution errors
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}
