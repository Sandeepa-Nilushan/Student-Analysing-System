<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['role'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

// Check the role and redirect accordingly
switch ($_SESSION['role']) {
    case 'STUDENT':
        header("Location: student_result.php");
        break;
    case 'PARENT':
        header("Location: parent_result.php");
        break;
    case 'TEACHER':
        header("Location: teacher_dashboard.php");
        break;
    case 'ADMIN':
        header("Location: users.php");
        break;
    default:
        // If the role is not recognized, redirect to login page
        header("Location: login.php");
        break;
}
?>