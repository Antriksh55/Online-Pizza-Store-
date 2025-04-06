<?php
    $pageTitle = "Menu";
    require_once 'includes/functions.php';
    
    // Get category filter
    $category_filter = isset($_GET['category']) ? sanitize($_GET['category']) : '';
    
    // Get products
    $conn = connectDB();
    
    $sql = "SELECT * FROM products";
    if (!empty($category_filter)) {
        $sql .= " WHERE category = '" . $conn->real_escape_string($category_filter) . "'";
    }
    $sql .= " ORDER BY name ASC";
    
    $result = $conn->query($sql);
    $products = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    
    // Get all categories
    $sql = "SELECT DISTINCT category FROM products ORDER BY category ASC";
    $result = $conn->query($sql);
    $categories = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $categories[] = $row['category'];
        }
    }
    
    $conn->close();
    
    require_once 'layouts/header.php';
?>

<!-- Menu Hero Section -->
<div class="bg-red-600 text-white py-12 rounded-lg shadow-md mb-8">
    <div class="container mx-auto px-4 text-center">
        <h1 class="text-4xl font-bold mb-4">Our Menu</h1>
        <p class="text-lg max-w-2xl mx-auto">Explore our wide range of delicious pizzas and sides, made with the freshest ingredients and lots of love.</p>
    </div>
</div>

<!-- Category Filter -->
<div class="mb-8 flex flex-wrap justify-center gap-2">
    <a href="menu.php" class="px-4 py-2 rounded-full <?php echo empty($category_filter) ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?> transition">
        All
    </a>
    <?php foreach ($categories as $category): ?>
        <a href="menu.php?category=<?php echo urlencode($category); ?>" class="px-4 py-2 rounded-full <?php echo $category_filter === $category ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?> transition capitalize">
            <?php echo $category; ?>
        </a>
    <?php endforeach; ?>
</div>

<!-- Products Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
    <?php foreach ($products as $product): ?>
        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition">
            <div class="h-48 overflow-hidden">
                <img src="uploads/products/<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>" class="w-full h-full object-cover">
            </div>
            <div class="p-6">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="text-xl font-semibold"><?php echo $product['name']; ?></h3>
                    <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full capitalize"><?php echo $product['category']; ?></span>
                </div>
                <p class="text-gray-600 mb-4"><?php echo $product['description']; ?></p>
                <div class="flex justify-between items-center">
                    <span class="text-xl font-bold text-red-600"><?php echo formatPrice($product['price']); ?></span>
                    <div class="flex space-x-2">
                        <?php if (isLoggedIn()): ?>
                            <button onclick="addToWishlist(<?php echo $product['id']; ?>)" class="text-gray-500 hover:text-red-600 focus:outline-none <?php echo isInWishlist($product['id']) ? 'text-red-600' : ''; ?>">
                                <i class="<?php echo isInWishlist($product['id']) ? 'fas' : 'far'; ?> fa-heart"></i>
                            </button>
                            <button onclick="addToCart(<?php echo $product['id']; ?>)" class="bg-red-600 text-white px-4 py-2 rounded-full hover:bg-red-700 transition">
                                Add to Cart
                            </button>
                        <?php else: ?>
                            <a href="login.php" class="bg-red-600 text-white px-4 py-2 rounded-full hover:bg-red-700 transition">
                                Login to Order
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    
    <?php if (empty($products)): ?>
        <div class="col-span-3 text-center py-12">
            <i class="fas fa-pizza-slice text-gray-300 text-5xl mb-4"></i>
            <p class="text-gray-500 text-xl">No products found for this category.</p>
            <a href="menu.php" class="mt-4 inline-block text-red-600 hover:underline">View all products</a>
        </div>
    <?php endif; ?>
</div>

<script>
    function addToCart(productId) {
        fetch('ajax/add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=${productId}&quantity=1`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Product added to cart successfully!');
                // Reload page to update cart count
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
    
    function addToWishlist(productId) {
        fetch('ajax/add_to_wishlist.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=${productId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Product added to wishlist successfully!');
                // Reload page to update wishlist icon
                location.reload();
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