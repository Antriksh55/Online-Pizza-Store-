<?php
    $pageTitle = "Manage Orders";
    require_once '../includes/functions.php';
    
    // Require admin login
    requireAdmin();
    
    // Handle order status updates
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
        $order_id = (int)$_POST['order_id'];
        $status = $_POST['status'];
        
        if (updateOrderStatus($order_id, $status)) {
            $_SESSION['success'] = "Order #$order_id status updated to " . getOrderStatusText($status);
        } else {
            $_SESSION['error'] = "Failed to update order status";
        }
        
        // Redirect to refresh the page (prevents form resubmission)
        header("Location: orders.php");
        exit;
    }
    
    // Pagination settings
    $records_per_page = 10;
    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
    $start_from = ($page - 1) * $records_per_page;
    
    // Filter settings
    $status_filter = isset($_GET['status']) ? $_GET['status'] : '';
    $date_filter = isset($_GET['date']) ? $_GET['date'] : '';
    
    // Get orders with pagination and filters
    $conn = connectDB();
    
    // Count total records for pagination
    $count_sql = "SELECT COUNT(*) as total FROM orders o JOIN users u ON o.user_id = u.id";
    $where_clauses = [];
    
    if (!empty($status_filter)) {
        $where_clauses[] = "o.order_status = '" . $conn->real_escape_string($status_filter) . "'";
    }
    
    if (!empty($date_filter)) {
        $where_clauses[] = "DATE(o.created_at) = '" . $conn->real_escape_string($date_filter) . "'";
    }
    
    if (!empty($where_clauses)) {
        $count_sql .= " WHERE " . implode(" AND ", $where_clauses);
    }
    
    $count_result = $conn->query($count_sql);
    $total_records = $count_result->fetch_assoc()['total'];
    $total_pages = ceil($total_records / $records_per_page);
    
    // Get order data with pagination
    $sql = "SELECT o.*, u.name as customer_name, u.phone 
            FROM orders o 
            JOIN users u ON o.user_id = u.id";
    
    if (!empty($where_clauses)) {
        $sql .= " WHERE " . implode(" AND ", $where_clauses);
    }
    
    $sql .= " ORDER BY o.created_at DESC LIMIT ?, ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $start_from, $records_per_page);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    
    // Get unique dates for filter
    $date_sql = "SELECT DISTINCT DATE(created_at) as order_date FROM orders ORDER BY order_date DESC LIMIT 30";
    $date_result = $conn->query($date_sql);
    $available_dates = [];
    
    while ($row = $date_result->fetch_assoc()) {
        $available_dates[] = $row['order_date'];
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
                <div class="py-4 px-6">
                    <h2 class="text-xl font-semibold text-gray-800">Manage Orders</h2>
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
                    <h3 class="text-lg font-semibold mb-4">Filter Orders</h3>
                    
                    <form action="orders.php" method="get" class="flex flex-wrap gap-4">
                        <div class="w-full sm:w-auto">
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Order Status</label>
                            <select id="status" name="status" class="block w-full rounded-md border-gray-300 shadow-sm py-2 px-3 bg-white">
                                <option value="">All Statuses</option>
                                <option value="confirmed" <?php if($status_filter === 'confirmed') echo 'selected'; ?>>Confirmed</option>
                                <option value="preparing" <?php if($status_filter === 'preparing') echo 'selected'; ?>>Preparing</option>
                                <option value="out_for_delivery" <?php if($status_filter === 'out_for_delivery') echo 'selected'; ?>>Out for Delivery</option>
                                <option value="delivered" <?php if($status_filter === 'delivered') echo 'selected'; ?>>Delivered</option>
                                <option value="cancelled" <?php if($status_filter === 'cancelled') echo 'selected'; ?>>Cancelled</option>
                            </select>
                        </div>
                        
                        <div class="w-full sm:w-auto">
                            <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Order Date</label>
                            <select id="date" name="date" class="block w-full rounded-md border-gray-300 shadow-sm py-2 px-3 bg-white">
                                <option value="">All Dates</option>
                                <?php foreach ($available_dates as $date): ?>
                                    <option value="<?php echo $date; ?>" <?php if($date_filter === $date) echo 'selected'; ?>>
                                        <?php echo date('M d, Y', strtotime($date)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="flex items-end">
                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                                <i class="fas fa-filter mr-1"></i> Apply Filters
                            </button>
                        </div>
                        
                        <?php if (!empty($status_filter) || !empty($date_filter)): ?>
                            <div class="flex items-end">
                                <a href="orders.php" class="text-gray-600 border border-gray-300 px-4 py-2 rounded-md hover:bg-gray-100">
                                    <i class="fas fa-times mr-1"></i> Clear Filters
                                </a>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
                
                <!-- Orders Table -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold">Order List</h3>
                    </div>
                    
                    <?php if (empty($orders)): ?>
                        <div class="p-6 text-center text-gray-500">
                            <i class="fas fa-box-open text-gray-300 text-5xl mb-3"></i>
                            <p>No orders found</p>
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
                                                <div>
                                                    <p class="text-gray-900 font-medium"><?php echo htmlspecialchars($order['customer_name']); ?></p>
                                                    <p class="text-xs text-gray-500"><?php echo $order['phone']; ?></p>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo date('M d, Y - h:i A', strtotime($order['created_at'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-medium">
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
                                                <div class="flex gap-2">
                                                    <a href="view_order.php?id=<?php echo $order['id']; ?>" class="text-indigo-600 hover:text-indigo-900">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                    <button type="button" 
                                                            class="text-blue-600 hover:text-blue-900 ml-3" 
                                                            onclick="openUpdateModal(<?php echo $order['id']; ?>, '<?php echo $order['order_status']; ?>')">
                                                        <i class="fas fa-edit"></i> Update
                                                    </button>
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
                                        <span class="font-medium"><?php echo $total_records; ?></span> orders
                                    </div>
                                    
                                    <div class="flex space-x-1">
                                        <?php
                                        $queries = $_GET;
                                        
                                        // Previous page
                                        if ($page > 1) {
                                            $queries['page'] = $page - 1;
                                            $prev_link = 'orders.php?' . http_build_query($queries);
                                            echo '<a href="' . $prev_link . '" class="px-3 py-1 rounded-md bg-white border border-gray-300 text-gray-500 hover:bg-gray-50">Previous</a>';
                                        }
                                        
                                        // Page numbers
                                        $start_page = max(1, $page - 2);
                                        $end_page = min($total_pages, $page + 2);
                                        
                                        for ($i = $start_page; $i <= $end_page; $i++) {
                                            $queries['page'] = $i;
                                            $page_link = 'orders.php?' . http_build_query($queries);
                                            
                                            if ($i == $page) {
                                                echo '<a href="' . $page_link . '" class="px-3 py-1 rounded-md bg-indigo-600 text-white">' . $i . '</a>';
                                            } else {
                                                echo '<a href="' . $page_link . '" class="px-3 py-1 rounded-md bg-white border border-gray-300 text-gray-500 hover:bg-gray-50">' . $i . '</a>';
                                            }
                                        }
                                        
                                        // Next page
                                        if ($page < $total_pages) {
                                            $queries['page'] = $page + 1;
                                            $next_link = 'orders.php?' . http_build_query($queries);
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
    
    <!-- Order Status Update Modal -->
    <div id="updateModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeUpdateModal()"></div>
            
            <div class="relative bg-white rounded-lg max-w-md w-full shadow-xl transform transition-all">
                <div class="px-6 py-5 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Update Order Status</h3>
                </div>
                
                <form id="updateForm" action="orders.php" method="post">
                    <input type="hidden" name="order_id" id="modal_order_id">
                    
                    <div class="px-6 py-4">
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">New Status</label>
                        <select id="modal_status" name="status" class="block w-full rounded-md border-gray-300 shadow-sm py-2 px-3 bg-white focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="confirmed">Confirmed</option>
                            <option value="preparing">Preparing</option>
                            <option value="out_for_delivery">Out for Delivery</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="px-6 py-4 bg-gray-50 flex justify-end border-t border-gray-200">
                        <button type="button" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md mr-2 hover:bg-gray-300" onclick="closeUpdateModal()">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-white bg-indigo-600 rounded-md hover:bg-indigo-700">
                            Update Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Modal functions
        function openUpdateModal(orderId, currentStatus) {
            document.getElementById('modal_order_id').value = orderId;
            document.getElementById('modal_status').value = currentStatus;
            document.getElementById('updateModal').classList.remove('hidden');
        }
        
        function closeUpdateModal() {
            document.getElementById('updateModal').classList.add('hidden');
        }
    </script>
</body>
</html> 