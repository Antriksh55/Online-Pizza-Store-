<?php
    // Database configuration
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'pizza_ordering_system');

    // Create connection
    function connectDB() {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
        
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        
        // Create database if not exists
        $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
        if ($conn->query($sql) === FALSE) {
            die("Error creating database: " . $conn->error);
        }
        
        // Select the database
        $conn->select_db(DB_NAME);
        
        return $conn;
    }
?> 