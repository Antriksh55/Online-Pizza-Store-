<?php
    $pageTitle = "Checkout";
    require_once 'includes/functions.php';
    
    // Redirect if not logged in
    if (!isLoggedIn()) {
        header("Location: login.php?redirect=checkout.php");
        exit();
    }
    
    // Redirect if cart is empty
    $conn = connectDB();
    $user_id = $_SESSION['user_id'];
    
    $sql = "SELECT COUNT(*) as count FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        header("Location: cart.php");
        exit();
    }
    
    // Get cart items and total
    $sql = "SELECT c.*, p.name, p.price, p.image_url 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $cart_items = [];
    $subtotal = 0;
    
    while($row = $result->fetch_assoc()) {
        $row['subtotal'] = $row['price'] * $row['quantity'];
        $subtotal += $row['subtotal'];
        $cart_items[] = $row;
    }
    
    // Get user details
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    $delivery_fee = 50;
    $total = $subtotal + $delivery_fee;
    
    $conn->close();
    
    // Razorpay API details
    $razorpay_key_id = "rzp_test_your_key_id";  // Replace with your Razorpay Key ID
    $razorpay_order_id = "order_" . time(); // This should be generated from your backend using Razorpay API
    
    require_once 'layouts/header.php';
?>

<!-- Checkout Page -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
    <!-- Order Summary -->
    <div class="md:col-span-2">
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-2xl font-bold mb-6">Order Summary</h2>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th class="py-3 px-4 text-left">Item</th>
                            <th class="py-3 px-4 text-left">Price</th>
                            <th class="py-3 px-4 text-left">Quantity</th>
                            <th class="py-3 px-4 text-left">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr class="border-b">
                                <td class="py-3 px-4">
                                    <div class="flex items-center">
                                        <img src="uploads/products/<?php echo $item['image_url']; ?>" alt="<?php echo $item['name']; ?>" class="w-12 h-12 object-cover rounded mr-3">
                                        <span class="font-medium"><?php echo $item['name']; ?></span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-gray-600"><?php echo formatPrice($item['price']); ?></td>
                                <td class="py-3 px-4 text-gray-600"><?php echo $item['quantity']; ?></td>
                                <td class="py-3 px-4 text-gray-600"><?php echo formatPrice($item['subtotal']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Delivery Address -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">Delivery Address</h2>
            
            <div class="border rounded-lg p-4 bg-gray-50">
                <p class="font-medium"><?php echo $user['name']; ?></p>
                <p class="text-gray-600 mt-1"><?php echo $user['phone']; ?></p>
                <p class="text-gray-600 mt-1"><?php echo $user['email']; ?></p>
                <p class="text-gray-600 mt-2"><?php echo $user['address']; ?></p>
            </div>
        </div>
    </div>
    
    <!-- Payment Section -->
    <div class="md:col-span-1">
        <div class="bg-white rounded-lg shadow-md p-6 sticky top-8">
            <h2 class="text-xl font-bold mb-4">Payment Summary</h2>
            
            <div class="space-y-3 mb-4">
                <div class="flex justify-between">
                    <span class="text-gray-600">Subtotal</span>
                    <span><?php echo formatPrice($subtotal); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Delivery Fee</span>
                    <span><?php echo formatPrice($delivery_fee); ?></span>
                </div>
                <div class="border-t pt-3 mt-3 flex justify-between font-bold">
                    <span>Total</span>
                    <span class="text-red-600"><?php echo formatPrice($total); ?></span>
                </div>
            </div>
            
            <h3 class="font-semibold mb-3">Payment Method</h3>
            
            <div class="space-y-3 mb-6">
                <label class="flex items-center p-3 border rounded-lg bg-gray-50 cursor-pointer">
                    <input type="radio" name="payment_method" value="razorpay" checked class="mr-3">
                    <div>
                        <p class="font-medium">Razorpay UPI</p>
                        <p class="text-sm text-gray-500">Pay using UPI via Razorpay</p>
                    </div>
                </label>
                
                <label class="flex items-center p-3 border rounded-lg bg-gray-50 cursor-pointer">
                    <input type="radio" name="payment_method" value="cod" class="mr-3">
                    <div>
                        <p class="font-medium">Cash on Delivery</p>
                        <p class="text-sm text-gray-500">Pay when your order arrives</p>
                    </div>
                </label>
            </div>
            
            <button id="rzp-button" class="w-full bg-red-600 text-white py-3 rounded-lg font-semibold hover:bg-red-700 transition">
                Pay Now (Razorpay UPI)
            </button>
            
            <form id="cod-form" action="place_order.php" method="post" class="hidden">
                <input type="hidden" name="payment_method" value="COD">
                <button type="submit" class="w-full bg-red-600 text-white py-3 rounded-lg font-semibold hover:bg-red-700 transition mt-4">
                    Place Order (COD)
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Razorpay Integration -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Show/hide payment buttons based on selected method
        const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
        const rzpButton = document.getElementById('rzp-button');
        const codForm = document.getElementById('cod-form');
        
        paymentRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'razorpay') {
                    rzpButton.classList.remove('hidden');
                    codForm.classList.add('hidden');
                } else {
                    rzpButton.classList.add('hidden');
                    codForm.classList.remove('hidden');
                }
            });
        });
        
        // Razorpay integration
        const options = {
            key: "<?php echo $razorpay_key_id; ?>",
            amount: "<?php echo $total * 100; ?>", // Razorpay takes amount in paise
            currency: "INR",
            name: "Pizza Store",
            description: "Pizza Order Payment",
            order_id: "<?php echo $razorpay_order_id; ?>",
            handler: function(response) {
                // Send payment details to server
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'place_order.php';
                
                const paymentMethod = document.createElement('input');
                paymentMethod.name = 'payment_method';
                paymentMethod.value = 'Razorpay';
                form.appendChild(paymentMethod);
                
                const paymentId = document.createElement('input');
                paymentId.name = 'payment_id';
                paymentId.value = response.razorpay_payment_id;
                form.appendChild(paymentId);
                
                document.body.appendChild(form);
                form.submit();
            },
            prefill: {
                name: "<?php echo $user['name']; ?>",
                email: "<?php echo $user['email']; ?>",
                contact: "<?php echo $user['phone']; ?>"
            },
            theme: {
                color: "#DC2626"
            }
        };
        
        const rzp = new Razorpay(options);
        
        rzpButton.addEventListener('click', function(e) {
            rzp.open();
            e.preventDefault();
        });
    });
</script>

<?php require_once 'layouts/footer.php'; ?> 