<?php
    $pageTitle = "Home";
    require_once 'includes/functions.php';
    
    // Get featured products
    $conn = connectDB();
    $sql = "SELECT * FROM products WHERE is_featured = 1 LIMIT 3";
    $result = $conn->query($sql);
    $featured_products = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $featured_products[] = $row;
        }
    }
    
    $conn->close();
    
    require_once 'layouts/header.php';
?>

<!-- Hero Section -->
<div class="relative pizza-gradient text-white rounded-lg shadow-xl overflow-hidden mb-12">
    <div class="container mx-auto px-6 py-12 md:py-24 z-10 relative">
        <div class="md:w-1/2">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Delicious Pizza Delivered To Your Door</h1>
            <p class="text-lg mb-8">Order your favorite pizza online with just a few clicks and enjoy the best flavors from our kitchen to your home.</p>
            <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                <a href="menu.php" class="bg-white text-red-600 font-semibold px-6 py-3 rounded-full inline-block text-center hover:bg-gray-100 transition">
                    Order Now
                </a>
                <a href="menu.php" class="border-2 border-white text-white font-semibold px-6 py-3 rounded-full inline-block text-center hover:bg-white hover:text-red-600 transition">
                    View Menu
                </a>
            </div>
        </div>
    </div>
    <div class="absolute right-0 bottom-0 w-2/5 h-full opacity-20 md:opacity-100">
        <img src="assets/images/pizza-hero.png" alt="Pizza" class="h-full object-contain object-right-bottom">
    </div>
</div>

<!-- Featured Products -->
<div class="mb-16">
    <h2 class="text-3xl font-bold text-center mb-8">Featured Pizzas</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <?php foreach ($featured_products as $product): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition">
                <div class="h-48 overflow-hidden">
                    <img src="uploads/products/<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>" class="w-full h-full object-cover">
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-semibold mb-2"><?php echo $product['name']; ?></h3>
                    <p class="text-gray-600 mb-4"><?php echo $product['description']; ?></p>
                    <div class="flex justify-between items-center">
                        <span class="text-xl font-bold text-red-600"><?php echo formatPrice($product['price']); ?></span>
                        <a href="menu.php" class="bg-red-600 text-white px-4 py-2 rounded-full hover:bg-red-700 transition">
                            Order Now
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        
        <?php if (empty($featured_products)): ?>
            <div class="col-span-3 text-center py-8">
                <p class="text-gray-500">No featured products available at the moment.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- How It Works -->
<div class="bg-white py-16 rounded-lg shadow-md mb-16">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">How It Works</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
            <div class="p-6">
                <div class="w-20 h-20 mx-auto bg-red-100 text-red-600 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-pizza-slice text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Choose Your Pizza</h3>
                <p class="text-gray-600">Browse our menu and select your favorite pizzas and sides.</p>
            </div>
            
            <div class="p-6">
                <div class="w-20 h-20 mx-auto bg-red-100 text-red-600 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-shopping-cart text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Place Your Order</h3>
                <p class="text-gray-600">Add items to your cart, proceed to checkout, and pay securely.</p>
            </div>
            
            <div class="p-6">
                <div class="w-20 h-20 mx-auto bg-red-100 text-red-600 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-truck text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Fast Delivery</h3>
                <p class="text-gray-600">Track your order as our delivery team brings fresh pizza to your door.</p>
            </div>
        </div>
    </div>
</div>

<!-- Customer Reviews -->
<div class="mb-16">
    <h2 class="text-3xl font-bold text-center mb-8">What Our Customers Say</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-4">
                    <span class="text-red-600 font-bold">JD</span>
                </div>
                <div>
                    <h4 class="font-semibold">John Doe</h4>
                    <div class="flex text-yellow-400">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                </div>
            </div>
            <p class="text-gray-600">"The pizza was amazing! Hot, fresh, and delivered right on time. Will definitely order again."</p>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-4">
                    <span class="text-red-600 font-bold">JS</span>
                </div>
                <div>
                    <h4 class="font-semibold">Jane Smith</h4>
                    <div class="flex text-yellow-400">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                </div>
            </div>
            <p class="text-gray-600">"Best pizza in town! Their online ordering system makes it so easy to get exactly what I want."</p>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-4">
                    <span class="text-red-600 font-bold">RJ</span>
                </div>
                <div>
                    <h4 class="font-semibold">Robert Johnson</h4>
                    <div class="flex text-yellow-400">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                </div>
            </div>
            <p class="text-gray-600">"The tracking feature is awesome - I knew exactly when my pizza was on the way. Great customer service!"</p>
        </div>
    </div>
</div>

<!-- Call to Action -->
<div class="pizza-gradient text-white p-8 rounded-lg shadow-xl text-center mb-16">
    <h2 class="text-3xl font-bold mb-4">Ready to Order?</h2>
    <p class="text-lg mb-6">Delicious pizzas are just a few clicks away!</p>
    <a href="menu.php" class="bg-white text-red-600 font-semibold px-8 py-3 rounded-full inline-block hover:bg-gray-100 transition">
        Order Now
    </a>
</div>

<?php require_once 'layouts/footer.php'; ?> 