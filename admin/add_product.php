<?php
    $pageTitle = "Add Product";
    require_once '../includes/functions.php';
    
    // Require admin login
    requireAdmin();
    
    // Initialize variables
    $name = '';
    $description = '';
    $price = '';
    $category = '';
    $is_available = 1;
    $errors = [];
    $categories = ['pizza', 'sides', 'drinks', 'desserts'];
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get and validate form data
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = trim($_POST['price']);
        $category = $_POST['category'];
        $is_available = isset($_POST['is_available']) ? 1 : 0;
        
        // Validate required fields
        if (empty($name)) {
            $errors['name'] = 'Product name is required';
        }
        
        if (empty($description)) {
            $errors['description'] = 'Product description is required';
        }
        
        if (empty($price)) {
            $errors['price'] = 'Product price is required';
        } elseif (!is_numeric($price) || $price <= 0) {
            $errors['price'] = 'Price must be a positive number';
        }
        
        if (empty($category)) {
            $errors['category'] = 'Category is required';
        }
        
        // Handle image upload
        $image_url = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/products/';
            
            // Create directory if it doesn't exist
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Get file info
            $file_tmp = $_FILES['image']['tmp_name'];
            $file_name = $_FILES['image']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            // Check file extension
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (!in_array($file_ext, $allowed_exts)) {
                $errors['image'] = 'Only JPG, JPEG, PNG, and GIF files are allowed';
            } else {
                // Generate unique filename
                $new_file_name = uniqid('product_') . '.' . $file_ext;
                $upload_path = $upload_dir . $new_file_name;
                
                // Move file to upload directory
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    $image_url = $new_file_name;
                } else {
                    $errors['image'] = 'Failed to upload image';
                }
            }
        } else {
            // Image is required
            if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                if ($_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
                    $errors['image'] = 'Product image is required';
                } else {
                    $errors['image'] = 'Image upload failed: ' . $_FILES['image']['error'];
                }
            }
        }
        
        // If no errors, insert product into database
        if (empty($errors)) {
            $conn = connectDB();
            
            $sql = "INSERT INTO products (name, description, price, category, image_url, is_available) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssdssi", $name, $description, $price, $category, $image_url, $is_available);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Product added successfully.";
                header("Location: products.php");
                exit;
            } else {
                $errors['general'] = "Failed to add product: " . $conn->error;
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
<body class="bg-gray-100 min-h-screen">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="bg-gray-800 text-white w-64 py-4 flex flex-col">
            <div class="px-4 py-4 border-b border-gray-700">
                <div class="flex items-center">
                    <i class="fas fa-pizza-slice text-yellow-500 text-xl mr-2"></i>
                    <h1 class="text-xl font-bold">Pizza Admin</h1>
                </div>
                <p class="text-sm text-gray-400 mt-1">Welcome, <?php echo $_SESSION['admin_name']; ?></p>
            </div>
            
            <nav class="px-2 py-4 flex-grow">
                <ul>
                    <li class="mb-1">
                        <a href="index.php" class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 rounded">
                            <i class="fas fa-tachometer-alt w-5 mr-2"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="mb-1">
                        <a href="orders.php" class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 rounded">
                            <i class="fas fa-shopping-cart w-5 mr-2"></i>
                            <span>Orders</span>
                        </a>
                    </li>
                    <li class="mb-1">
                        <a href="products.php" class="flex items-center px-4 py-2 bg-gray-700 rounded text-white">
                            <i class="fas fa-box w-5 mr-2"></i>
                            <span>Products</span>
                        </a>
                    </li>
                    <li class="mb-1">
                        <a href="customers.php" class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 rounded">
                            <i class="fas fa-users w-5 mr-2"></i>
                            <span>Customers</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="mt-auto px-4 py-2 border-t border-gray-700">
                <a href="logout.php" class="flex items-center text-gray-300 hover:text-white">
                    <i class="fas fa-sign-out-alt mr-2"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <header class="bg-white shadow">
                <div class="py-4 px-6 flex justify-between items-center">
                    <h2 class="text-xl font-semibold text-gray-800">Add New Product</h2>
                    <a href="products.php" class="text-indigo-600 hover:text-indigo-900">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Products
                    </a>
                </div>
            </header>
            
            <main class="p-6">
                <?php if (isset($errors['general'])): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?php echo $errors['general']; ?>
                    </div>
                <?php endif; ?>
                
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg font-semibold">Product Information</h3>
                    </div>
                    
                    <div class="p-6">
                        <form action="add_product.php" method="post" enctype="multipart/form-data">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <div class="mb-4">
                                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                            Product Name <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required
                                            class="block w-full rounded-md border-gray-300 shadow-sm py-2 px-3 bg-white focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                        <?php if (isset($errors['name'])): ?>
                                            <p class="mt-1 text-sm text-red-600"><?php echo $errors['name']; ?></p>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="category" class="block text-sm font-medium text-gray-700 mb-1">
                                            Category <span class="text-red-500">*</span>
                                        </label>
                                        <select id="category" name="category" required
                                            class="block w-full rounded-md border-gray-300 shadow-sm py-2 px-3 bg-white focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                            <option value="">Select Category</option>
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?php echo $cat; ?>" <?php if($category === $cat) echo 'selected'; ?>>
                                                    <?php echo ucfirst($cat); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if (isset($errors['category'])): ?>
                                            <p class="mt-1 text-sm text-red-600"><?php echo $errors['category']; ?></p>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="price" class="block text-sm font-medium text-gray-700 mb-1">
                                            Price (₹) <span class="text-red-500">*</span>
                                        </label>
                                        <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars($price); ?>" required
                                            class="block w-full rounded-md border-gray-300 shadow-sm py-2 px-3 bg-white focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                        <?php if (isset($errors['price'])): ?>
                                            <p class="mt-1 text-sm text-red-600"><?php echo $errors['price']; ?></p>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="image" class="block text-sm font-medium text-gray-700 mb-1">
                                            Product Image <span class="text-red-500">*</span>
                                        </label>
                                        <input type="file" id="image" name="image" accept="image/*" required
                                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                        <p class="mt-1 text-sm text-gray-500">JPG, PNG or GIF. Max 2MB.</p>
                                        <?php if (isset($errors['image'])): ?>
                                            <p class="mt-1 text-sm text-red-600"><?php echo $errors['image']; ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div>
                                    <div class="mb-4">
                                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                            Description <span class="text-red-500">*</span>
                                        </label>
                                        <textarea id="description" name="description" rows="5" required
                                            class="block w-full rounded-md border-gray-300 shadow-sm py-2 px-3 bg-white focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"><?php echo htmlspecialchars($description); ?></textarea>
                                        <?php if (isset($errors['description'])): ?>
                                            <p class="mt-1 text-sm text-red-600"><?php echo $errors['description']; ?></p>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <div class="flex items-center">
                                            <input type="checkbox" id="is_available" name="is_available" <?php if($is_available) echo 'checked'; ?>
                                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                            <label for="is_available" class="ml-2 block text-sm text-gray-900">
                                                Product is available for purchase
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div id="image-preview" class="mt-4 hidden">
                                        <p class="text-sm font-medium text-gray-700 mb-2">Image Preview</p>
                                        <img id="preview-img" src="#" alt="Preview" class="rounded-lg max-h-48 object-contain border">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-6 flex justify-end border-t border-gray-200 pt-6">
                                <a href="products.php" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md mr-2 hover:bg-gray-300">
                                    Cancel
                                </a>
                                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700">
                                    <i class="fas fa-save mr-1"></i> Save Product
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script>
        // Image preview functionality
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview-img').src = e.target.result;
                    document.getElementById('image-preview').classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html> 