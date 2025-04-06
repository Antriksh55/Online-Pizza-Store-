<?php
    $pageTitle = "Order Details";
    require_once 'includes/functions.php';
    
    // Redirect if not logged in
    if (!isLoggedIn()) {
        header("Location: login.php?redirect=orders.php");
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

<!-- Order Details -->
<div class="bg-white rounded-lg shadow-md p-6 mb-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Order #<?php echo $order_id; ?></h1>
        <a href="orders.php" class="text-red-600 hover:text-red-800">
            <i class="fas fa-arrow-left mr-1"></i> Back to Orders
        </a>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- Order Info -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h2 class="text-lg font-semibold mb-3">Order Information</h2>
            <div class="space-y-2">
                <p><span class="text-gray-600">Date:</span> <?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></p>
                <p><span class="text-gray-600">Status:</span> 
                    <span class="<?php
                        if ($order['order_status'] == 'delivered') {
                            echo 'text-green-600';
                        } elseif ($order['order_status'] == 'cancelled') {
                            echo 'text-red-600';
                        } else {
                            echo 'text-blue-600';
                        }
                    ?>">
                        <?php echo getOrderStatusText($order['order_status']); ?>
                    </span>
                </p>
                <p><span class="text-gray-600">Payment Method:</span> <?php echo $order['payment_method']; ?></p>
                <p><span class="text-gray-600">Payment Status:</span>
                    <span class="<?php echo $order['payment_status'] == 'completed' ? 'text-green-600' : 'text-amber-600'; ?>">
                        <?php echo ucfirst($order['payment_status']); ?>
                    </span>
                </p>
                <?php if ($order['payment_id']): ?>
                    <p><span class="text-gray-600">Payment ID:</span> <?php echo $order['payment_id']; ?></p>
                <?php endif; ?>
                <p><span class="text-gray-600">Total:</span> <span class="font-semibold text-red-600"><?php echo formatPrice($order['total_amount']); ?></span></p>
            </div>
        </div>
        
        <!-- Delivery Info -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h2 class="text-lg font-semibold mb-3">Delivery Information</h2>
            <div class="space-y-2">
                <p><span class="text-gray-600">Customer:</span> <?php echo $order['customer_name']; ?></p>
                <p><span class="text-gray-600">Email:</span> <?php echo $order['email']; ?></p>
                <p><span class="text-gray-600">Phone:</span> <?php echo $order['phone']; ?></p>
                <p><span class="text-gray-600">Address:</span> <?php echo $order['address']; ?></p>
            </div>
        </div>
    </div>
    
    <!-- Order Progress Tracker -->
    <div class="mb-8">
        <h2 class="text-lg font-semibold mb-4">Order Progress</h2>
        
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
    </div>
    
    <!-- Order Items -->
    <h2 class="text-lg font-semibold mb-4">Order Items</h2>
    
    <div class="overflow-x-auto mb-8">
        <table class="w-full">
            <thead class="bg-gray-100 text-gray-700">
                <tr>
                    <th class="py-3 px-4 text-left">Item</th>
                    <th class="py-3 px-4 text-center">Price</th>
                    <th class="py-3 px-4 text-center">Quantity</th>
                    <th class="py-3 px-4 text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order['items'] as $item): ?>
                    <tr class="border-b">
                        <td class="py-4 px-4">
                            <div class="flex items-center">
                                <img src="uploads/products/<?php echo $item['image_url']; ?>" alt="<?php echo $item['name']; ?>" class="w-12 h-12 object-cover rounded mr-3">
                                <span class="font-medium"><?php echo $item['name']; ?></span>
                            </div>
                        </td>
                        <td class="py-4 px-4 text-center text-gray-600">
                            <?php echo formatPrice($item['price']); ?>
                        </td>
                        <td class="py-4 px-4 text-center text-gray-600">
                            <?php echo $item['quantity']; ?>
                        </td>
                        <td class="py-4 px-4 text-right text-gray-800 font-medium">
                            <?php echo formatPrice($item['price'] * $item['quantity']); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr class="border-b bg-gray-50">
                    <td colspan="3" class="py-4 px-4 text-right font-semibold">Subtotal:</td>
                    <td class="py-4 px-4 text-right font-semibold"><?php echo formatPrice($order['total_amount'] - 50); ?></td>
                </tr>
                <tr class="border-b bg-gray-50">
                    <td colspan="3" class="py-4 px-4 text-right font-semibold">Delivery Fee:</td>
                    <td class="py-4 px-4 text-right font-semibold"><?php echo formatPrice(50); ?></td>
                </tr>
                <tr class="bg-gray-50">
                    <td colspan="3" class="py-4 px-4 text-right font-bold">Total:</td>
                    <td class="py-4 px-4 text-right font-bold text-red-600"><?php echo formatPrice($order['total_amount']); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <!-- Reorder Option -->
    <?php if ($order['order_status'] == 'delivered'): ?>
        <div class="text-center mt-8">
            <form action="reorder.php" method="post">
                <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                <button type="submit" class="bg-red-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-red-700 transition">
                    Reorder This Again
                </button>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'layouts/footer.php'; ?> 