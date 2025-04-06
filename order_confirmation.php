<?php
    $pageTitle = "Order Confirmation";
    require_once 'includes/functions.php';
    
    // Redirect if not logged in
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
    
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
    
    require_once 'layouts/header.php';
?>

<!-- Order Confirmation -->
<div class="bg-white rounded-lg shadow-md p-8 mb-8 text-center">
    <div class="text-green-500 mb-4">
        <i class="fas fa-check-circle text-6xl"></i>
    </div>
    
    <h1 class="text-3xl font-bold mb-4">Order Confirmed!</h1>
    <p class="text-gray-600 mb-6">Thank you for your order. Your order #<?php echo $order_id; ?> has been placed successfully.</p>
    
    <div class="inline-block bg-gray-100 rounded-lg px-6 py-4 text-left mb-8">
        <p class="font-medium">Order Total: <span class="text-red-600"><?php echo formatPrice($order['total_amount']); ?></span></p>
        <p class="text-gray-600 mt-1">Payment Method: <?php echo $order['payment_method']; ?></p>
        <p class="text-gray-600 mt-1">Order Date: <?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></p>
    </div>
    
    <div class="mb-8">
        <h2 class="text-xl font-bold mb-4">Track Your Order</h2>
        
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
                    if ($order['order_status'] == 'confirmed') echo '10%';
                    elseif ($order['order_status'] == 'preparing') echo '35%';
                    elseif ($order['order_status'] == 'out_for_delivery') echo '70%';
                    elseif ($order['order_status'] == 'delivered') echo '100%';
                    else echo '0%';
                ?>;"></div>
            </div>
        </div>
        
        <p class="text-gray-600 mt-6">
            Current Status: <strong><?php echo getOrderStatusText($order['order_status']); ?></strong>
        </p>
    </div>
    
    <h2 class="text-xl font-bold mb-4">Order Items</h2>
    
    <div class="border rounded-lg overflow-hidden mb-8">
        <table class="w-full">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-3 px-4 text-left">Item</th>
                    <th class="py-3 px-4 text-right">Quantity</th>
                    <th class="py-3 px-4 text-right">Price</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order['items'] as $item): ?>
                    <tr class="border-t">
                        <td class="py-3 px-4">
                            <div class="flex items-center">
                                <img src="uploads/products/<?php echo $item['image_url']; ?>" alt="<?php echo $item['name']; ?>" class="w-12 h-12 object-cover rounded mr-3">
                                <span class="font-medium"><?php echo $item['name']; ?></span>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <?php echo $item['quantity']; ?>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <?php echo formatPrice($item['price'] * $item['quantity']); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="flex justify-center space-x-4">
        <a href="orders.php" class="bg-red-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-red-700 transition">
            View All Orders
        </a>
        <a href="index.php" class="bg-gray-200 text-gray-800 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition">
            Back to Home
        </a>
    </div>
</div>

<?php require_once 'layouts/footer.php'; ?> 