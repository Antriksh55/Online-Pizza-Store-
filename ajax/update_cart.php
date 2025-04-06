<?php
session_start();
require_once '../includes/functions.php';

// Set the content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to manage your cart']);
    exit;
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

// Validate cart ID and quantity
if (!isset($_POST['cart_id']) || !is_numeric($_POST['cart_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid cart item ID']);
    exit;
}

if (!isset($_POST['quantity']) || !is_numeric($_POST['quantity'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid quantity']);
    exit;
}

$cart_id = (int)$_POST['cart_id'];
$quantity = (int)$_POST['quantity'];

// Update cart
$result = updateCart($cart_id, $quantity);

// Get updated cart total
if ($result['status'] === 'success') {
    $cartItems = getCartItems();
    $cartTotal = 0;
    
    foreach ($cartItems as $item) {
        $cartTotal += $item['price'] * $item['quantity'];
    }
    
    $result['cart_total'] = formatPrice($cartTotal);
    $result['cart_count'] = count($cartItems);
}

// Return JSON response
echo json_encode($result); 