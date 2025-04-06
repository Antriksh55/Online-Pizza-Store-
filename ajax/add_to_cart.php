<?php
header('Content-Type: application/json');
require_once '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in to add items to cart'
    ]);
    exit;
}

// Validate product_id and quantity
if (!isset($_POST['product_id']) || !is_numeric($_POST['product_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid product ID'
    ]);
    exit;
}

$product_id = (int) $_POST['product_id'];
$quantity = isset($_POST['quantity']) && is_numeric($_POST['quantity']) ? (int) $_POST['quantity'] : 1;

// Validate product exists
$conn = connectDB();
$sql = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Product not found'
    ]);
    $conn->close();
    exit;
}

// Add to cart
$success = addToCart($product_id, $quantity);

if ($success) {
    echo json_encode([
        'success' => true,
        'message' => 'Product added to cart successfully',
        'cart_count' => getCartCount()
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to add product to cart'
    ]);
}

$conn->close(); 