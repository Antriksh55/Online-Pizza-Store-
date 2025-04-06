<?php
    $pageTitle = "Manage Customers";
    require_once '../includes/functions.php';
    
    // Require admin login
    requireAdmin();
    
    // Pagination settings
    $records_per_page = 10;
    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
    $start_from = ($page - 1) * $records_per_page;
    
    // Search filter
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    
    // Get customers with pagination and search
    $conn = connectDB();
    
    // Count total records for pagination
    $count_sql = "SELECT COUNT(*) as total FROM users";
    if (!empty($search)) {
        $search_term = '%' . $conn->real_escape_string($search) . '%';
        $count_sql .= " WHERE name LIKE ? OR email LIKE ? OR phone LIKE ?";
        $count_stmt = $conn->prepare($count_sql);
        $count_stmt->bind_param("sss", $search_term, $search_term, $search_term);
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
    } else {
        $count_result = $conn->query($count_sql);
    }
    
    $total_records = $count_result->fetch_assoc()['total'];
    $total_pages = ceil($total_records / $records_per_page);
    
    // Get customer data with pagination
    $sql = "SELECT * FROM users";
    if (!empty($search)) {
        $sql .= " WHERE name LIKE ? OR email LIKE ? OR phone LIKE ?";
    }
    $sql .= " ORDER BY id DESC LIMIT ?, ?";
    
    $stmt = $conn->prepare($sql);
    
    if (!empty($search)) {
        $stmt->bind_param("sssii", $search_term, $search_term, $search_term, $start_from, $records_per_page);
    } else {
        $stmt->bind_param("ii", $start_from, $records_per_page);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $customers = [];
    while ($row = $result->fetch_assoc()) {
        // Get order count for each customer
        $order_sql = "SELECT COUNT(*) as order_count FROM orders WHERE user_id = ?";
        $order_stmt = $conn->prepare($order_sql);
        $order_stmt->bind_param("i", $row['id']);
        $order_stmt->execute();
        $order_result = $order_stmt->get_result();
        $order_count = $order_result->fetch_assoc()['order_count'];
        
        // Add order count to customer data
        $row['order_count'] = $order_count;
        
        $customers[] = $row;
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
                <div class="py-4 px-6">
                    <h2 class="text-xl font-semibold text-gray-800">Manage Customers</h2>
                </div>
            </header>
            
            <main class="p-6">
                <!-- Search -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <form action="customers.php" method="get" class="flex gap-4">
                        <div class="flex-1">
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search Customers</label>
                            <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                                placeholder="Search by name, email or phone" 
                                class="block w-full rounded-md border-gray-300 shadow-sm py-2 px-3 bg-white focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        
                        <div class="flex items-end">
                            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                                <i class="fas fa-search mr-1"></i> Search
                            </button>
                        </div>
                        
                        <?php if (!empty($search)): ?>
                            <div class="flex items-end">
                                <a href="customers.php" class="text-gray-600 border border-gray-300 px-4 py-2 rounded-md hover:bg-gray-100">
                                    <i class="fas fa-times mr-1"></i> Clear
                                </a>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
                
                <!-- Customers Table -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold">Customer List</h3>
                    </div>
                    
                    <?php if (empty($customers)): ?>
                        <div class="p-6 text-center text-gray-500">
                            <i class="fas fa-users text-gray-300 text-5xl mb-3"></i>
                            <?php if (!empty($search)): ?>
                                <p>No customers found matching your search criteria.</p>
                            <?php else: ?>
                                <p>No customers found.</p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Orders</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($customers as $customer): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                #<?php echo $customer['id']; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($customer['name']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo htmlspecialchars($customer['email']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo htmlspecialchars($customer['phone']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo date('M d, Y', strtotime($customer['created_at'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                <span class="px-2 py-1 bg-indigo-100 text-indigo-800 rounded-full text-xs font-semibold">
                                                    <?php echo $customer['order_count']; ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <div class="flex gap-2">
                                                    <button type="button" 
                                                            onclick="viewCustomerDetails(<?php echo $customer['id']; ?>)" 
                                                            class="text-indigo-600 hover:text-indigo-900">
                                                        <i class="fas fa-eye"></i> View
                                                    </button>
                                                    <a href="customer_orders.php?user_id=<?php echo $customer['id']; ?>" class="text-blue-600 hover:text-blue-900 ml-3">
                                                        <i class="fas fa-shopping-cart"></i> Orders
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
                                        <span class="font-medium"><?php echo $total_records; ?></span> customers
                                    </div>
                                    
                                    <div class="flex space-x-1">
                                        <?php
                                        $queries = $_GET;
                                        
                                        // Previous page
                                        if ($page > 1) {
                                            $queries['page'] = $page - 1;
                                            $prev_link = 'customers.php?' . http_build_query($queries);
                                            echo '<a href="' . $prev_link . '" class="px-3 py-1 rounded-md bg-white border border-gray-300 text-gray-500 hover:bg-gray-50">Previous</a>';
                                        }
                                        
                                        // Page numbers
                                        $start_page = max(1, $page - 2);
                                        $end_page = min($total_pages, $page + 2);
                                        
                                        for ($i = $start_page; $i <= $end_page; $i++) {
                                            $queries['page'] = $i;
                                            $page_link = 'customers.php?' . http_build_query($queries);
                                            
                                            if ($i == $page) {
                                                echo '<a href="' . $page_link . '" class="px-3 py-1 rounded-md bg-indigo-600 text-white">' . $i . '</a>';
                                            } else {
                                                echo '<a href="' . $page_link . '" class="px-3 py-1 rounded-md bg-white border border-gray-300 text-gray-500 hover:bg-gray-50">' . $i . '</a>';
                                            }
                                        }
                                        
                                        // Next page
                                        if ($page < $total_pages) {
                                            $queries['page'] = $page + 1;
                                            $next_link = 'customers.php?' . http_build_query($queries);
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
    
    <!-- Customer Details Modal -->
    <div id="customerModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeCustomerModal()"></div>
            
            <div class="relative bg-white rounded-lg max-w-lg w-full shadow-xl transform transition-all">
                <div class="px-6 py-5 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Customer Details</h3>
                    <button type="button" onclick="closeCustomerModal()" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div id="customerDetails" class="px-6 py-4">
                    <p class="text-center text-gray-500 py-8">Loading customer details...</p>
                </div>
                
                <div class="px-6 py-4 bg-gray-50 flex justify-end border-t border-gray-200">
                    <button type="button" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300" onclick="closeCustomerModal()">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Modal functions
        function viewCustomerDetails(customerId) {
            document.getElementById('customerModal').classList.remove('hidden');
            document.getElementById('customerDetails').innerHTML = '<p class="text-center text-gray-500 py-8">Loading customer details...</p>';
            
            // Ajax request to get customer details
            fetch(`get_customer_details.php?id=${customerId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const customer = data.customer;
                        let html = `
                            <div class="grid grid-cols-1 gap-4">
                                <div class="border-b pb-3">
                                    <h4 class="font-semibold text-gray-800 mb-2">Basic Information</h4>
                                    <p><span class="text-gray-600">Name:</span> ${customer.name}</p>
                                    <p><span class="text-gray-600">Email:</span> ${customer.email}</p>
                                    <p><span class="text-gray-600">Phone:</span> ${customer.phone}</p>
                                    <p><span class="text-gray-600">Registered on:</span> ${customer.created_at}</p>
                                </div>
                                <div class="border-b pb-3">
                                    <h4 class="font-semibold text-gray-800 mb-2">Address</h4>
                                    <p>${customer.address}</p>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-800 mb-2">Order Summary</h4>
                                    <p><span class="text-gray-600">Total Orders:</span> ${customer.order_count}</p>
                                    <p><span class="text-gray-600">Total Spent:</span> ${customer.total_spent}</p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <a href="customer_orders.php?user_id=${customer.id}" class="text-indigo-600 hover:text-indigo-900">
                                    <i class="fas fa-shopping-cart mr-1"></i> View All Orders
                                </a>
                            </div>
                        `;
                        document.getElementById('customerDetails').innerHTML = html;
                    } else {
                        document.getElementById('customerDetails').innerHTML = '<p class="text-center text-red-500 py-8">Error loading customer details. Please try again.</p>';
                    }
                })
                .catch(error => {
                    document.getElementById('customerDetails').innerHTML = '<p class="text-center text-red-500 py-8">Error loading customer details. Please try again.</p>';
                });
        }
        
        function closeCustomerModal() {
            document.getElementById('customerModal').classList.add('hidden');
        }
    </script>
</body>
</html> 