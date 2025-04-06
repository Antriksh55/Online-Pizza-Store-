<?php
session_start();
require_once 'config/database.php';

// Authentication functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is logged in as admin
 *
 * @return bool True if user is logged in as admin, false otherwise
 */
function isAdmin() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header("Location: admin/login.php");
        exit();
    }
}

// Sanitize input function
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Cart functions
function getCartCount() {
    if (!isLoggedIn()) {
        return 0;
    }
    
    $conn = connectDB();
    $user_id = $_SESSION['user_id'];
    
    $sql = "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $conn->close();
    
    return $row['total'] ? $row['total'] : 0;
}

function getWishlistCount() {
    if (!isLoggedIn()) {
        return 0;
    }
    
    $conn = connectDB();
    $user_id = $_SESSION['user_id'];
    
    $sql = "SELECT COUNT(*) as total FROM wishlist WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $conn->close();
    
    return $row['total'] ? $row['total'] : 0;
}

function addToCart($product_id, $quantity = 1) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $conn = connectDB();
    $user_id = $_SESSION['user_id'];
    
    // Check if item already exists in cart
    $sql = "SELECT * FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update quantity
        $row = $result->fetch_assoc();
        $new_quantity = $row['quantity'] + $quantity;
        
        $sql = "UPDATE cart SET quantity = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $new_quantity, $row['id']);
        $success = $stmt->execute();
    } else {
        // Add new item
        $sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $user_id, $product_id, $quantity);
        $success = $stmt->execute();
    }
    
    $conn->close();
    return $success;
}

function removeFromCart($cart_id) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $conn = connectDB();
    $user_id = $_SESSION['user_id'];
    
    $sql = "DELETE FROM cart WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $cart_id, $user_id);
    $success = $stmt->execute();
    
    $conn->close();
    return $success;
}

function updateCartQuantity($cart_id, $quantity) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $conn = connectDB();
    $user_id = $_SESSION['user_id'];
    
    if ($quantity <= 0) {
        return removeFromCart($cart_id);
    }
    
    $sql = "UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $quantity, $cart_id, $user_id);
    $success = $stmt->execute();
    
    $conn->close();
    return $success;
}

function clearCart() {
    if (!isLoggedIn()) {
        return false;
    }
    
    $conn = connectDB();
    $user_id = $_SESSION['user_id'];
    
    $sql = "DELETE FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $success = $stmt->execute();
    
    $conn->close();
    return $success;
}

// Wishlist functions
function addToWishlist($product_id) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $conn = connectDB();
    $user_id = $_SESSION['user_id'];
    
    // Check if product already in wishlist
    $sql = "SELECT * FROM wishlist WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Already in wishlist
        $conn->close();
        return true;
    }
    
    // Add to wishlist
    $sql = "INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $product_id);
    $success = $stmt->execute();
    
    $conn->close();
    return $success;
}

function removeFromWishlist($wishlist_id) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $conn = connectDB();
    $user_id = $_SESSION['user_id'];
    
    $sql = "DELETE FROM wishlist WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $wishlist_id, $user_id);
    $success = $stmt->execute();
    
    $conn->close();
    return $success;
}

// Format price
function formatPrice($price) {
    return '₹' . number_format($price, 2);
}

// Check if product is in wishlist
function isInWishlist($product_id) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $conn = connectDB();
    $user_id = $_SESSION['user_id'];
    
    $sql = "SELECT * FROM wishlist WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $conn->close();
    return $result->num_rows > 0;
}

// Get product by ID
function getProduct($product_id) {
    $conn = connectDB();
    
    $sql = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    
    $conn->close();
    return $product;
}

// Create new order
function createOrder($payment_method, $payment_id = null) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $conn = connectDB();
    $user_id = $_SESSION['user_id'];
    
    // Get cart items
    $sql = "SELECT c.*, p.price FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        $conn->close();
        return false;
    }
    
    // Calculate total
    $total_amount = 0;
    $items = [];
    
    while ($row = $result->fetch_assoc()) {
        $subtotal = $row['price'] * $row['quantity'];
        $total_amount += $subtotal;
        $items[] = $row;
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Create order
        $sql = "INSERT INTO orders (user_id, total_amount, payment_method, payment_id, payment_status) 
                VALUES (?, ?, ?, ?, ?)";
        $payment_status = ($payment_method == 'COD') ? 'pending' : 'completed';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("idsss", $user_id, $total_amount, $payment_method, $payment_id, $payment_status);
        $stmt->execute();
        
        $order_id = $conn->insert_id;
        
        // Add order items
        $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        foreach ($items as $item) {
            $stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
            $stmt->execute();
        }
        
        // Clear cart
        $sql = "DELETE FROM cart WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        $conn->close();
        return $order_id;
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $conn->close();
        return false;
    }
}

// Get order details
function getOrder($order_id) {
    if (!isLoggedIn() && !isAdmin()) {
        return false;
    }
    
    $conn = connectDB();
    
    $sql = "SELECT o.*, u.name as customer_name, u.email, u.phone, u.address 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            WHERE o.id = ?";
            
    if (isLoggedIn() && !isAdmin()) {
        $sql .= " AND o.user_id = " . $_SESSION['user_id'];
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        $conn->close();
        return false;
    }
    
    $order = $result->fetch_assoc();
    
    // Get order items
    $sql = "SELECT oi.*, p.name, p.image_url 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $order['items'] = [];
    
    while ($row = $result->fetch_assoc()) {
        $order['items'][] = $row;
    }
    
    $conn->close();
    return $order;
}

// Update order status
function updateOrderStatus($order_id, $status) {
    if (!isAdmin()) {
        return false;
    }
    
    $conn = connectDB();
    
    $sql = "UPDATE orders SET order_status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $order_id);
    $success = $stmt->execute();
    
    $conn->close();
    return $success;
}

// Get a human-readable order status
function getOrderStatusText($status) {
    $statuses = [
        'confirmed' => 'Order Confirmed',
        'preparing' => 'Preparing',
        'out_for_delivery' => 'Out for Delivery',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled'
    ];
    
    return isset($statuses[$status]) ? $statuses[$status] : 'Unknown';
}

// Get user orders
function getUserOrders($user_id = null) {
    if (!isLoggedIn() && !isAdmin()) {
        return [];
    }
    
    if ($user_id === null && isLoggedIn()) {
        $user_id = $_SESSION['user_id'];
    }
    
    $conn = connectDB();
    
    $sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    
    $conn->close();
    return $orders;
}
?> 