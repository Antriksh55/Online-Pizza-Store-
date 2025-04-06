<?php
session_start();
require_once '../includes/functions.php';

// Set the content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to view cart']);
    exit;
}

$user_id = $_SESSION['user_id'];
$conn = connectDB();

// Get cart items with subtotals
$sql = "SELECT c.id as cart_id, c.quantity, p.id, p.name, p.price, c.quantity * p.price as subtotal 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$items = $result->fetch_all(MYSQLI_ASSOC);

// Calculate totals
$cartSubtotal = 0;
$deliveryFee = 50; // Fixed delivery fee
$taxRate = 0.05; // 5% tax rate

foreach ($items as &$item) {
    $cartSubtotal += $item['subtotal'];
    $item['subtotal'] = formatPrice($item['subtotal']);
}

$taxAmount = $cartSubtotal * $taxRate;
$orderTotal = $cartSubtotal + $deliveryFee + $taxAmount;

$conn->close();

echo json_encode([
    'status' => 'success',
    'subtotal' => formatPrice($cartSubtotal),
    'delivery_fee' => formatPrice($deliveryFee),
    'tax' => formatPrice($taxAmount),
    'total' => formatPrice($orderTotal),
    'items' => $items
]); 