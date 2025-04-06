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

// Validate cart_id
if (!isset($_POST['cart_id']) || !is_numeric($_POST['cart_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid cart ID'
    ]);
    exit;
}

$cart_id = (int) $_POST['cart_id'];
$user_id = $_SESSION['user_id'];

// Remove item from cart
$success = removeFromCart($cart_id);

if (!$success) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to remove item from cart'
    ]);
    exit;
}

// Get updated cart details
$conn = connectDB();

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
$cart_empty = true;

if ($result->num_rows > 0) {
    $cart_empty = false;
    while ($row = $result->fetch_assoc()) {
        $cart_subtotal += $row['price'] * $row['quantity'];
    }
}

$delivery_fee = 50;
$cart_total = $cart_subtotal + ($cart_empty ? 0 : $delivery_fee);

$conn->close();

echo json_encode([
    'success' => true,
    'cart_subtotal' => formatPrice($cart_subtotal),
    'cart_total' => formatPrice($cart_total),
    'cart_empty' => $cart_empty
]); 