<?php
session_start();
$pageTitle = "Add Product";
require_once '../includes/functions.php';

// Require admin login
requireAdmin();

$name = $description = $price = '';
$category_id = 0;
$error = '';
$success = '';

// Get categories for the dropdown
$conn = connectDB();
$sql = "SELECT id, name FROM categories ORDER BY name";
$result = $conn->query($sql);
$categories = [];
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

// Add a new category if requested
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $category_name = trim($_POST['new_category']);
    
    if (empty($category_name)) {
        $error = "Category name cannot be empty";
    } else {
        $sql = "INSERT INTO categories (name) VALUES (?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $category_name);
        
        if ($stmt->execute()) {
            $success = "Category added successfully";
            
            // Refresh categories
            $sql = "SELECT id, name FROM categories ORDER BY name";
            $result = $conn->query($sql);
            $categories = [];
            while ($row = $result->fetch_assoc()) {
                $categories[] = $row;
            }
            
            // Set the newly added category as selected
            $category_id = $conn->insert_id;
        } else {
            $error = "Failed to add category: " . $conn->error;
        }
    }
}

// Handle form submission for product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    $active = isset($_POST['active']) ? 1 : 0;
    
    // Validate form inputs
    if (empty($name) || empty($description) || empty($price)) {
        $error = "All fields are required";
    } elseif (!is_numeric($price) || $price <= 0) {
        $error = "Price must be a positive number";
    } elseif ($category_id <= 0) {
        $error = "Please select a category";
    } else {
        // Handle image upload
        $image_name = '';
        $upload_success = true;
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = getUploadPath('uploads/');
            
            // Generate unique image name
            $image_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image_name = uniqid('product_') . '.' . $image_extension;
            $target_file = $upload_dir . $image_name;
            
            // Check file type
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array(strtolower($image_extension), $allowed_types)) {
                $error = "Only JPG, JPEG, PNG & GIF files are allowed";
                $upload_success = false;
            } 
            // Check file size (max 5MB)
            elseif ($_FILES['image']['size'] > 5000000) {
                $error = "File is too large (max 5MB)";
                $upload_success = false;
            } 
            // Upload file
            elseif (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $error = "Failed to upload image";
                $upload_success = false;
            }
        }
        
        if ($upload_success) {
            try {
                // Start transaction
                $conn->begin_transaction();
                
                // Insert product
                $sql = "INSERT INTO products (name, description, price, category_id, image, active) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssdiis", $name, $description, $price, $category_id, $image_name, $active);
                
                if ($stmt->execute()) {
                    // Commit transaction
                    $conn->commit();
                    
                    // Redirect to product list
                    $_SESSION['success'] = "Product added successfully";
                    header("Location: products.php");
                    exit();
                } else {
                    // Rollback transaction
                    $conn->rollback();
                    $error = "Failed to add product: " . $stmt->error;
                    
                    // Delete uploaded image if product insertion failed
                    if (!empty($image_name)) {
                        @unlink($upload_dir . $image_name);
                    }
                }
            } catch (Exception $e) {
                // Rollback transaction
                $conn->rollback();
                $error = "An error occurred: " . $e->getMessage();
                
                // Delete uploaded image if an exception occurred
                if (!empty($image_name)) {
                    @unlink($upload_dir . $image_name);
                }
            }
        }
    }
}

$conn->close();
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
                    <a href="products.php" class="flex items-center text-gray-600 hover:text-gray-900">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Products
                    </a>
                </div>
            </header>
            
            <main class="p-6">
                <?php if (!empty($error)): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                        <p><?php echo $error; ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                        <p><?php echo $success; ?></p>
                    </div>
                <?php endif; ?>
                
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6">
                        <form action="add_product.php" method="post" enctype="multipart/form-data">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
                                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required 
                                        class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                
                                <div>
                                    <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Price (₹)</label>
                                    <input type="number" id="price" name="price" value="<?php echo htmlspecialchars($price); ?>" required min="0" step="0.01" 
                                        class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                
                                <div>
                                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                    <div class="flex">
                                        <select id="category_id" name="category_id" required class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="">Select Category</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?php echo $category['id']; ?>" <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="button" id="newCategoryBtn" class="ml-2 inline-flex items-center p-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium bg-white hover:bg-gray-50">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div>
                                    <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Product Image</label>
                                    <input type="file" id="image" name="image" accept="image/*" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none">
                                    <div class="mt-1 text-xs text-gray-500">
                                        Recommended size: 500x500px. Max file size: 5MB.
                                    </div>
                                    <div class="mt-2">
                                        <img id="image-preview" src="#" alt="Image Preview" class="hidden max-h-32 rounded-md">
                                    </div>
                                </div>
                                
                                <div class="md:col-span-2">
                                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                    <textarea id="description" name="description" rows="4" required 
                                        class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"><?php echo htmlspecialchars($description); ?></textarea>
                                </div>
                                
                                <div class="md:col-span-2">
                                    <div class="flex items-center">
                                        <input type="checkbox" id="active" name="active" value="1" checked 
                                            class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                        <label for="active" class="ml-2 block text-sm text-gray-700">
                                            Active (available for purchase)
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="md:col-span-2">
                                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <i class="fas fa-save mr-2"></i> Save Product
                                    </button>
                                    <a href="products.php" class="ml-2 inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Cancel
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- New Category Modal -->
    <div id="newCategoryModal" class="fixed z-10 inset-0 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="add_product.php" method="post">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Add New Category</h3>
                                <div class="mt-2">
                                    <input type="text" name="new_category" required placeholder="Category Name" 
                                        class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" name="add_category" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Add Category
                        </button>
                        <button type="button" id="cancelCategoryBtn" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Image preview
        document.getElementById('image').addEventListener('change', function(e) {
            const preview = document.getElementById('image-preview');
            const file = e.target.files[0];
            
            if (file) {
                preview.src = URL.createObjectURL(file);
                preview.classList.remove('hidden');
            } else {
                preview.src = '#';
                preview.classList.add('hidden');
            }
        });
        
        // New category modal
        const modal = document.getElementById('newCategoryModal');
        const newCategoryBtn = document.getElementById('newCategoryBtn');
        const cancelCategoryBtn = document.getElementById('cancelCategoryBtn');
        
        newCategoryBtn.addEventListener('click', function() {
            modal.classList.remove('hidden');
        });
        
        cancelCategoryBtn.addEventListener('click', function() {
            modal.classList.add('hidden');
        });
    </script>
</body>
</html> 