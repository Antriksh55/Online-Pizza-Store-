<?php
header('Content-Type: application/json');
require_once '../includes/functions.php';

// Require admin login
if (!isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid customer ID']);
    exit();
}

$customerId = (int)$_GET['id'];
$conn = connectDB();

// Get customer details
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customerId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Customer not found']);
    $conn->close();
    exit();
}

$customer = $result->fetch_assoc();

// Get order count
$sql = "SELECT COUNT(*) as order_count FROM orders WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customerId);
$stmt->execute();
$result = $stmt->get_result();
$order_count = $result->fetch_assoc()['order_count'];

// Get total spent
$sql = "SELECT SUM(total_amount) as total_spent FROM orders WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customerId);
$stmt->execute();
$result = $stmt->get_result();
$total_spent = $result->fetch_assoc()['total_spent'];

// Format created_at
$customer['created_at'] = date('F j, Y', strtotime($customer['created_at']));

// Format total_spent
$customer['total_spent'] = formatPrice($total_spent ? $total_spent : 0);

// Add order count
$customer['order_count'] = $order_count;

// Remove password before sending to client
unset($customer['password']);

$conn->close();

echo json_encode([
    'success' => true,
    'customer' => $customer
]); 