<?php
require_once '../includes/functions.php';

// Clear admin session variables
$_SESSION['admin_id'] = null;
$_SESSION['admin_name'] = null;
$_SESSION['admin_username'] = null;

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: login.php");
exit();
?> 