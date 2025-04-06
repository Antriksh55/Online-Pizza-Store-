<?php
require_once '../includes/functions.php';

// This file is for creating an initial admin account
// After creating the admin account, this file should be deleted for security reasons

// Default admin credentials (change these)
$admin_name = "Administrator";
$admin_username = "admin";
$admin_password = "admin123"; // This should be changed immediately after first login

// Check if we already have an admin
$conn = connectDB();
$sql = "SELECT COUNT(*) as count FROM admins";
$result = $conn->query($sql);

if ($result === false) {
    // Table doesn't exist, create it
    $sql = "CREATE TABLE IF NOT EXISTS admins (
        id INT(11) NOT NULL AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    )";
    
    if ($conn->query($sql) === false) {
        die("Error creating admins table: " . $conn->error);
    }
    
    $admin_count = 0;
} else {
    $row = $result->fetch_assoc();
    $admin_count = $row['count'];
}

// Response message
$message = "";

// Check if we should add an admin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Use form inputs if provided
    $admin_name = !empty($_POST['name']) ? trim($_POST['name']) : $admin_name;
    $admin_username = !empty($_POST['username']) ? trim($_POST['username']) : $admin_username;
    $admin_password = !empty($_POST['password']) ? $_POST['password'] : $admin_password;
    
    // Hash the password
    $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
    
    // Insert the admin
    $sql = "INSERT INTO admins (name, username, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $admin_name, $admin_username, $hashed_password);
    
    if ($stmt->execute()) {
        $message = "Admin account created successfully. Please delete this file for security.";
        $admin_count++; // Increment for display
    } else {
        $message = "Error creating admin account: " . $stmt->error;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin Account - Pizza Store</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full">
        <div class="text-center mb-6">
            <i class="fas fa-user-shield text-gray-700 text-4xl mb-4"></i>
            <h1 class="text-2xl font-bold text-gray-800">Create Admin Account</h1>
            <p class="text-gray-600">Pizza Store Management System</p>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="mb-4 p-3 rounded <?php echo strpos($message, 'Error') !== false ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($admin_count > 0): ?>
            <div class="bg-yellow-100 text-yellow-800 p-3 rounded mb-4">
                <p><strong>Warning:</strong> There are already <?php echo $admin_count; ?> admin account(s) in the database.</p>
            </div>
        <?php endif; ?>
        
        <form method="post" action="create_admin.php">
            <div class="mb-4">
                <label for="name" class="block text-gray-700 font-medium mb-2">Admin Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($admin_name); ?>" 
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-800">
            </div>
            
            <div class="mb-4">
                <label for="username" class="block text-gray-700 font-medium mb-2">Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($admin_username); ?>" 
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-800">
            </div>
            
            <div class="mb-6">
                <label for="password" class="block text-gray-700 font-medium mb-2">Password</label>
                <input type="password" id="password" name="password" value="<?php echo htmlspecialchars($admin_password); ?>" 
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-800">
                <p class="text-sm text-gray-500 mt-1">Default: admin123 (change this immediately after login)</p>
            </div>
            
            <button type="submit" class="w-full bg-gray-800 text-white py-2 rounded-lg font-semibold hover:bg-gray-700 transition">
                Create Admin Account
            </button>
        </form>
        
        <div class="mt-6 text-center">
            <a href="login.php" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-arrow-left mr-1"></i> Back to Login
            </a>
        </div>
        
        <div class="mt-8 pt-4 border-t border-gray-200 text-sm text-gray-500">
            <p><strong>Important:</strong> Delete this file (create_admin.php) after creating the admin account for security purposes.</p>
        </div>
    </div>
</body>
</html> 