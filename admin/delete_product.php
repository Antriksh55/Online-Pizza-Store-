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

try {
    // Begin transaction
    $conn->begin_transaction();
    
    // Get product details for image deletion
    $sql = "SELECT image_url FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Product not found");
    }
    
    $product = $result->fetch_assoc();
    
    // Delete product
    $sql = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    
    if ($stmt->execute()) {
        // Delete product image if it exists
        if (!empty($product['image_url'])) {
            $image_path = getUploadPath('../uploads/products/' . $product['image_url']);
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
        
        // Commit transaction
        $conn->commit();
        $_SESSION['success'] = "Product deleted successfully";
    } else {
        throw new Exception("Failed to delete product");
    }
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    $_SESSION['error'] = $e->getMessage();
}

$conn->close();

// Redirect back to products page
header("Location: products.php");
exit;
?> 