<?php
    require_once '../includes/functions.php';
    requireAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - Admin Dashboard' : 'Admin Dashboard'; ?></title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex">
    <!-- Sidebar -->
    <aside class="bg-gray-800 text-white w-64 min-h-screen p-4 hidden md:block">
        <div class="text-xl font-bold mb-8 text-center">
            <i class="fas fa-pizza-slice mr-2"></i>Admin Panel
        </div>
        
        <nav>
            <ul class="space-y-2">
                <li>
                    <a href="index.php" class="flex items-center px-4 py-3 hover:bg-gray-700 rounded transition">
                        <i class="fas fa-tachometer-alt w-6"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="orders.php" class="flex items-center px-4 py-3 hover:bg-gray-700 rounded transition">
                        <i class="fas fa-clipboard-list w-6"></i>
                        <span>Orders</span>
                    </a>
                </li>
                <li>
                    <a href="products.php" class="flex items-center px-4 py-3 hover:bg-gray-700 rounded transition">
                        <i class="fas fa-pizza-slice w-6"></i>
                        <span>Products</span>
                    </a>
                </li>
                <li>
                    <a href="users.php" class="flex items-center px-4 py-3 hover:bg-gray-700 rounded transition">
                        <i class="fas fa-users w-6"></i>
                        <span>Users</span>
                    </a>
                </li>
                <li>
                    <a href="../index.php" target="_blank" class="flex items-center px-4 py-3 hover:bg-gray-700 rounded transition">
                        <i class="fas fa-external-link-alt w-6"></i>
                        <span>View Site</span>
                    </a>
                </li>
                <li class="mt-8">
                    <a href="logout.php" class="flex items-center px-4 py-3 hover:bg-red-600 bg-red-500 rounded transition">
                        <i class="fas fa-sign-out-alt w-6"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </nav>
    </aside>
    
    <!-- Main Content -->
    <div class="flex-1">
        <!-- Top Navbar -->
        <header class="bg-white shadow-md p-4 flex justify-between items-center">
            <button class="md:hidden text-gray-800 focus:outline-none" id="mobile-menu-button">
                <i class="fas fa-bars text-xl"></i>
            </button>
            
            <div>
                <span class="md:hidden text-lg font-bold text-gray-800">Admin Panel</span>
            </div>
            
            <div class="flex items-center space-x-4">
                <div class="relative group">
                    <button class="flex items-center text-gray-700 focus:outline-none">
                        <span class="mr-2"><?php echo $_SESSION['admin_name']; ?></span>
                        <i class="fas fa-user-circle text-xl"></i>
                    </button>
                    <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 hidden group-hover:block">
                        <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-800 hover:text-white">
                            Profile
                        </a>
                        <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-800 hover:text-white">
                            Logout
                        </a>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Mobile Sidebar (Hidden by default) -->
        <div class="md:hidden fixed inset-0 bg-gray-800 bg-opacity-75 z-40 hidden" id="mobile-menu">
            <div class="bg-gray-800 text-white w-64 min-h-screen p-4">
                <div class="flex justify-between items-center mb-8">
                    <div class="text-xl font-bold">
                        <i class="fas fa-pizza-slice mr-2"></i>Admin Panel
                    </div>
                    <button class="text-white focus:outline-none" id="close-mobile-menu">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <nav>
                    <ul class="space-y-2">
                        <li>
                            <a href="index.php" class="flex items-center px-4 py-3 hover:bg-gray-700 rounded transition">
                                <i class="fas fa-tachometer-alt w-6"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="orders.php" class="flex items-center px-4 py-3 hover:bg-gray-700 rounded transition">
                                <i class="fas fa-clipboard-list w-6"></i>
                                <span>Orders</span>
                            </a>
                        </li>
                        <li>
                            <a href="products.php" class="flex items-center px-4 py-3 hover:bg-gray-700 rounded transition">
                                <i class="fas fa-pizza-slice w-6"></i>
                                <span>Products</span>
                            </a>
                        </li>
                        <li>
                            <a href="users.php" class="flex items-center px-4 py-3 hover:bg-gray-700 rounded transition">
                                <i class="fas fa-users w-6"></i>
                                <span>Users</span>
                            </a>
                        </li>
                        <li>
                            <a href="../index.php" target="_blank" class="flex items-center px-4 py-3 hover:bg-gray-700 rounded transition">
                                <i class="fas fa-external-link-alt w-6"></i>
                                <span>View Site</span>
                            </a>
                        </li>
                        <li class="mt-8">
                            <a href="logout.php" class="flex items-center px-4 py-3 hover:bg-red-600 bg-red-500 rounded transition">
                                <i class="fas fa-sign-out-alt w-6"></i>
                                <span>Logout</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
        
        <!-- Page Content -->
        <main class="p-6"> 