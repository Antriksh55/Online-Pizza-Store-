<?php
    $pageTitle = "Customer Orders";
    require_once '../includes/functions.php';
    
    // Require admin login
    requireAdmin();
    
    // Check if user ID is provided
    if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
        header("Location: customers.php");
        exit();
    }
    
    $user_id = (int) $_GET['user_id'];
    
    // Get customer details
    $conn = connectDB();
    
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['error'] = "Customer not found";
        header("Location: customers.php");
        exit();
    }
    
    $customer = $result->fetch_assoc();
    
    // Get orders
    $sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    
    // Calculate total spent
    $sql = "SELECT SUM(total_amount) as total_spent FROM orders WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $total_spent = $result->fetch_assoc()['total_spent'];
    
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
                        <a href="products.php" class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 rounded">
                            <i class="fas fa-box w-5 mr-2"></i>
                            <span>Products</span>
                        </a>
                    </li>
                    <li class="mb-1">
                        <a href="customers.php" class="flex items-center px-4 py-2 bg-gray-700 rounded text-white">
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
                    <h2 class="text-xl font-semibold text-gray-800">Orders for <?php echo htmlspecialchars($customer['name']); ?></h2>
                    <a href="customers.php" class="text-indigo-600 hover:text-indigo-900">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Customers
                    </a>
                </div>
            </header>
            
            <main class="p-6">
                <!-- Customer Info -->
                <div class="bg-white rounded-lg shadow-md mb-6 overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg font-semibold">Customer Information</h3>
                    </div>
                    
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <div class="mb-4">
                                    <p class="text-sm text-gray-500">Name</p>
                                    <p class="font-medium"><?php echo htmlspecialchars($customer['name']); ?></p>
                                </div>
                                <div class="mb-4">
                                    <p class="text-sm text-gray-500">Email</p>
                                    <p class="font-medium"><?php echo htmlspecialchars($customer['email']); ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Phone</p>
                                    <p class="font-medium"><?php echo htmlspecialchars($customer['phone']); ?></p>
                                </div>
                            </div>
                            <div>
                                <div class="mb-4">
                                    <p class="text-sm text-gray-500">Address</p>
                                    <p class="font-medium"><?php echo htmlspecialchars($customer['address']); ?></p>
                                </div>
                                <div class="mb-4">
                                    <p class="text-sm text-gray-500">Registered On</p>
                                    <p class="font-medium"><?php echo date('F j, Y', strtotime($customer['created_at'])); ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Total Spent</p>
                                    <p class="font-medium text-green-600"><?php echo formatPrice($total_spent ? $total_spent : 0); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Orders List -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg font-semibold">Order History (<?php echo count($orders); ?> orders)</h3>
                    </div>
                    
                    <?php if (empty($orders)): ?>
                        <div class="p-6 text-center text-gray-500">
                            <i class="fas fa-shopping-cart text-gray-300 text-5xl mb-3"></i>
                            <p>This customer has not placed any orders yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                #<?php echo $order['id']; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo date('M d, Y - h:i A', strtotime($order['created_at'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                <?php echo formatPrice($order['total_amount']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <div>
                                                    <span class="text-gray-900"><?php echo $order['payment_method']; ?></span>
                                                    <p class="text-xs mt-1">
                                                        <span class="<?php echo $order['payment_status'] === 'completed' ? 'text-green-600' : 'text-amber-600'; ?> font-medium">
                                                            <?php echo ucfirst($order['payment_status']); ?>
                                                        </span>
                                                    </p>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
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
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <a href="view_order.php?id=<?php echo $order['id']; ?>" class="text-indigo-600 hover:text-indigo-900">
                                                    <i class="fas fa-eye"></i> View Details
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