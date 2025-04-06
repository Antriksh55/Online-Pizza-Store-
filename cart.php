<?php
session_start();
require_once 'includes/functions.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header('Location: login.php?redirect=cart.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$conn = connectDB();

// Get cart items
$sql = "SELECT c.id as cart_id, c.quantity, p.*, c.quantity * p.price as subtotal 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ? 
        ORDER BY c.id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cartItems = $result->fetch_all(MYSQLI_ASSOC);

// Calculate totals
$cartSubtotal = 0;
$deliveryFee = 50; // Fixed delivery fee
$taxRate = 0.05; // 5% tax rate

foreach ($cartItems as $item) {
    $cartSubtotal += $item['subtotal'];
}

$taxAmount = $cartSubtotal * $taxRate;
$orderTotal = $cartSubtotal + $deliveryFee + $taxAmount;

$conn->close();

// Page title
$pageTitle = 'My Cart - Pizza Store';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Shopping Cart</h1>
    
    <?php if (empty($cartItems)): ?>
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <div class="text-5xl text-gray-300 mb-4">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <h2 class="text-2xl font-semibold text-gray-700 mb-4">Your cart is empty</h2>
            <p class="text-gray-600 mb-6">Add some delicious items to your cart and place an order</p>
            <a href="index.php" class="inline-block bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 transition duration-200">
                Browse Products
            </a>
        </div>
    <?php else: ?>
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Cart Items -->
            <div class="lg:w-2/3">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-4 bg-gray-50 border-b">
                        <h2 class="text-lg font-semibold text-gray-700">Items in Your Cart (<?php echo count($cartItems); ?>)</h2>
                    </div>
                    
                    <ul class="divide-y divide-gray-200">
                        <?php foreach ($cartItems as $item): ?>
                            <?php
                            $imagePath = getUploadPath() . $item['image'];
                            if (!file_exists($imagePath) || empty($item['image'])) {
                                $imagePath = 'assets/images/default-product.jpg';
                            } else {
                                $imagePath = 'uploads/' . $item['image'];
                            }
                            ?>
                            <li class="p-4 hover:bg-gray-50 transition-colors duration-150 cart-item" id="cart-item-<?php echo $item['cart_id']; ?>">
                                <div class="flex flex-col sm:flex-row items-start sm:items-center">
                                    <div class="sm:w-20 h-20 flex-shrink-0 mb-3 sm:mb-0">
                                        <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                             class="w-full h-full object-cover rounded">
                                    </div>
                                    
                                    <div class="flex-1 ml-0 sm:ml-4">
                                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start">
                                            <div>
                                                <h3 class="text-lg font-semibold text-gray-800 hover:text-red-600">
                                                    <a href="product.php?id=<?php echo $item['id']; ?>"><?php echo htmlspecialchars($item['name']); ?></a>
                                                </h3>
                                                <p class="text-sm text-gray-600 mt-1">
                                                    Unit Price: <?php echo formatPrice($item['price']); ?>
                                                </p>
                                            </div>
                                            
                                            <div class="mt-3 sm:mt-0 flex flex-col sm:items-end">
                                                <div class="flex items-center mb-2">
                                                    <label for="quantity-<?php echo $item['cart_id']; ?>" class="text-sm text-gray-600 mr-2 hidden sm:inline">Qty:</label>
                                                    <div class="flex items-center border rounded">
                                                        <button class="px-2 py-1 border-r text-gray-600 hover:bg-gray-100 decrease-qty" 
                                                                data-cart-id="<?php echo $item['cart_id']; ?>">
                                                            <i class="fas fa-minus text-xs"></i>
                                                        </button>
                                                        <input type="number" id="quantity-<?php echo $item['cart_id']; ?>" 
                                                               class="w-12 py-1 px-2 text-center cart-qty" 
                                                               value="<?php echo $item['quantity']; ?>" min="1" max="10"
                                                               data-cart-id="<?php echo $item['cart_id']; ?>">
                                                        <button class="px-2 py-1 border-l text-gray-600 hover:bg-gray-100 increase-qty" 
                                                                data-cart-id="<?php echo $item['cart_id']; ?>">
                                                            <i class="fas fa-plus text-xs"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                
                                                <div class="flex items-center justify-between">
                                                    <span class="font-bold text-green-600 item-subtotal">
                                                        <?php echo formatPrice($item['subtotal']); ?>
                                                    </span>
                                                    <button class="ml-4 text-sm text-red-500 hover:text-red-700 remove-btn"
                                                            data-cart-id="<?php echo $item['cart_id']; ?>">
                                                        <i class="fas fa-trash mr-1"></i> Remove
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div class="mt-6 flex justify-between">
                    <a href="index.php" class="inline-flex items-center text-red-600 hover:text-red-700">
                        <i class="fas fa-arrow-left mr-2"></i> Continue Shopping
                    </a>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="lg:w-1/3">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-4">
                    <h2 class="text-lg font-semibold text-gray-700 mb-4">Order Summary</h2>
                    
                    <div class="space-y-3 text-gray-700">
                        <div class="flex justify-between">
                            <span>Subtotal</span>
                            <span id="cart-subtotal"><?php echo formatPrice($cartSubtotal); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Delivery Fee</span>
                            <span><?php echo formatPrice($deliveryFee); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Tax (5%)</span>
                            <span id="cart-tax"><?php echo formatPrice($taxAmount); ?></span>
                        </div>
                        
                        <div class="border-t pt-3 mt-3">
                            <div class="flex justify-between font-bold text-lg">
                                <span>Total</span>
                                <span id="cart-total"><?php echo formatPrice($orderTotal); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <a href="checkout.php" class="mt-6 block w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 transition duration-200 text-center">
                        Proceed to Checkout
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update quantity
    function updateCartQuantity(cartId, quantity) {
        fetch('ajax/update_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'cart_id=' + cartId + '&quantity=' + quantity
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Update UI with new values
                updateCartTotals();
                showToast('Cart updated successfully');
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred', 'error');
        });
    }
    
    // Remove item
    const removeButtons = document.querySelectorAll('.remove-btn');
    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const cartId = this.getAttribute('data-cart-id');
            const cartItem = document.getElementById('cart-item-' + cartId);
            
            fetch('ajax/remove_from_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'cart_id=' + cartId
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Animate removal of item
                    cartItem.style.opacity = '0';
                    cartItem.style.maxHeight = '0';
                    cartItem.style.transition = 'opacity 0.3s, max-height 0.5s';
                    
                    setTimeout(() => {
                        cartItem.remove();
                        
                        // Check if cart is now empty
                        if (document.querySelectorAll('.cart-item').length === 0) {
                            location.reload(); // Reload to show empty state
                        } else {
                            updateCartTotals();
                        }
                    }, 300);
                    
                    showToast('Item removed from cart');
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred', 'error');
            });
        });
    });
    
    // Quantity input change
    const quantityInputs = document.querySelectorAll('.cart-qty');
    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            const cartId = this.getAttribute('data-cart-id');
            let quantity = parseInt(this.value);
            
            // Ensure quantity is between 1 and 10
            if (quantity < 1) quantity = 1;
            if (quantity > 10) quantity = 10;
            
            this.value = quantity;
            updateCartQuantity(cartId, quantity);
        });
    });
    
    // Increase quantity
    const increaseButtons = document.querySelectorAll('.increase-qty');
    increaseButtons.forEach(button => {
        button.addEventListener('click', function() {
            const cartId = this.getAttribute('data-cart-id');
            const input = document.getElementById('quantity-' + cartId);
            let quantity = parseInt(input.value) + 1;
            
            if (quantity > 10) quantity = 10;
            input.value = quantity;
            updateCartQuantity(cartId, quantity);
        });
    });
    
    // Decrease quantity
    const decreaseButtons = document.querySelectorAll('.decrease-qty');
    decreaseButtons.forEach(button => {
        button.addEventListener('click', function() {
            const cartId = this.getAttribute('data-cart-id');
            const input = document.getElementById('quantity-' + cartId);
            let quantity = parseInt(input.value) - 1;
            
            if (quantity < 1) quantity = 1;
            input.value = quantity;
            updateCartQuantity(cartId, quantity);
        });
    });
    
    // Update cart totals
    function updateCartTotals() {
        fetch('ajax/get_cart_totals.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Update subtotal and totals
                document.getElementById('cart-subtotal').textContent = data.subtotal;
                document.getElementById('cart-tax').textContent = data.tax;
                document.getElementById('cart-total').textContent = data.total;
                
                // Update each item's subtotal
                data.items.forEach(item => {
                    const itemElement = document.getElementById('cart-item-' + item.cart_id);
                    if (itemElement) {
                        const subtotalElement = itemElement.querySelector('.item-subtotal');
                        if (subtotalElement) {
                            subtotalElement.textContent = item.subtotal;
                        }
                    }
                });
            }
        })
        .catch(error => console.error('Error:', error));
    }
    
    // Simple toast notification
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `fixed bottom-4 right-4 z-50 p-4 rounded-lg ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white shadow-lg transform transition-transform duration-300 ease-in-out`;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('translate-y-20', 'opacity-0');
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 3000);
    }
});
</script>

<?php include 'includes/footer.php'; ?> 