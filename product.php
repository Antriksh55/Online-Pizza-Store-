<?php
session_start();
require_once 'includes/functions.php';

// Check if product ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$product_id = (int)$_GET['id'];
$conn = connectDB();

// Get product details
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id = ? AND p.active = 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Product not found or inactive
    header('Location: index.php');
    exit;
}

$product = $result->fetch_assoc();

// Get related products
$sql = "SELECT p.* FROM products p 
        WHERE p.category_id = ? AND p.id != ? AND p.active = 1 
        ORDER BY p.id DESC LIMIT 4";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $product['category_id'], $product_id);
$stmt->execute();
$relatedProducts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Check if product is in wishlist
$inWishlist = false;
if (isLoggedIn()) {
    $inWishlist = isInWishlist($product_id);
}

$conn->close();

// Get image path
$imagePath = getUploadPath() . $product['image'];
if (!file_exists($imagePath) || empty($product['image'])) {
    $imagePath = 'assets/images/default-product.jpg';
} else {
    $imagePath = 'uploads/' . $product['image'];
}

// Page title
$pageTitle = $product['name'] . ' - Pizza Store';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="index.php" class="text-sm text-gray-600 hover:text-red-500">
            <i class="fas fa-arrow-left mr-2"></i>Back to Products
        </a>
    </div>
    
    <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-8">
        <div class="md:flex">
            <div class="md:w-1/2">
                <div class="h-96 overflow-hidden">
                    <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" 
                         class="w-full h-full object-cover">
                </div>
            </div>
            <div class="md:w-1/2 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h1 class="text-3xl font-bold text-gray-800"><?php echo htmlspecialchars($product['name']); ?></h1>
                    <?php if (isLoggedIn()): ?>
                        <button id="wishlist-btn" class="focus:outline-none" data-product-id="<?php echo $product_id; ?>">
                            <i class="<?php echo $inWishlist ? 'fas' : 'far'; ?> fa-heart text-2xl <?php echo $inWishlist ? 'text-red-500' : 'text-gray-400'; ?> hover:text-red-500"></i>
                        </button>
                    <?php endif; ?>
                </div>
                
                <div class="mb-4">
                    <span class="inline-block bg-red-100 text-red-800 text-sm font-semibold px-3 py-1 rounded-full">
                        <?php echo htmlspecialchars($product['category_name']); ?>
                    </span>
                </div>
                
                <div class="mb-6">
                    <p class="text-2xl font-bold text-red-600 mb-2"><?php echo formatPrice($product['price']); ?></p>
                    <p class="text-gray-700 leading-relaxed mb-4"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    
                    <?php if ($product['active']): ?>
                        <div class="inline-block bg-green-100 text-green-800 text-sm font-semibold px-3 py-1 rounded-full mb-4">
                            In Stock
                        </div>
                    <?php else: ?>
                        <div class="inline-block bg-gray-100 text-gray-800 text-sm font-semibold px-3 py-1 rounded-full mb-4">
                            Out of Stock
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($product['active']): ?>
                    <div class="mt-6">
                        <div class="flex items-center mb-4">
                            <div class="mr-4">
                                <label for="quantity" class="text-gray-700 font-medium block mb-1">Quantity</label>
                                <select id="quantity" class="form-select border border-gray-300 rounded px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-red-500">
                                    <?php for ($i = 1; $i <= 10; $i++): ?>
                                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        
                        <button id="add-to-cart-btn" data-product-id="<?php echo $product_id; ?>" 
                                class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 transition duration-200">
                            <i class="fas fa-shopping-cart mr-2"></i>Add to Cart
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php if (count($relatedProducts) > 0): ?>
    <div class="mt-12">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">You May Also Like</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($relatedProducts as $related): ?>
                <?php
                $relatedImagePath = getUploadPath() . $related['image'];
                if (!file_exists($relatedImagePath) || empty($related['image'])) {
                    $relatedImagePath = 'assets/images/default-product.jpg';
                } else {
                    $relatedImagePath = 'uploads/' . $related['image'];
                }
                ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <a href="product.php?id=<?php echo $related['id']; ?>">
                        <div class="h-48 overflow-hidden">
                            <img src="<?php echo htmlspecialchars($relatedImagePath); ?>" alt="<?php echo htmlspecialchars($related['name']); ?>" class="w-full h-full object-cover hover:scale-105 transition duration-300">
                        </div>
                    </a>
                    <div class="p-4">
                        <a href="product.php?id=<?php echo $related['id']; ?>" class="text-lg font-semibold text-gray-800 hover:text-red-600"><?php echo htmlspecialchars($related['name']); ?></a>
                        <p class="text-red-600 font-bold mt-2"><?php echo formatPrice($related['price']); ?></p>
                        <div class="mt-4">
                            <a href="product.php?id=<?php echo $related['id']; ?>" class="inline-block px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition duration-200">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Wishlist toggle
    const wishlistBtn = document.getElementById('wishlist-btn');
    if (wishlistBtn) {
        wishlistBtn.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            
            fetch('ajax/toggle_wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + productId
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const heartIcon = wishlistBtn.querySelector('i');
                    
                    if (data.action === 'added') {
                        heartIcon.classList.remove('far');
                        heartIcon.classList.add('fas', 'text-red-500');
                    } else {
                        heartIcon.classList.remove('fas', 'text-red-500');
                        heartIcon.classList.add('far', 'text-gray-400');
                    }
                    
                    // Show toast notification
                    showToast(data.message);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred', 'error');
            });
        });
    }
    
    // Add to cart
    const addToCartBtn = document.getElementById('add-to-cart-btn');
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            const quantity = document.getElementById('quantity').value;
            
            fetch('ajax/add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + productId + '&quantity=' + quantity
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showToast('Product added to cart');
                    
                    // Update cart count in header if exists
                    const cartCountEl = document.querySelector('.cart-count');
                    if (cartCountEl && data.cart_count) {
                        cartCountEl.textContent = data.cart_count;
                    }
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred', 'error');
            });
        });
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