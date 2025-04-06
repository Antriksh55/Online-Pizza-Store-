<?php
session_start();
require_once '../includes/functions.php';

// Set the content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to manage your wishlist']);
    exit;
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

// Validate product ID
if (!isset($_POST['product_id']) || !is_numeric($_POST['product_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid product ID']);
    exit;
}

$product_id = (int)$_POST['product_id'];

// Toggle wishlist
$result = toggleWishlist($product_id);

// Return JSON response
echo json_encode($result); 