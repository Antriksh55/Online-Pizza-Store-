<?php
    $pageTitle = "Manage Products";
    require_once '../includes/functions.php';
    
    // Require admin login
    requireAdmin();
    
    // Pagination settings
    $records_per_page = 10;
    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
    $start_from = ($page - 1) * $records_per_page;
    
    // Search filter
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $category = isset($_GET['category']) ? trim($_GET['category']) : '';
    
    // Get products with pagination and filters
    $conn = connectDB();
    
    // Count total records for pagination
    $count_sql = "SELECT COUNT(*) as total FROM products";
    $where_clauses = [];
    
    if (!empty($search)) {
        $search_term = '%' . $conn->real_escape_string($search) . '%';
        $where_clauses[] = "(name LIKE ? OR description LIKE ?)";
    }
    
    if (!empty($category)) {
        $where_clauses[] = "category = ?";
    }
    
    if (!empty($where_clauses)) {
        $count_sql .= " WHERE " . implode(" AND ", $where_clauses);
    }
    
    if (!empty($search) && !empty($category)) {
        $count_stmt = $conn->prepare($count_sql);
        $count_stmt->bind_param("sss", $search_term, $search_term, $category);
    } elseif (!empty($search)) {
        $count_stmt = $conn->prepare($count_sql);
        $count_stmt->bind_param("ss", $search_term, $search_term);
    } elseif (!empty($category)) {
        $count_stmt = $conn->prepare($count_sql);
        $count_stmt->bind_param("s", $category);
    } else {
        $count_result = $conn->query($count_sql);
    }
    
    if (isset($count_stmt)) {
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
    }
    
    $total_records = $count_result->fetch_assoc()['total'];
    $total_pages = ceil($total_records / $records_per_page);
    
    // Get product data with pagination
    $sql = "SELECT * FROM products";
    
    if (!empty($where_clauses)) {
        $sql .= " WHERE " . implode(" AND ", $where_clauses);
    }
    
    $sql .= " ORDER BY category, name LIMIT ?, ?";
    
    $stmt = $conn->prepare($sql);
    
    if (!empty($search) && !empty($category)) {
        $stmt->bind_param("sssii", $search_term, $search_term, $category, $start_from, $records_per_page);
    } elseif (!empty($search)) {
        $stmt->bind_param("ssii", $search_term, $search_term, $start_from, $records_per_page);
    } elseif (!empty($category)) {
        $stmt->bind_param("sii", $category, $start_from, $records_per_page);
    } else {
        $stmt->bind_param("ii", $start_from, $records_per_page);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    // Get categories for filter
    $category_sql = "SELECT DISTINCT category FROM products ORDER BY category";
    $category_result = $conn->query($category_sql);
    $categories = [];
    
    while ($row = $category_result->fetch_assoc()) {
        $categories[] = $row['category'];
    }
    
    // Handle product deletion if requested
    if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
        $product_id = (int) $_GET['delete'];
        
        // Get product info for file deletion
        $sql = "SELECT image_url FROM products WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        
        // Delete product
        $sql = "DELETE FROM products WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            // Delete product image if exists
            if ($product && !empty($product['image_url'])) {
                $image_path = "../uploads/products/" . $product['image_url'];
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }
            
            $_SESSION['success'] = "Product deleted successfully.";
        } else {
            $_SESSION['error'] = "Failed to delete product.";
        }
        
        // Redirect to remove the delete parameter
        header("Location: products.php");
        exit;
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
                    <h2 class="text-xl font-semibold text-gray-800">Manage Products</h2>
                    <a href="add_product.php" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                        <i class="fas fa-plus mr-1"></i> Add Product
                    </a>
                </div>
            </header>
            
            <main class="p-6">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        <?php 
                            echo $_SESSION['success']; 
                            unset($_SESSION['success']);
                        ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?php 
                            echo $_SESSION['error']; 
                            unset($_SESSION['error']);
                        ?>
                    </div>
                <?php endif; ?>
                
                <!-- Filters -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h3 class="text-lg font-semibold mb-4">Filter Products</h3>
                    
                    <form action="products.php" method="get" class="flex flex-wrap gap-4">
                        <div class="w-full sm:w-auto flex-1">
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                            <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                                placeholder="Search by name or description" 
                                class="block w-full rounded-md border-gray-300 shadow-sm py-2 px-3 bg-white focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        
                        <div class="w-full sm:w-auto">
                            <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select id="category" name="category" class="block w-full rounded-md border-gray-300 shadow-sm py-2 px-3 bg-white">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat; ?>" <?php if($category === $cat) echo 'selected'; ?>>
                                        <?php echo ucfirst($cat); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="flex items-end">
                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                                <i class="fas fa-filter mr-1"></i> Apply Filters
                            </button>
                        </div>
                        
                        <?php if (!empty($search) || !empty($category)): ?>
                            <div class="flex items-end">
                                <a href="products.php" class="text-gray-600 border border-gray-300 px-4 py-2 rounded-md hover:bg-gray-100">
                                    <i class="fas fa-times mr-1"></i> Clear Filters
                                </a>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
                
                <!-- Products Table -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold">Product List</h3>
                    </div>
                    
                    <?php if (empty($products)): ?>
                        <div class="p-6 text-center text-gray-500">
                            <i class="fas fa-box-open text-gray-300 text-5xl mb-3"></i>
                            <?php if (!empty($search) || !empty($category)): ?>
                                <p>No products found matching your search criteria.</p>
                            <?php else: ?>
                                <p>No products found. Start adding products to your store.</p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($products as $product): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <img src="../uploads/products/<?php echo $product['image_url']; ?>" 
                                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                                     class="h-12 w-12 rounded-full object-cover">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="font-medium text-gray-900"><?php echo htmlspecialchars($product['name']); ?></div>
                                                <div class="text-sm text-gray-500"><?php echo strlen($product['description']) > 50 ? substr(htmlspecialchars($product['description']), 0, 47) . '...' : htmlspecialchars($product['description']); ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo ucfirst($product['category']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                <?php echo formatPrice($product['price']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <?php if ($product['is_available']): ?>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        Available
                                                    </span>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        Unavailable
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <div class="flex gap-3">
                                                    <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="text-indigo-600 hover:text-indigo-900">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <a href="#" onclick="confirmDelete(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>')" class="text-red-600 hover:text-red-900">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                                <div class="flex justify-between items-center">
                                    <div class="text-sm text-gray-700">
                                        Showing <span class="font-medium"><?php echo $start_from + 1; ?></span> to 
                                        <span class="font-medium"><?php echo min($start_from + $records_per_page, $total_records); ?></span> of 
                                        <span class="font-medium"><?php echo $total_records; ?></span> products
                                    </div>
                                    
                                    <div class="flex space-x-1">
                                        <?php
                                        $queries = $_GET;
                                        
                                        // Previous page
                                        if ($page > 1) {
                                            $queries['page'] = $page - 1;
                                            $prev_link = 'products.php?' . http_build_query($queries);
                                            echo '<a href="' . $prev_link . '" class="px-3 py-1 rounded-md bg-white border border-gray-300 text-gray-500 hover:bg-gray-50">Previous</a>';
                                        }
                                        
                                        // Page numbers
                                        $start_page = max(1, $page - 2);
                                        $end_page = min($total_pages, $page + 2);
                                        
                                        for ($i = $start_page; $i <= $end_page; $i++) {
                                            $queries['page'] = $i;
                                            $page_link = 'products.php?' . http_build_query($queries);
                                            
                                            if ($i == $page) {
                                                echo '<a href="' . $page_link . '" class="px-3 py-1 rounded-md bg-indigo-600 text-white">' . $i . '</a>';
                                            } else {
                                                echo '<a href="' . $page_link . '" class="px-3 py-1 rounded-md bg-white border border-gray-300 text-gray-500 hover:bg-gray-50">' . $i . '</a>';
                                            }
                                        }
                                        
                                        // Next page
                                        if ($page < $total_pages) {
                                            $queries['page'] = $page + 1;
                                            $next_link = 'products.php?' . http_build_query($queries);
                                            echo '<a href="' . $next_link . '" class="px-3 py-1 rounded-md bg-white border border-gray-300 text-gray-500 hover:bg-gray-50">Next</a>';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
    
    <script>
        function confirmDelete(productId, productName) {
            if (confirm(`Are you sure you want to delete the product "${productName}"?`)) {
                window.location.href = `products.php?delete=${productId}`;
            }
        }
    </script>
</body>
</html> 