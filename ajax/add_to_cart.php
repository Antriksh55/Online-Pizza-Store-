<?php
session_start();
require_once '../includes/functions.php';

// Set the content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to add items to cart']);
    exit;
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

// Validate product ID and quantity
if (!isset($_POST['product_id']) || !is_numeric($_POST['product_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid product ID']);
    exit;
}

if (!isset($_POST['quantity']) || !is_numeric($_POST['quantity']) || $_POST['quantity'] < 1 || $_POST['quantity'] > 10) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid quantity (must be between 1 and 10)']);
    exit;
}

$product_id = (int)$_POST['product_id'];
$quantity = (int)$_POST['quantity'];
$user_id = $_SESSION['user_id'];

$conn = connectDB();

// Check if product exists and is active
$sql = "SELECT id, price FROM products WHERE id = ? AND active = 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $conn->close();
    echo json_encode(['status' => 'error', 'message' => 'Product not available']);
    exit;
}

$product = $result->fetch_assoc();

// Check if product is already in cart
$sql = "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update quantity
    $cart_item = $result->fetch_assoc();
    $new_quantity = min(10, $cart_item['quantity'] + $quantity); // Cap at 10
    
    $sql = "UPDATE cart SET quantity = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $new_quantity, $cart_item['id']);
    
    if (!$stmt->execute()) {
        $conn->close();
        echo json_encode(['status' => 'error', 'message' => 'Failed to update cart']);
        exit;
    }
} else {
    // Add new item to cart
    $sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $user_id, $product_id, $quantity);
    
    if (!$stmt->execute()) {
        $conn->close();
        echo json_encode(['status' => 'error', 'message' => 'Failed to add item to cart']);
        exit;
    }
}

// Get updated cart count
$sql = "SELECT COUNT(*) as count FROM cart WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_count = $stmt->get_result()->fetch_assoc()['count'];

$conn->close();

echo json_encode([
    'status' => 'success',
    'message' => 'Product added to cart',
    'cart_count' => $cart_count
]); 