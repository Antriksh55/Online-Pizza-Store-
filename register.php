<?php
session_start();
$pageTitle = "Register";
require_once 'includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '';
$name = $email = $phone = $address = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    // Validate form inputs
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password) || empty($phone) || empty($address)) {
        $error = "All fields are required";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {
        try {
            // Check if email already exists
            $conn = connectDB();
            
            $sql = "SELECT id FROM users WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = "Email already exists. Please use a different email or login.";
            } else {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user
                $sql = "INSERT INTO users (name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssss", $name, $email, $hashed_password, $phone, $address);
                
                if ($stmt->execute()) {
                    // Registration successful, log the user in
                    $user_id = $conn->insert_id;
                    
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    
                    // Redirect to intended page or home
                    if (!empty($redirect)) {
                        header("Location: $redirect");
                    } else {
                        header("Location: index.php");
                    }
                    exit();
                } else {
                    $error = "Registration failed: " . $stmt->error;
                }
            }
            
            $conn->close();
        } catch (Exception $e) {
            $error = "An error occurred during registration. Please try again later.";
            error_log("Registration error: " . $e->getMessage());
        }
    }
}

include 'includes/header.php';
?>

<div class="flex justify-center">
    <div class="bg-white rounded-lg shadow-md p-8 max-w-md w-full">
        <h1 class="text-2xl font-bold mb-6 text-center">Create an Account</h1>
        
        <?php if (!empty($error)): ?>
            <div class="mb-4 bg-red-100 text-red-700 p-3 rounded">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="post" action="register.php<?php echo !empty($redirect) ? '?redirect=' . urlencode($redirect) : ''; ?>">
            <div class="mb-4">
                <label for="name" class="block text-gray-700 font-medium mb-2">Full Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required 
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            
            <div class="mb-4">
                <label for="email" class="block text-gray-700 font-medium mb-2">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required 
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            
            <div class="mb-4">
                <label for="phone" class="block text-gray-700 font-medium mb-2">Phone Number</label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required 
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            
            <div class="mb-4">
                <label for="address" class="block text-gray-700 font-medium mb-2">Delivery Address</label>
                <textarea id="address" name="address" required rows="3"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"><?php echo htmlspecialchars($address); ?></textarea>
            </div>
            
            <div class="mb-4">
                <label for="password" class="block text-gray-700 font-medium mb-2">Password</label>
                <input type="password" id="password" name="password" required 
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                <p class="text-sm text-gray-500 mt-1">Password must be at least 6 characters</p>
            </div>
            
            <div class="mb-6">
                <label for="confirm_password" class="block text-gray-700 font-medium mb-2">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required 
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            
            <button type="submit" class="w-full bg-red-600 text-white py-2 rounded-lg font-semibold hover:bg-red-700 transition">
                Register
            </button>
        </form>
        
        <div class="mt-6 text-center">
            <p class="text-gray-600">Already have an account? <a href="login.php<?php echo !empty($redirect) ? '?redirect=' . urlencode($redirect) : ''; ?>" class="text-red-600 hover:underline">Login here</a></p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 