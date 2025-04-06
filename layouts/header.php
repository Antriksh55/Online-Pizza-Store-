<?php
    require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - Pizza Store' : 'Pizza Store'; ?></title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .pizza-gradient {
            background: linear-gradient(90deg, #ff4b2b 0%, #ff416c 100%);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Navbar -->
    <nav class="bg-white shadow-md">
        <div class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <!-- Logo -->
                <a href="index.php" class="text-xl font-bold text-red-600">
                    <i class="fas fa-pizza-slice mr-2"></i>Pizza Store
                </a>
                
                <!-- Navigation Links -->
                <div class="hidden md:flex space-x-8">
                    <a href="index.php" class="text-gray-700 hover:text-red-600 transition">Home</a>
                    <a href="menu.php" class="text-gray-700 hover:text-red-600 transition">Menu</a>
                    <?php if (isLoggedIn()): ?>
                        <a href="orders.php" class="text-gray-700 hover:text-red-600 transition">My Orders</a>
                    <?php endif; ?>
                </div>
                
                <!-- Right Side Navigation -->
                <div class="flex items-center space-x-4">
                    <!-- Wishlist -->
                    <a href="wishlist.php" class="text-gray-700 hover:text-red-600 transition relative">
                        <i class="far fa-heart text-xl"></i>
                        <?php if (isLoggedIn() && getWishlistCount() > 0): ?>
                            <span class="absolute -top-2 -right-2 bg-red-600 text-white text-xs w-5 h-5 flex items-center justify-center rounded-full">
                                <?php echo getWishlistCount(); ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    
                    <!-- Cart -->
                    <a href="cart.php" class="text-gray-700 hover:text-red-600 transition relative">
                        <i class="fas fa-shopping-cart text-xl"></i>
                        <?php if (isLoggedIn() && getCartCount() > 0): ?>
                            <span class="absolute -top-2 -right-2 bg-red-600 text-white text-xs w-5 h-5 flex items-center justify-center rounded-full">
                                <?php echo getCartCount(); ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    
                    <!-- Account -->
                    <?php if (isLoggedIn()): ?>
                        <div class="relative group">
                            <button class="text-gray-700 hover:text-red-600 transition focus:outline-none">
                                <i class="fas fa-user-circle text-xl"></i>
                            </button>
                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 hidden group-hover:block">
                                <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-red-600 hover:text-white">
                                    Profile
                                </a>
                                <a href="orders.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-red-600 hover:text-white">
                                    My Orders
                                </a>
                                <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-red-600 hover:text-white">
                                    Logout
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="text-gray-700 hover:text-red-600 transition">
                            <i class="fas fa-sign-in-alt mr-1"></i> Login
                        </a>
                    <?php endif; ?>
                    
                    <!-- Mobile Menu Button -->
                    <button class="md:hidden focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
            
            <!-- Mobile Menu (Hidden by default) -->
            <div class="md:hidden hidden mt-3 pt-3 border-t">
                <a href="index.php" class="block py-2 text-gray-700 hover:text-red-600">Home</a>
                <a href="menu.php" class="block py-2 text-gray-700 hover:text-red-600">Menu</a>
                <?php if (isLoggedIn()): ?>
                    <a href="orders.php" class="block py-2 text-gray-700 hover:text-red-600">My Orders</a>
                    <a href="profile.php" class="block py-2 text-gray-700 hover:text-red-600">Profile</a>
                    <a href="logout.php" class="block py-2 text-gray-700 hover:text-red-600">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="block py-2 text-gray-700 hover:text-red-600">Login</a>
                    <a href="register.php" class="block py-2 text-gray-700 hover:text-red-600">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8"> 