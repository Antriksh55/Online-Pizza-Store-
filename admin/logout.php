<?php
session_start();

// Check if admin is logged in
if (isset($_SESSION['admin_id'])) {
    // Remove admin session variables
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin_name']);
    unset($_SESSION['admin_username']);
    
    // Destroy the session
    session_destroy();
}

// Redirect to login page
header("Location: login.php");
exit();
?> 