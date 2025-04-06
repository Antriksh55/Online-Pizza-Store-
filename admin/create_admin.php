<?php
require_once '../config/database.php';

// This file is for creating an initial admin account
// After creating the admin account, this file should be deleted for security reasons

// Default admin credentials
$admin_username = 'admin';
$admin_password = 'admin123'; // This should be changed immediately after first login
$admin_name = 'Admin User';

// Connect to database
$conn = connectDB();

// Check if admin table exists and has any users
$sql = "SELECT COUNT(*) as count FROM admins";
$result = $conn->query($sql);

if ($result && $result->fetch_assoc()['count'] > 0) {
    echo '<div style="max-width: 600px; margin: 50px auto; padding: 20px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px;">';
    echo '<h2 style="margin-top: 0;">Admin Account Already Exists</h2>';
    echo '<p>There is already at least one admin account in the database. For security reasons, this script will not create another admin account while existing accounts are present.</p>';
    echo '<p>If you need to create a new admin account, please use the admin panel or contact your database administrator.</p>';
    echo '<p><strong>Warning:</strong> For security purposes, please delete this file (create_admin.php) from your server after you have created your initial admin account.</p>';
    echo '<p><a href="login.php" style="color: #721c24; text-decoration: underline;">Go to Admin Login</a></p>';
    echo '</div>';
    exit;
}

// Create admin user
$success = false;
$error = '';

try {
    // Hash the password for security
    $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
    
    // Check if table exists, if not create it
    $sql = "CREATE TABLE IF NOT EXISTS admins (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($sql)) {
        throw new Exception("Error creating admin table: " . $conn->error);
    }
    
    // Insert admin user
    $sql = "INSERT INTO admins (username, password, name) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $admin_username, $hashed_password, $admin_name);
    
    if ($stmt->execute()) {
        $success = true;
    } else {
        throw new Exception("Error creating admin user: " . $stmt->error);
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin Account - Pizza Store</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        .warning {
            background-color: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            border: 1px solid #ffeeba;
        }
        h1 {
            color: #333;
            margin-top: 0;
        }
        .credentials {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .btn {
            display: inline-block;
            background-color: #4e73df;
            color: #fff;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .btn:hover {
            background-color: #2e59d9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Create Admin Account</h1>
        
        <?php if ($success): ?>
            <div class="success">
                <h2>Success!</h2>
                <p>An admin account has been created successfully.</p>
            </div>
            
            <div class="credentials">
                <h3>Admin Credentials</h3>
                <p><strong>Username:</strong> <?php echo htmlspecialchars($admin_username); ?></p>
                <p><strong>Password:</strong> <?php echo htmlspecialchars($admin_password); ?></p>
            </div>
            
            <p>You can now log in to the admin panel with these credentials.</p>
            
            <div class="warning">
                <h3>Important Security Warning</h3>
                <p><strong>Please change your password immediately after the first login.</strong></p>
                <p>For security purposes, please <strong>delete this file</strong> (create_admin.php) from your server after you have logged in.</p>
            </div>
            
            <a href="login.php" class="btn">Go to Admin Login</a>
            
        <?php else: ?>
            <div class="error">
                <h2>Error</h2>
                <p>Failed to create admin account. The following error occurred:</p>
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
            
            <p>Please check your database configuration and try again. If the problem persists, contact your system administrator.</p>
        <?php endif; ?>
    </div>
</body>
</html> 