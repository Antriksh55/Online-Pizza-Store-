<?php
    require_once '../config/database.php';

    // Create connection
    $conn = connectDB();

    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        address TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    if ($conn->query($sql) === FALSE) {
        echo "Error creating users table: " . $conn->error;
    }

    // Create products table
    $sql = "CREATE TABLE IF NOT EXISTS products (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        image_url VARCHAR(255) NOT NULL,
        category VARCHAR(50) NOT NULL,
        is_featured TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    if ($conn->query($sql) === FALSE) {
        echo "Error creating products table: " . $conn->error;
    }

    // Create cart table
    $sql = "CREATE TABLE IF NOT EXISTS cart (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        product_id INT(11) NOT NULL,
        quantity INT(11) NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )";

    if ($conn->query($sql) === FALSE) {
        echo "Error creating cart table: " . $conn->error;
    }

    // Create wishlist table
    $sql = "CREATE TABLE IF NOT EXISTS wishlist (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        product_id INT(11) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        UNIQUE KEY user_product (user_id, product_id)
    )";

    if ($conn->query($sql) === FALSE) {
        echo "Error creating wishlist table: " . $conn->error;
    }

    // Create orders table
    $sql = "CREATE TABLE IF NOT EXISTS orders (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
        order_status ENUM('confirmed', 'preparing', 'out_for_delivery', 'delivered', 'cancelled') DEFAULT 'confirmed',
        payment_method VARCHAR(50) NOT NULL,
        payment_id VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";

    if ($conn->query($sql) === FALSE) {
        echo "Error creating orders table: " . $conn->error;
    }

    // Create order_items table
    $sql = "CREATE TABLE IF NOT EXISTS order_items (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        order_id INT(11) NOT NULL,
        product_id INT(11) NOT NULL,
        quantity INT(11) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )";

    if ($conn->query($sql) === FALSE) {
        echo "Error creating order_items table: " . $conn->error;
    }

    // Create admins table
    $sql = "CREATE TABLE IF NOT EXISTS admins (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    if ($conn->query($sql) === FALSE) {
        echo "Error creating admins table: " . $conn->error;
    }

    // Insert default admin account
    $default_admin_username = 'admin';
    $default_admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $default_admin_name = 'Administrator';

    $sql = "INSERT IGNORE INTO admins (username, password, name) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $default_admin_username, $default_admin_password, $default_admin_name);
    $stmt->execute();

    echo "Database schema created successfully!";

    $conn->close();
?> 