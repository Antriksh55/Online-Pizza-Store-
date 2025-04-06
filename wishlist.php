<?php
session_start();
require_once 'includes/functions.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$conn = connectDB();
$user_id = $_SESSION['user_id'];

// Get wishlist items
$sql = "SELECT w.id as wishlist_id, p.*, c.name as category_name 
        FROM wishlist w 
        JOIN products p ON w.product_id = p.id 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE w.user_id = ? 
        ORDER BY w.id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$wishlistItems = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();

// Page title
$pageTitle = 'My Wishlist - Pizza Store';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">My Wishlist</h1>
    
    <?php if (empty($wishlistItems)): ?>
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <div class="text-5xl text-gray-300 mb-4">
                <i class="far fa-heart"></i>
            </div>
            <h2 class="text-2xl font-semibold text-gray-700 mb-4">Your wishlist is empty</h2>
            <p class="text-gray-600 mb-6">Browse our products and add items to your wishlist</p>
            <a href="index.php" class="inline-block bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 transition duration-200">
                Browse Products
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php foreach ($wishlistItems as $item): ?>
                <?php
                $imagePath = getUploadPath() . $item['image'];
                if (!file_exists($imagePath) || empty($item['image'])) {
                    $imagePath = 'assets/images/default-product.jpg';
                } else {
                    $imagePath = 'uploads/' . $item['image'];
                }
                ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden relative" id="wishlist-item-<?php echo $item['wishlist_id']; ?>">
                    <button class="absolute top-3 right-3 z-10 bg-white rounded-full p-2 shadow hover:bg-red-100 focus:outline-none remove-wishlist-btn" 
                            data-id="<?php echo $item['wishlist_id']; ?>" 
                            data-product-id="<?php echo $item['id']; ?>">
                        <i class="fas fa-times text-red-500"></i>
                    </button>
                    
                    <a href="product.php?id=<?php echo $item['id']; ?>">
                        <div class="h-48 overflow-hidden">
                            <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                 class="w-full h-full object-cover hover:scale-105 transition duration-300">
                        </div>
                    </a>
                    
                    <div class="p-4">
                        <div class="mb-2">
                            <span class="inline-block bg-red-100 text-red-800 text-xs font-semibold px-2 py-1 rounded-full">
                                <?php echo htmlspecialchars($item['category_name']); ?>
                            </span>
                        </div>
                        
                        <a href="product.php?id=<?php echo $item['id']; ?>" class="text-lg font-semibold text-gray-800 hover:text-red-600">
                            <?php echo htmlspecialchars($item['name']); ?>
                        </a>
                        
                        <p class="text-red-600 font-bold mt-2"><?php echo formatPrice($item['price']); ?></p>
                        
                        <div class="mt-4 flex justify-between">
                            <a href="product.php?id=<?php echo $item['id']; ?>" class="inline-block px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition duration-200">
                                View Details
                            </a>
                            
                            <button class="inline-block px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition duration-200 add-to-cart-btn" 
                                    data-product-id="<?php echo $item['id']; ?>">
                                <i class="fas fa-shopping-cart mr-1"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Remove from wishlist
    const removeButtons = document.querySelectorAll('.remove-wishlist-btn');
    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            const wishlistItemId = this.getAttribute('data-id');
            const wishlistItem = document.getElementById('wishlist-item-' + wishlistItemId);
            
            fetch('ajax/toggle_wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + productId
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success' && data.action === 'removed') {
                    // Animate removal of item
                    wishlistItem.style.opacity = '0';
                    wishlistItem.style.transform = 'scale(0.8)';
                    wishlistItem.style.transition = 'opacity 0.3s, transform 0.3s';
                    
                    setTimeout(() => {
                        wishlistItem.remove();
                        
                        // Check if wishlist is now empty
                        if (document.querySelectorAll('.remove-wishlist-btn').length === 0) {
                            location.reload(); // Reload to show empty state
                        }
                    }, 300);
                    
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
    });
    
    // Add to cart
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            
            fetch('ajax/add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + productId + '&quantity=1'
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
    });
    
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