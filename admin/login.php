<?php
    $pageTitle = "Admin Login";
    require_once '../includes/functions.php';
    
    // Redirect if already logged in as admin
    if (isAdmin()) {
        header("Location: index.php");
        exit();
    }
    
    $username = '';
    $error = '';
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        
        // Validate form inputs
        if (empty($username) || empty($password)) {
            $error = "All fields are required";
        } else {
            // Check admin credentials
            $conn = connectDB();
            
            $sql = "SELECT * FROM admins WHERE username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $admin = $result->fetch_assoc();
                
                if (password_verify($password, $admin['password'])) {
                    // Login successful
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_name'] = $admin['name'];
                    $_SESSION['admin_username'] = $admin['username'];
                    
                    // Redirect to admin dashboard
                    header("Location: index.php");
                    exit();
                } else {
                    $error = "Invalid username or password";
                }
            } else {
                $error = "Invalid username or password";
            }
            
            $conn->close();
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Pizza Store</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full">
        <div class="text-center mb-8">
            <i class="fas fa-pizza-slice text-red-600 text-4xl mb-4"></i>
            <h1 class="text-2xl font-bold">Admin Login</h1>
            <p class="text-gray-600">Pizza Store Management System</p>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="mb-4 bg-red-100 text-red-700 p-3 rounded">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="post" action="login.php">
            <div class="mb-4">
                <label for="username" class="block text-gray-700 font-medium mb-2">Username</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                        <i class="fas fa-user"></i>
                    </span>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required 
                        class="w-full pl-10 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-800">
                </div>
            </div>
            
            <div class="mb-6">
                <label for="password" class="block text-gray-700 font-medium mb-2">Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" id="password" name="password" required 
                        class="w-full pl-10 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-800">
                </div>
            </div>
            
            <button type="submit" class="w-full bg-gray-800 text-white py-2 rounded-lg font-semibold hover:bg-gray-700 transition">
                Login
            </button>
        </form>
        
        <div class="mt-6 text-center">
            <a href="../index.php" class="text-gray-600 hover:text-red-600">
                <i class="fas fa-arrow-left mr-1"></i> Back to Website
            </a>
        </div>
    </div>
</body>
</html> 