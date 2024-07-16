<?php
// Include config file
require_once "config.php";

// Check if ID is provided
if (isset($_GET['id'])) {
    // Prepare and execute the update statement
    $id = $_GET['id'];
    $stmt = $mysqli->prepare("UPDATE MEETING SET IS_ACCEPTED = 1 WHERE ID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Check if the update was successful
    if ($stmt->affected_rows === 1) {
        echo "Success";
    } else {
        echo "Failed";
    }

    // Close statement
    $stmt->close();
}

// Close connection
$mysqli->close();
?>
