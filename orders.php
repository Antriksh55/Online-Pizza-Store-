<?php
    $pageTitle = "My Orders";
    require_once 'includes/functions.php';
    
    // Redirect if not logged in
    if (!isLoggedIn()) {
        header("Location: login.php?redirect=orders.php");
        exit();
    }
    
    // Get user orders
    $orders = getUserOrders();
    
    require_once 'layouts/header.php';
?>

<!-- Orders Page -->
<div class="bg-white rounded-lg shadow-md p-6 mb-8">
    <h1 class="text-2xl font-bold mb-6">My Orders</h1>
    
    <?php if (!empty($orders)): ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-100 text-gray-700">
                    <tr>
                        <th class="py-3 px-4 text-left">Order ID</th>
                        <th class="py-3 px-4 text-left">Date</th>
                        <th class="py-3 px-4 text-left">Total</th>
                        <th class="py-3 px-4 text-left">Status</th>
                        <th class="py-3 px-4 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr class="border-b">
                            <td class="py-4 px-4 font-medium">
                                #<?php echo $order['id']; ?>
                            </td>
                            <td class="py-4 px-4 text-gray-600">
                                <?php echo date('M j, Y, g:i a', strtotime($order['created_at'])); ?>
                            </td>
                            <td class="py-4 px-4 text-gray-600">
                                <?php echo formatPrice($order['total_amount']); ?>
                            </td>
                            <td class="py-4 px-4">
                                <span class="<?php
                                    if ($order['order_status'] == 'delivered') {
                                        echo 'bg-green-100 text-green-800';
                                    } elseif ($order['order_status'] == 'cancelled') {
                                        echo 'bg-red-100 text-red-800';
                                    } else {
                                        echo 'bg-blue-100 text-blue-800';
                                    }
                                ?> text-xs px-2 py-1 rounded-full">
                                    <?php echo getOrderStatusText($order['order_status']); ?>
                                </span>
                            </td>
                            <td class="py-4 px-4">
                                <a href="order_details.php?id=<?php echo $order['id']; ?>" class="text-red-600 hover:text-red-800">
                                    View Details
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="text-center py-12">
            <i class="fas fa-shopping-bag text-gray-300 text-5xl mb-4"></i>
            <p class="text-gray-500 text-xl mb-6">You haven't placed any orders yet</p>
            <a href="menu.php" class="bg-red-600 text-white px-6 py-3 rounded-full hover:bg-red-700 transition">
                Start Shopping
            </a>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'layouts/footer.php'; ?> 