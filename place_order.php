<?php
require_once 'includes/functions.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Check if payment method is provided
if (!isset($_POST['payment_method'])) {
    $_SESSION['error'] = "Payment method is required";
    header("Location: checkout.php");
    exit();
}

$payment_method = $_POST['payment_method'];
$payment_id = isset($_POST['payment_id']) ? $_POST['payment_id'] : null;

// Validate cart is not empty
$conn = connectDB();
$user_id = $_SESSION['user_id'];

$sql = "SELECT COUNT(*) as count FROM cart WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    $_SESSION['error'] = "Your cart is empty";
    header("Location: cart.php");
    exit();
}

// Create the order
$order_id = createOrder($payment_method, $payment_id);

if (!$order_id) {
    $_SESSION['error'] = "Failed to create order. Please try again.";
    header("Location: checkout.php");
    exit();
}

// Redirect to order confirmation page
header("Location: order_confirmation.php?id=$order_id");
exit();
?> 