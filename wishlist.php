<?php
    $pageTitle = "My Wishlist";
    require_once 'includes/functions.php';
    
    // Redirect if not logged in
    if (!isLoggedIn()) {
        header("Location: login.php?redirect=wishlist.php");
        exit();
    }
    
    // Get wishlist items
    $conn = connectDB();
    $user_id = $_SESSION['user_id'];
    
    $sql = "SELECT w.*, p.name, p.price, p.description, p.image_url 
            FROM wishlist w 
            JOIN products p ON w.product_id = p.id 
            WHERE w.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $wishlist_items = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $wishlist_items[] = $row;
        }
    }
    
    $conn->close();
    
    require_once 'layouts/header.php';
?>

<!-- Wishlist Page -->
<div class="bg-white rounded-lg shadow-md p-6 mb-8">
    <h1 class="text-2xl font-bold mb-6">My Wishlist</h1>
    
    <?php if (!empty($wishlist_items)): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($wishlist_items as $item): ?>
                <div class="border rounded-lg overflow-hidden shadow-sm hover:shadow-md transition" id="wishlist-item-<?php echo $item['id']; ?>">
                    <div class="h-48 overflow-hidden">
                        <img src="uploads/products/<?php echo $item['image_url']; ?>" alt="<?php echo $item['name']; ?>" class="w-full h-full object-cover">
                    </div>
                    <div class="p-4">
                        <h3 class="text-lg font-semibold mb-2"><?php echo $item['name']; ?></h3>
                        <p class="text-gray-600 text-sm mb-4"><?php echo $item['description']; ?></p>
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-bold text-red-600"><?php echo formatPrice($item['price']); ?></span>
                            <div class="flex space-x-2">
                                <button onclick="removeFromWishlist(<?php echo $item['id']; ?>)" class="text-gray-500 hover:text-red-600 focus:outline-none">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <button onclick="addToCart(<?php echo $item['product_id']; ?>)" class="bg-red-600 text-white px-4 py-2 rounded-full hover:bg-red-700 transition text-sm">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-12">
            <i class="far fa-heart text-gray-300 text-5xl mb-4"></i>
            <p class="text-gray-500 text-xl mb-6">Your wishlist is empty</p>
            <a href="menu.php" class="bg-red-600 text-white px-6 py-3 rounded-full hover:bg-red-700 transition">
                Start Shopping
            </a>
        </div>
    <?php endif; ?>
</div>

<script>
    function removeFromWishlist(wishlistId) {
        if (confirm('Are you sure you want to remove this item from your wishlist?')) {
            fetch('ajax/remove_from_wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `wishlist_id=${wishlistId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove item from DOM
                    document.getElementById(`wishlist-item-${wishlistId}`).remove();
                    
                    // If wishlist is empty, refresh page to show empty wishlist message
                    if (data.wishlist_empty) {
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
    }
    
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
</script>

<?php require_once 'layouts/footer.php'; ?>