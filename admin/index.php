<?php
    $pageTitle = "Dashboard";
    require_once '../includes/functions.php';
    
    // Require admin login
    requireAdmin();
    
    // Get dashboard data
    $conn = connectDB();
    
    try {
        // Total orders
        $sql = "SELECT COUNT(*) as total FROM orders";
        $result = $conn->query($sql);
        $total_orders = ($result) ? $result->fetch_assoc()['total'] : 0;
        
        // Pending orders
        $sql = "SELECT COUNT(*) as total FROM orders WHERE status = 'pending' OR status = 'confirmed'";
        $result = $conn->query($sql);
        $pending_orders = ($result) ? $result->fetch_assoc()['total'] : 0;
        
        // Total products
        $sql = "SELECT COUNT(*) as total FROM products";
        $result = $conn->query($sql);
        $total_products = ($result) ? $result->fetch_assoc()['total'] : 0;
        
        // Total customers
        $sql = "SELECT COUNT(*) as total FROM users";
        $result = $conn->query($sql);
        $total_customers = ($result) ? $result->fetch_assoc()['total'] : 0;
        
        // Recent orders
        $sql = "SELECT o.*, u.name as customer_name 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                ORDER BY o.order_date DESC 
                LIMIT 5";
        $result = $conn->query($sql);
        $recent_orders = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $recent_orders[] = $row;
            }
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error retrieving dashboard data: " . $e->getMessage();
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
                <p class="text-sm text-gray-400 mt-1">Welcome, <?php echo $_SESSION['admin_name'] ?? 'Admin'; ?></p>
            </div>
            
            <nav class="px-2 py-4 flex-grow">
                <ul>
                    <li class="mb-1">
                        <a href="index.php" class="flex items-center px-4 py-2 bg-gray-700 rounded text-white">
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
                        <a href="products.php" class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 rounded">
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
                <div class="py-4 px-6">
                    <h2 class="text-xl font-semibold text-gray-800">Dashboard</h2>
                </div>
            </header>
            
            <main class="p-6">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?php 
                            echo $_SESSION['error']; 
                            unset($_SESSION['error']);
                        ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        <?php 
                            echo $_SESSION['success']; 
                            unset($_SESSION['success']);
                        ?>
                    </div>
                <?php endif; ?>
                
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <!-- Total Orders -->
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="px-6 py-5 flex items-center">
                            <div class="rounded-full bg-indigo-100 p-3 mr-4">
                                <i class="fas fa-shopping-cart text-indigo-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm">Total Orders</p>
                                <h3 class="text-2xl font-bold text-gray-800"><?php echo $total_orders ?? 0; ?></h3>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pending Orders -->
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="px-6 py-5 flex items-center">
                            <div class="rounded-full bg-yellow-100 p-3 mr-4">
                                <i class="fas fa-clock text-yellow-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm">Pending Orders</p>
                                <h3 class="text-2xl font-bold text-gray-800"><?php echo $pending_orders ?? 0; ?></h3>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Total Products -->
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="px-6 py-5 flex items-center">
                            <div class="rounded-full bg-green-100 p-3 mr-4">
                                <i class="fas fa-box text-green-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm">Total Products</p>
                                <h3 class="text-2xl font-bold text-gray-800"><?php echo $total_products ?? 0; ?></h3>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Total Customers -->
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="px-6 py-5 flex items-center">
                            <div class="rounded-full bg-purple-100 p-3 mr-4">
                                <i class="fas fa-users text-purple-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm">Total Customers</p>
                                <h3 class="text-2xl font-bold text-gray-800"><?php echo $total_customers ?? 0; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Orders -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-semibold">Recent Orders</h3>
                        <a href="orders.php" class="text-indigo-600 hover:text-indigo-900 text-sm">
                            View All
                        </a>
                    </div>
                    
                    <?php if (empty($recent_orders)): ?>
                        <div class="p-6 text-center text-gray-500">
                            <i class="fas fa-shopping-cart text-gray-300 text-5xl mb-3"></i>
                            <p>No orders found.</p>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($recent_orders as $order): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="text-sm font-medium text-gray-900">#<?php echo $order['id']; ?></span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="text-sm text-gray-500"><?php echo htmlspecialchars($order['customer_name']); ?></span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="text-sm text-gray-500"><?php echo date('M j, Y, g:i a', strtotime($order['order_date'])); ?></span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="text-sm font-medium text-gray-900"><?php echo formatPrice($order['total_amount']); ?></span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="<?php echo getOrderStatusClass($order['status'] ?? 'pending'); ?>">
                                                    <?php echo getOrderStatusText($order['status'] ?? 'pending'); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <a href="view_order.php?id=<?php echo $order['id']; ?>" class="text-indigo-600 hover:text-indigo-900">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
</body>
</html> 