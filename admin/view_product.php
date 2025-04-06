<?php
    $pageTitle = "View Product";
    require_once '../includes/functions.php';
    
    // Require admin login
    requireAdmin();
    
    // Check if product ID is provided
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        $_SESSION['error'] = "Product ID is required";
        header("Location: products.php");
        exit;
    }
    
    $product_id = intval($_GET['id']);
    $conn = connectDB();
    
    // Get product details
    $sql = "SELECT p.*, 
                  (SELECT COUNT(*) FROM order_items oi JOIN orders o ON oi.order_id = o.id WHERE oi.product_id = p.id) as order_count 
            FROM products p 
            WHERE p.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['error'] = "Product not found";
        header("Location: products.php");
        exit;
    }
    
    $product = $result->fetch_assoc();
    
    // Get recent orders for this product
    $sql = "SELECT o.id as order_id, o.order_date, o.status, u.name as customer_name, 
                   oi.quantity, oi.unit_price 
            FROM order_items oi 
            JOIN orders o ON oi.order_id = o.id 
            JOIN users u ON o.user_id = u.id 
            WHERE oi.product_id = ? 
            ORDER BY o.order_date DESC 
            LIMIT 5";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $recent_orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
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
                    <h2 class="text-xl font-semibold text-gray-800">Product Details</h2>
                    <div class="flex space-x-2">
                        <a href="edit_product.php?id=<?php echo $product_id; ?>" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                            <i class="fas fa-edit mr-1"></i> Edit Product
                        </a>
                        <a href="products.php" class="text-indigo-600 hover:text-indigo-900 border border-indigo-600 bg-white px-4 py-2 rounded-md">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Products
                        </a>
                    </div>
                </div>
            </header>
            
            <main class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Product Info -->
                    <div class="col-span-2">
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                                <h3 class="text-lg font-semibold">Product Information</h3>
                            </div>
                            
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <h1 class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($product['name']); ?></h1>
                                        
                                        <div class="mt-4 space-y-3">
                                            <div>
                                                <p class="text-sm text-gray-500">Category</p>
                                                <p class="font-medium"><?php echo ucfirst($product['category']); ?></p>
                                            </div>
                                            
                                            <div>
                                                <p class="text-sm text-gray-500">Price</p>
                                                <p class="font-medium text-lg text-indigo-600"><?php echo formatPrice($product['price']); ?></p>
                                            </div>
                                            
                                            <div>
                                                <p class="text-sm text-gray-500">Status</p>
                                                <?php if ($product['is_available']): ?>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        Available
                                                    </span>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        Unavailable
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div>
                                                <p class="text-sm text-gray-500">Product ID</p>
                                                <p class="font-medium">#<?php echo $product['id']; ?></p>
                                            </div>
                                            
                                            <?php if (isset($product['slug'])): ?>
                                            <div>
                                                <p class="text-sm text-gray-500">Slug</p>
                                                <p class="font-medium"><?php echo htmlspecialchars($product['slug']); ?></p>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <div>
                                                <p class="text-sm text-gray-500">Added On</p>
                                                <p class="font-medium"><?php echo date('F j, Y', strtotime($product['created_at'] ?? 'now')); ?></p>
                                            </div>
                                            
                                            <?php if (isset($product['updated_at'])): ?>
                                            <div>
                                                <p class="text-sm text-gray-500">Last Updated</p>
                                                <p class="font-medium"><?php echo date('F j, Y g:i A', strtotime($product['updated_at'])); ?></p>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <p class="text-sm text-gray-500 mb-2">Description</p>
                                        <div class="prose max-w-none">
                                            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                                        </div>
                                        
                                        <?php if (!empty($product['image_url'])): ?>
                                        <div class="mt-6">
                                            <p class="text-sm text-gray-500 mb-2">Product Image</p>
                                            <img src="<?php echo getUploadUrl('../uploads/products/' . $product['image_url']); ?>" 
                                                alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                class="rounded-lg h-auto max-w-full object-cover border"
                                                onerror="this.src='/uploads/products/default.png'; this.onerror='';">
                                        </div>
                                        <?php else: ?>
                                        <div class="mt-6">
                                            <p class="text-sm text-gray-500 mb-2">No Product Image</p>
                                            <div class="rounded-lg h-48 w-full bg-gray-200 flex items-center justify-center border">
                                                <i class="fas fa-image text-gray-400 text-5xl"></i>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sales Stats -->
                    <div>
                        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                                <h3 class="text-lg font-semibold">Sales Statistics</h3>
                            </div>
                            
                            <div class="p-6">
                                <div class="mb-4 text-center">
                                    <div class="text-3xl font-bold text-indigo-600"><?php echo $product['order_count']; ?></div>
                                    <div class="text-sm text-gray-500">Total Orders</div>
                                </div>
                                
                                <div class="border-t border-gray-200 pt-4">
                                    <h4 class="font-medium mb-2">Quick Actions</h4>
                                    <div class="flex flex-col space-y-2">
                                        <a href="edit_product.php?id=<?php echo $product_id; ?>" class="text-indigo-600 hover:text-indigo-900 flex items-center">
                                            <i class="fas fa-edit mr-2"></i> Edit Product
                                        </a>
                                        <a href="delete_product.php?id=<?php echo $product['id']; ?>" onclick="return confirm('Are you sure you want to delete this product?');" class="text-red-600 hover:text-red-900 flex items-center">
                                            <i class="fas fa-trash mr-2"></i> Delete Product
                                        </a>
                                        <?php if ($product['is_available']): ?>
                                            <a href="toggle_product_status.php?id=<?php echo $product_id; ?>" class="text-orange-600 hover:text-orange-900 flex items-center">
                                                <i class="fas fa-ban mr-2"></i> Mark as Unavailable
                                            </a>
                                        <?php else: ?>
                                            <a href="toggle_product_status.php?id=<?php echo $product_id; ?>" class="text-green-600 hover:text-green-900 flex items-center">
                                                <i class="fas fa-check-circle mr-2"></i> Mark as Available
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Recent Orders -->
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                                <h3 class="text-lg font-semibold">Recent Orders</h3>
                            </div>
                            
                            <div class="p-4">
                                <?php if (empty($recent_orders)): ?>
                                    <div class="text-center py-4 text-gray-500">
                                        <p>No orders found for this product</p>
                                    </div>
                                <?php else: ?>
                                    <div class="divide-y divide-gray-200">
                                        <?php foreach ($recent_orders as $order): ?>
                                            <div class="py-3">
                                                <div class="flex justify-between items-center">
                                                    <a href="view_order.php?id=<?php echo $order['order_id']; ?>" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                                        Order #<?php echo $order['order_id']; ?>
                                                    </a>
                                                    <span class="text-sm text-gray-500">
                                                        <?php echo date('M j, Y', strtotime($order['order_date'])); ?>
                                                    </span>
                                                </div>
                                                <p class="text-sm">
                                                    <span class="text-gray-600"><?php echo htmlspecialchars($order['customer_name']); ?></span> • 
                                                    <span class="font-medium"><?php echo $order['quantity']; ?> × <?php echo formatPrice($order['unit_price']); ?></span>
                                                </p>
                                                <p class="text-xs mt-1">
                                                    <span class="<?php echo getOrderStatusClass($order['status']); ?>">
                                                        <?php echo getOrderStatusText($order['status'] ?? 'pending'); ?>
                                                    </span>
                                                </p>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <?php if ($product['order_count'] > count($recent_orders)): ?>
                                        <div class="mt-4 text-center">
                                            <a href="#" class="text-indigo-600 hover:text-indigo-900 text-sm">
                                                View all <?php echo $product['order_count']; ?> orders →
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script>
        function getOrderStatusClass(status) {
            switch(status) {
                case 'pending': return 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800';
                case 'confirmed': return 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800';
                case 'preparing': return 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800';
                case 'out_for_delivery': return 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800';
                case 'delivered': return 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800';
                case 'cancelled': return 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800';
                default: return 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800';
            }
        }
    </script>
</body>
</html> 