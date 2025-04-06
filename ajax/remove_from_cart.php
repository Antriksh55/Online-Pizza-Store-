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

// Validate cart ID
if (!isset($_POST['cart_id']) || !is_numeric($_POST['cart_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid cart item ID']);
    exit;
}

$cart_id = (int)$_POST['cart_id'];
$user_id = $_SESSION['user_id'];

$conn = connectDB();

// Verify cart item belongs to user
$sql = "SELECT id FROM cart WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $cart_id, $user_id);
$stmt->execute();

if ($stmt->get_result()->num_rows === 0) {
    $conn->close();
    echo json_encode(['status' => 'error', 'message' => 'Cart item not found']);
    exit;
}

// Delete cart item
$sql = "DELETE FROM cart WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $cart_id, $user_id);

if (!$stmt->execute()) {
    $conn->close();
    echo json_encode(['status' => 'error', 'message' => 'Failed to remove item from cart']);
    exit;
}

// Check if cart is now empty
$sql = "SELECT COUNT(*) as count FROM cart WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_empty = ($stmt->get_result()->fetch_assoc()['count'] == 0);

$conn->close();

echo json_encode([
    'status' => 'success',
    'message' => 'Item removed from cart',
    'cart_empty' => $cart_empty
]); 