<?php
    $pageTitle = "Shopping Cart";
    require_once 'includes/functions.php';
    
    // Redirect if not logged in
    if (!isLoggedIn()) {
        header("Location: login.php?redirect=cart.php");
        exit();
    }
    
    // Get cart items
    $conn = connectDB();
    $user_id = $_SESSION['user_id'];
    
    $sql = "SELECT c.*, p.name, p.price, p.image_url 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $cart_items = [];
    $total = 0;
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $row['subtotal'] = $row['price'] * $row['quantity'];
            $total += $row['subtotal'];
            $cart_items[] = $row;
        }
    }
    
    $conn->close();
    
    require_once 'layouts/header.php';
?>

<!-- Cart Page -->
<div class="bg-white rounded-lg shadow-md p-6 mb-8">
    <h1 class="text-2xl font-bold mb-6">Shopping Cart</h1>
    
    <?php if (!empty($cart_items)): ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-100 text-gray-700">
                    <tr>
                        <th class="py-3 px-4 text-left">Product</th>
                        <th class="py-3 px-4 text-left">Price</th>
                        <th class="py-3 px-4 text-left">Quantity</th>
                        <th class="py-3 px-4 text-left">Subtotal</th>
                        <th class="py-3 px-4 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr class="border-b" id="cart-item-<?php echo $item['id']; ?>">
                            <td class="py-4 px-4">
                                <div class="flex items-center">
                                    <img src="uploads/products/<?php echo $item['image_url']; ?>" alt="<?php echo $item['name']; ?>" class="w-16 h-16 object-cover rounded mr-4">
                                    <span class="font-medium"><?php echo $item['name']; ?></span>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-gray-700">
                                <?php echo formatPrice($item['price']); ?>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center">
                                    <button onclick="updateQuantity(<?php echo $item['id']; ?>, <?php echo $item['quantity'] - 1; ?>)" class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center hover:bg-gray-300">
                                        <i class="fas fa-minus text-sm"></i>
                                    </button>
                                    <span class="mx-3" id="quantity-<?php echo $item['id']; ?>"><?php echo $item['quantity']; ?></span>
                                    <button onclick="updateQuantity(<?php echo $item['id']; ?>, <?php echo $item['quantity'] + 1; ?>)" class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center hover:bg-gray-300">
                                        <i class="fas fa-plus text-sm"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-gray-700" id="subtotal-<?php echo $item['id']; ?>">
                                <?php echo formatPrice($item['subtotal']); ?>
                            </td>
                            <td class="py-4 px-4">
                                <button onclick="removeItem(<?php echo $item['id']; ?>)" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="mt-8 flex flex-col md:flex-row justify-between items-start">
            <div class="mb-4 md:mb-0">
                <a href="menu.php" class="flex items-center text-red-600 hover:text-red-800">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Continue Shopping
                </a>
            </div>
            
            <div class="bg-gray-100 p-6 rounded-lg w-full md:w-1/3">
                <h3 class="text-lg font-semibold mb-4">Order Summary</h3>
                
                <div class="flex justify-between mb-2">
                    <span class="text-gray-600">Subtotal</span>
                    <span id="cart-total"><?php echo formatPrice($total); ?></span>
                </div>
                
                <div class="flex justify-between mb-2">
                    <span class="text-gray-600">Delivery Fee</span>
                    <span><?php echo formatPrice(50); ?></span>
                </div>
                
                <div class="border-t pt-2 mt-2">
                    <div class="flex justify-between mb-2">
                        <span class="font-semibold">Total</span>
                        <span class="font-bold text-red-600" id="grand-total"><?php echo formatPrice($total + 50); ?></span>
                    </div>
                </div>
                
                <a href="checkout.php" class="block w-full bg-red-600 text-white text-center py-3 rounded-lg mt-4 hover:bg-red-700 transition">
                    Proceed to Checkout
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="text-center py-12">
            <i class="fas fa-shopping-cart text-gray-300 text-5xl mb-4"></i>
            <p class="text-gray-500 text-xl mb-6">Your cart is empty</p>
            <a href="menu.php" class="bg-red-600 text-white px-6 py-3 rounded-full hover:bg-red-700 transition">
                Start Shopping
            </a>
        </div>
    <?php endif; ?>
</div>

<script>
    function updateQuantity(cartId, quantity) {
        if (quantity <= 0) {
            if (confirm('Are you sure you want to remove this item from your cart?')) {
                removeItem(cartId);
            }
            return;
        }
        
        fetch('ajax/update_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `cart_id=${cartId}&quantity=${quantity}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update quantity display
                document.getElementById(`quantity-${cartId}`).innerText = quantity;
                
                // Update subtotal display
                document.getElementById(`subtotal-${cartId}`).innerText = data.item_subtotal;
                
                // Update cart total
                document.getElementById('cart-total').innerText = data.cart_subtotal;
                document.getElementById('grand-total').innerText = data.cart_total;
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
    
    function removeItem(cartId) {
        fetch('ajax/remove_from_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `cart_id=${cartId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove item from DOM
                document.getElementById(`cart-item-${cartId}`).remove();
                
                // Update cart total
                document.getElementById('cart-total').innerText = data.cart_subtotal;
                document.getElementById('grand-total').innerText = data.cart_total;
                
                // If cart is empty, refresh page to show empty cart message
                if (data.cart_empty) {
                    location.reload();
                }
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
</script>

<?php require_once 'layouts/footer.php'; ?> 