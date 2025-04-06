<?php
    // Database configuration
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'pizza_store';

    // Database connection function
    function connectDB() {
        global $host, $username, $password, $database;
        
        // Create connection
        $conn = new mysqli($host, $username, $password);
        
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        
        // Check if database exists, if not create it
        $db_check = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$database'");
        if ($db_check->num_rows == 0) {
            $conn->query("CREATE DATABASE IF NOT EXISTS $database");
        }
        
        // Connect to the database
        $conn->select_db($database);
        
        // Create required tables if they don't exist
        createTables($conn);
        
        return $conn;
    }

    // Create required tables if they don't exist
    function createTables($conn) {
        // Users table
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            phone VARCHAR(20),
            address TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $conn->query($sql);
        
        // Admins table
        $sql = "CREATE TABLE IF NOT EXISTS admins (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            name VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $conn->query($sql);
        
        // Products table
        $sql = "CREATE TABLE IF NOT EXISTS products (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(150),
            description TEXT NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            category VARCHAR(50) NOT NULL,
            image_url VARCHAR(255) NOT NULL,
            is_available TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $conn->query($sql);
        
        // Cart table
        $sql = "CREATE TABLE IF NOT EXISTS cart (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            user_id INT(11) NOT NULL,
            product_id INT(11) NOT NULL,
            quantity INT(11) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )";
        $conn->query($sql);
        
        // Wishlist table
        $sql = "CREATE TABLE IF NOT EXISTS wishlist (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            user_id INT(11) NOT NULL,
            product_id INT(11) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )";
        $conn->query($sql);
        
        // Orders table
        $sql = "CREATE TABLE IF NOT EXISTS orders (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            user_id INT(11) NOT NULL,
            total_amount DECIMAL(10,2) NOT NULL,
            payment_method VARCHAR(50) NOT NULL,
            payment_id VARCHAR(100),
            payment_status VARCHAR(50) DEFAULT 'pending',
            status VARCHAR(50) DEFAULT 'pending',
            order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )";
        $conn->query($sql);
        
        // Order items table
        $sql = "CREATE TABLE IF NOT EXISTS order_items (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            order_id INT(11) NOT NULL,
            product_id INT(11) NOT NULL,
            quantity INT(11) NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )";
        $conn->query($sql);
    }
?> 