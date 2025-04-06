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

// Validate wishlist_id
if (!isset($_POST['wishlist_id']) || !is_numeric($_POST['wishlist_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid wishlist ID'
    ]);
    exit;
}

$wishlist_id = (int) $_POST['wishlist_id'];
$user_id = $_SESSION['user_id'];

// Remove item from wishlist
$success = removeFromWishlist($wishlist_id);

if (!$success) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to remove item from wishlist'
    ]);
    exit;
}

// Check if wishlist is now empty
$conn = connectDB();
$sql = "SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$wishlist_empty = $row['count'] == 0;
$conn->close();

echo json_encode([
    'success' => true,
    'wishlist_empty' => $wishlist_empty
]); 