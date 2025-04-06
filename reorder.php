<?php
require_once 'includes/functions.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Check if order ID is provided
if (!isset($_POST['order_id']) || !is_numeric($_POST['order_id'])) {
    $_SESSION['error'] = "Invalid order ID";
    header("Location: orders.php");
    exit();
}

$order_id = (int) $_POST['order_id'];

// Get order details
$order = getOrder($order_id);

if (!$order) {
    $_SESSION['error'] = "Order not found";
    header("Location: orders.php");
    exit();
}

// Connect to database
$conn = connectDB();
$user_id = $_SESSION['user_id'];

// Clear current cart
$sql = "DELETE FROM cart WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

// Add items from past order to cart
$sql = "SELECT oi.product_id, oi.quantity FROM order_items oi 
        JOIN orders o ON oi.order_id = o.id 
        WHERE o.id = ? AND o.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$item_count = 0;

while ($row = $result->fetch_assoc()) {
    // Check if product still exists
    $sql = "SELECT id FROM products WHERE id = ?";
    $check_stmt = $conn->prepare($sql);
    $check_stmt->bind_param("i", $row['product_id']);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // Add to cart
        $sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
        $cart_stmt = $conn->prepare($sql);
        $cart_stmt->bind_param("iii", $user_id, $row['product_id'], $row['quantity']);
        $cart_stmt->execute();
        $item_count++;
    }
}

$conn->close();

if ($item_count > 0) {
    $_SESSION['success'] = "Items from previous order have been added to your cart";
    header("Location: cart.php");
} else {
    $_SESSION['error'] = "Could not add items to cart. Some products may no longer be available.";
    header("Location: orders.php");
}
exit();
?> 