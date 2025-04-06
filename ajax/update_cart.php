<?php
header('Content-Type: application/json');
require_once '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in'
    ]);
    exit;
}

// Validate cart_id and quantity
if (!isset($_POST['cart_id']) || !is_numeric($_POST['cart_id']) || 
    !isset($_POST['quantity']) || !is_numeric($_POST['quantity']) || $_POST['quantity'] <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid parameters'
    ]);
    exit;
}

$cart_id = (int) $_POST['cart_id'];
$quantity = (int) $_POST['quantity'];
$user_id = $_SESSION['user_id'];

// Update cart
$success = updateCartQuantity($cart_id, $quantity);

if (!$success) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update cart'
    ]);
    exit;
}

// Get updated cart details
$conn = connectDB();

// Get item details
$sql = "SELECT c.*, p.price 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.id = ? AND c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $cart_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();

// Calculate subtotal
$item_subtotal = $item['price'] * $item['quantity'];

// Get total cart amount
$sql = "SELECT c.*, p.price 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_subtotal = 0;
while ($row = $result->fetch_assoc()) {
    $cart_subtotal += $row['price'] * $row['quantity'];
}

$delivery_fee = 50;
$cart_total = $cart_subtotal + $delivery_fee;

$conn->close();

echo json_encode([
    'success' => true,
    'item_subtotal' => formatPrice($item_subtotal),
    'cart_subtotal' => formatPrice($cart_subtotal),
    'cart_total' => formatPrice($cart_total)
]); 