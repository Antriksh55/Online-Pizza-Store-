<?php
    $pageTitle = "Admin Dashboard";
    require_once '../includes/functions.php';
    
    // Require admin login
    requireAdmin();
    
    // Get some basic stats
    $conn = connectDB();
    
    // Total orders
    $sql = "SELECT COUNT(*) as total FROM orders";
    $result = $conn->query($sql);
    $totalOrders = $result->fetch_assoc()['total'];
    
    // Pending orders
    $sql = "SELECT COUNT(*) as total FROM orders WHERE order_status = 'confirmed'";
    $result = $conn->query($sql);
    $pendingOrders = $result->fetch_assoc()['total'];
    
    // Total products
    $sql = "SELECT COUNT(*) as total FROM products";
    $result = $conn->query($sql);
    $totalProducts = $result->fetch_assoc()['total'];
    
    // Total customers
    $sql = "SELECT COUNT(*) as total FROM users";
    $result = $conn->query($sql);
    $totalCustomers = $result->fetch_assoc()['total'];
    
    // Recent orders
    $sql = "SELECT o.*, u.name as customer_name 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            ORDER BY o.created_at DESC 
            LIMIT 5";
    $result = $conn->query($sql);
    $recentOrders = [];
    while ($row = $result->fetch_assoc()) {
        $recentOrders[] = $row;
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
                <!-- Stats Cards -->
                <div class="grid grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow p-5">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-500 text-white mr-4">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm">Total Orders</p>
                                <p class="text-2xl font-semibold"><?php echo $totalOrders; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-5">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-500 text-white mr-4">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm">Pending Orders</p>
                                <p class="text-2xl font-semibold"><?php echo $pendingOrders; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-5">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-500 text-white mr-4">
                                <i class="fas fa-box"></i>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm">Total Products</p>
                                <p class="text-2xl font-semibold"><?php echo $totalProducts; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-5">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-500 text-white mr-4">
                                <i class="fas fa-users"></i>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm">Total Customers</p>
                                <p class="text-2xl font-semibold"><?php echo $totalCustomers; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Orders -->
                <div class="bg-white rounded-lg shadow">
                    <div class="py-3 px-5 border-b border-gray-200">
                        <h3 class="text-lg font-semibold">Recent Orders</h3>
                    </div>
                    
                    <div class="p-5">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (empty($recentOrders)): ?>
                                    <tr>
                                        <td colspan="6" class="px-4 py-4 text-center text-gray-500">No orders found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recentOrders as $order): ?>
                                        <tr>
                                            <td class="px-4 py-4 whitespace-nowrap">#<?php echo $order['id']; ?></td>
                                            <td class="px-4 py-4 whitespace-nowrap"><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                            <td class="px-4 py-4 whitespace-nowrap"><?php echo formatPrice($order['total_amount']); ?></td>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    <?php 
                                                    $statusClass = 'bg-gray-100 text-gray-800';
                                                    switch ($order['order_status']) {
                                                        case 'confirmed':
                                                            $statusClass = 'bg-blue-100 text-blue-800';
                                                            break;
                                                        case 'preparing':
                                                            $statusClass = 'bg-yellow-100 text-yellow-800';
                                                            break;
                                                        case 'out_for_delivery':
                                                            $statusClass = 'bg-purple-100 text-purple-800';
                                                            break;
                                                        case 'delivered':
                                                            $statusClass = 'bg-green-100 text-green-800';
                                                            break;
                                                        case 'cancelled':
                                                            $statusClass = 'bg-red-100 text-red-800';
                                                            break;
                                                    }
                                                    echo $statusClass;
                                                    ?>">
                                                    <?php echo getOrderStatusText($order['order_status']); ?>
                                                </span>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <?php echo date('M d, Y', strtotime($order['created_at'])); ?>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm">
                                                <a href="view_order.php?id=<?php echo $order['id']; ?>" class="text-indigo-600 hover:text-indigo-900">View</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        
                        <div class="mt-4 text-right">
                            <a href="orders.php" class="text-indigo-600 hover:text-indigo-900">View All Orders</a>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html> 