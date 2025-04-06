<?php
require_once '../includes/functions.php';

// Require admin login
requireAdmin();

// Check if product ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Product ID is required";
    header("Location: products.php");
    exit;
}

$product_id = intval($_GET['id']);
$conn = connectDB();

// Get current product status
$sql = "SELECT is_available FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Product not found";
    header("Location: products.php");
    exit;
}

$product = $result->fetch_assoc();

// Toggle status
$new_status = $product['is_available'] ? 0 : 1;
$status_text = $new_status ? "available" : "unavailable";

// Update product status
$sql = "UPDATE products SET is_available = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $new_status, $product_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    $_SESSION['success'] = "Product marked as " . $status_text . " successfully";
} else {
    $_SESSION['error'] = "Failed to update product status";
}

$conn->close();

// Redirect back to the product page
$redirect = isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'view_product.php') !== false 
            ? "view_product.php?id=" . $product_id 
            : "products.php";
                
header("Location: " . $redirect);
exit;
?> 