<?php
    $pageTitle = "View Order";
    require_once '../includes/functions.php';
    
    // Require admin login
    requireAdmin();
    
    // Check if order ID is provided
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        header("Location: orders.php");
        exit();
    }
    
    $order_id = (int) $_GET['id'];
    
    // Get order details
    $order = getOrder($order_id);
    
    if (!$order) {
        $_SESSION['error'] = "Order not found";
        header("Location: orders.php");
        exit();
    }
    
    // Handle status update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
        $status = $_POST['status'];
        
        if (updateOrderStatus($order_id, $status)) {
            $_SESSION['success'] = "Order status updated to " . getOrderStatusText($status);
            
            // Refresh order data
            $order = getOrder($order_id);
        } else {
            $_SESSION['error'] = "Failed to update order status";
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
                        <a href="orders.php" class="flex items-center px-4 py-2 bg-gray-700 rounded text-white">
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
                <div class="py-4 px-6 flex justify-between items-center">
                    <h2 class="text-xl font-semibold text-gray-800">Order #<?php echo $order_id; ?> Details</h2>
                    <a href="orders.php" class="text-indigo-600 hover:text-indigo-900">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Orders
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
                
                <!-- Order Summary -->
                <div class="bg-white rounded-lg shadow-md mb-6 overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg font-semibold">Order Summary</h3>
                    </div>
                    
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Order Info -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-semibold text-gray-700 mb-2">Order Information</h4>
                                <div class="space-y-2 text-sm">
                                    <p><span class="text-gray-500">Order ID:</span> #<?php echo $order['id']; ?></p>
                                    <p><span class="text-gray-500">Date:</span> <?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></p>
                                    <p class="flex items-center">
                                        <span class="text-gray-500 mr-1">Status:</span> 
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ml-1
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
                                    </p>
                                    <p><span class="text-gray-500">Total:</span> <span class="font-semibold text-gray-900"><?php echo formatPrice($order['total_amount']); ?></span></p>
                                </div>
                            </div>
                            
                            <!-- Payment Info -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-semibold text-gray-700 mb-2">Payment Information</h4>
                                <div class="space-y-2 text-sm">
                                    <p><span class="text-gray-500">Payment Method:</span> <?php echo $order['payment_method']; ?></p>
                                    <p>
                                        <span class="text-gray-500">Payment Status:</span> 
                                        <span class="<?php echo $order['payment_status'] === 'completed' ? 'text-green-600' : 'text-amber-600'; ?> font-medium">
                                            <?php echo ucfirst($order['payment_status']); ?>
                                        </span>
                                    </p>
                                    <?php if ($order['payment_id']): ?>
                                        <p><span class="text-gray-500">Payment ID:</span> <?php echo $order['payment_id']; ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Customer Info -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-semibold text-gray-700 mb-2">Customer Information</h4>
                                <div class="space-y-2 text-sm">
                                    <p><span class="text-gray-500">Name:</span> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                                    <p><span class="text-gray-500">Email:</span> <?php echo htmlspecialchars($order['email']); ?></p>
                                    <p><span class="text-gray-500">Phone:</span> <?php echo htmlspecialchars($order['phone']); ?></p>
                                    <p><span class="text-gray-500">Address:</span> <?php echo htmlspecialchars($order['address']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Status Update and Order Progress -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <!-- Status Update -->
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-lg font-semibold">Update Status</h3>
                        </div>
                        
                        <div class="p-6">
                            <form action="view_order.php?id=<?php echo $order_id; ?>" method="post">
                                <div class="mb-4">
                                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                    <select id="status" name="status" class="block w-full rounded-md border-gray-300 shadow-sm py-2 px-3 bg-white focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="confirmed" <?php if($order['order_status'] === 'confirmed') echo 'selected'; ?>>Confirmed</option>
                                        <option value="preparing" <?php if($order['order_status'] === 'preparing') echo 'selected'; ?>>Preparing</option>
                                        <option value="out_for_delivery" <?php if($order['order_status'] === 'out_for_delivery') echo 'selected'; ?>>Out for Delivery</option>
                                        <option value="delivered" <?php if($order['order_status'] === 'delivered') echo 'selected'; ?>>Delivered</option>
                                        <option value="cancelled" <?php if($order['order_status'] === 'cancelled') echo 'selected'; ?>>Cancelled</option>
                                    </select>
                                </div>
                                
                                <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    Update Status
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Order Progress -->
                    <div class="md:col-span-2 bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-lg font-semibold">Order Progress</h3>
                        </div>
                        
                        <div class="p-6">
                            <div class="relative">
                                <div class="flex justify-between mb-2">
                                    <div class="text-center">
                                        <div class="w-10 h-10 bg-green-500 text-white rounded-full flex items-center justify-center mx-auto">
                                            <i class="fas fa-check"></i>
                                        </div>
                                        <p class="text-sm mt-1">Confirmed</p>
                                    </div>
                                    
                                    <div class="text-center">
                                        <div class="w-10 h-10 <?php echo $order['order_status'] == 'preparing' || $order['order_status'] == 'out_for_delivery' || $order['order_status'] == 'delivered' ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-500'; ?> rounded-full flex items-center justify-center mx-auto">
                                            <?php if ($order['order_status'] == 'preparing' || $order['order_status'] == 'out_for_delivery' || $order['order_status'] == 'delivered'): ?>
                                                <i class="fas fa-check"></i>
                                            <?php else: ?>
                                                <i class="fas fa-utensils"></i>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-sm mt-1">Preparing</p>
                                    </div>
                                    
                                    <div class="text-center">
                                        <div class="w-10 h-10 <?php echo $order['order_status'] == 'out_for_delivery' || $order['order_status'] == 'delivered' ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-500'; ?> rounded-full flex items-center justify-center mx-auto">
                                            <?php if ($order['order_status'] == 'out_for_delivery' || $order['order_status'] == 'delivered'): ?>
                                                <i class="fas fa-check"></i>
                                            <?php else: ?>
                                                <i class="fas fa-truck"></i>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-sm mt-1">Out for Delivery</p>
                                    </div>
                                    
                                    <div class="text-center">
                                        <div class="w-10 h-10 <?php echo $order['order_status'] == 'delivered' ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-500'; ?> rounded-full flex items-center justify-center mx-auto">
                                            <?php if ($order['order_status'] == 'delivered'): ?>
                                                <i class="fas fa-check"></i>
                                            <?php else: ?>
                                                <i class="fas fa-home"></i>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-sm mt-1">Delivered</p>
                                    </div>
                                </div>
                                
                                <div class="absolute top-5 left-0 right-0 h-1 bg-gray-300 -z-10">
                                    <div class="h-full bg-green-500" style="width: <?php 
                                        if ($order['order_status'] == 'confirmed') echo '0%';
                                        elseif ($order['order_status'] == 'preparing') echo '33%';
                                        elseif ($order['order_status'] == 'out_for_delivery') echo '66%';
                                        elseif ($order['order_status'] == 'delivered') echo '100%';
                                        else echo '0%';
                                    ?>;"></div>
                                </div>
                            </div>
                            
                            <?php if ($order['order_status'] === 'cancelled'): ?>
                                <div class="mt-6 p-3 bg-red-100 text-red-800 rounded-md text-center">
                                    <p><i class="fas fa-exclamation-circle mr-2"></i> This order has been cancelled</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Order Items -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg font-semibold">Order Items</h3>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($order['items'] as $item): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <img src="../uploads/products/<?php echo $item['image_url']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-10 h-10 rounded-full object-cover mr-3">
                                                <span class="font-medium text-gray-900"><?php echo htmlspecialchars($item['name']); ?></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo formatPrice($item['price']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo $item['quantity']; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?php echo formatPrice($item['price'] * $item['quantity']); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900">Subtotal:</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo formatPrice($order['total_amount'] - 50); ?></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900">Delivery Fee:</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo formatPrice(50); ?></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900">Total:</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo formatPrice($order['total_amount']); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="mt-6 flex justify-between">
                    <a href="orders.php" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Orders
                    </a>
                    
                    <?php if ($order['order_status'] !== 'cancelled'): ?>
                        <button type="button" 
                                class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700"
                                onclick="if(confirm('Are you sure you want to cancel this order?')) { document.getElementById('cancel-form').submit(); }">
                            <i class="fas fa-times mr-1"></i> Cancel Order
                        </button>
                        
                        <form id="cancel-form" action="view_order.php?id=<?php echo $order_id; ?>" method="post" class="hidden">
                            <input type="hidden" name="status" value="cancelled">
                        </form>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
</body>
</html>