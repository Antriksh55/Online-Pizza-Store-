<?php
    session_start();
    require_once '../config/database.php';
    
    $username = $password = '';
    $error = '';
    
    // Check if admin is already logged in
    if (isset($_SESSION['admin_id'])) {
        header('Location: index.php');
        exit;
    }
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        
        // Validate input
        if (empty($username)) {
            $error = 'Username is required';
        } else if (empty($password)) {
            $error = 'Password is required';
        } else {
            // Connect to database
            $conn = connectDB();
            
            // Check if user exists
            $sql = "SELECT * FROM admins WHERE username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $admin = $result->fetch_assoc();
                
                // Verify password
                if (password_verify($password, $admin['password'])) {
                    // Password is correct, set session
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_name'] = $admin['name'];
                    $_SESSION['admin_username'] = $admin['username'];
                    
                    // Redirect to admin dashboard
                    header('Location: index.php');
                    exit;
                } else {
                    $error = 'Invalid password';
                }
            } else {
                $error = 'Username not found';
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
    <title>Admin Login - Pizza Store</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-gray-800 px-6 py-8 text-white text-center">
            <div class="inline-block p-4 rounded-full bg-gray-700 mb-4">
                <i class="fas fa-pizza-slice text-yellow-500 text-3xl"></i>
            </div>
            <h1 class="text-2xl font-bold">Pizza Store Admin</h1>
            <p class="text-gray-400 mt-2">Login to manage your store</p>
        </div>
        
        <div class="p-6">
            <?php if (!empty($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form action="login.php" method="post">
                <div class="mb-4">
                    <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-user text-gray-400"></i>
                        </div>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>"
                            class="block w-full pl-10 py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Enter your username">
                    </div>
                </div>
                
                <div class="mb-6">
                    <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input type="password" id="password" name="password"
                            class="block w-full pl-10 py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Enter your password">
                    </div>
                </div>
                
                <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-sign-in-alt mr-2"></i> Login
                </button>
            </form>
            
            <div class="mt-6 text-center text-sm text-gray-500">
                <p>Don't have admin access? Contact the site administrator.</p>
            </div>
        </div>
    </div>
    
    <script>
        // Focus on the username field when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('username').focus();
        });
    </script>
</body>
</html> 