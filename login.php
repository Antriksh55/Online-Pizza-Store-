<?php
    $pageTitle = "Login";
    require_once 'includes/functions.php';
    
    // Redirect if already logged in
    if (isLoggedIn()) {
        header("Location: index.php");
        exit();
    }
    
    $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '';
    $email = $password = '';
    $error = '';
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        
        // Validate form inputs
        if (empty($email) || empty($password)) {
            $error = "All fields are required";
        } else {
            // Check user credentials
            $conn = connectDB();
            
            $sql = "SELECT * FROM users WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                if (password_verify($password, $user['password'])) {
                    // Login successful
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    
                    // Redirect to intended page or home
                    if (!empty($redirect)) {
                        header("Location: $redirect");
                    } else {
                        header("Location: index.php");
                    }
                    exit();
                } else {
                    $error = "Invalid email or password";
                }
            } else {
                $error = "Invalid email or password";
            }
            
            $conn->close();
        }
    }
    
    require_once 'layouts/header.php';
?>

<div class="flex justify-center">
    <div class="bg-white rounded-lg shadow-md p-8 max-w-md w-full">
        <h1 class="text-2xl font-bold mb-6 text-center">Login to Your Account</h1>
        
        <?php if (!empty($error)): ?>
            <div class="mb-4 bg-red-100 text-red-700 p-3 rounded">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="post" action="login.php<?php echo !empty($redirect) ? '?redirect=' . urlencode($redirect) : ''; ?>">
            <div class="mb-4">
                <label for="email" class="block text-gray-700 font-medium mb-2">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required 
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            
            <div class="mb-6">
                <label for="password" class="block text-gray-700 font-medium mb-2">Password</label>
                <input type="password" id="password" name="password" required 
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            
            <button type="submit" class="w-full bg-red-600 text-white py-2 rounded-lg font-semibold hover:bg-red-700 transition">
                Login
            </button>
        </form>
        
        <div class="mt-6 text-center">
            <p class="text-gray-600">Don't have an account? <a href="register.php<?php echo !empty($redirect) ? '?redirect=' . urlencode($redirect) : ''; ?>" class="text-red-600 hover:underline">Register here</a></p>
        </div>
    </div>
</div>

<?php require_once 'layouts/footer.php'; ?> 