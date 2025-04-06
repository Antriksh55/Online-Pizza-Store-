<?php
    require_once '../config/database.php';

    // Create connection
    $conn = connectDB();

    // Sample pizza products
    $products = [
        [
            'name' => 'Margherita Pizza',
            'description' => 'Classic delight with 100% real mozzarella cheese and fresh tomato sauce',
            'price' => 199.00,
            'image_url' => 'margherita.jpg',
            'category' => 'pizza',
            'is_featured' => 1
        ],
        [
            'name' => 'Pepperoni Pizza',
            'description' => 'American classic with spicy pepperoni, mozzarella and tomato sauce',
            'price' => 249.00,
            'image_url' => 'pepperoni.jpg',
            'category' => 'pizza',
            'is_featured' => 1
        ],
        [
            'name' => 'Supreme Pizza',
            'description' => 'Loaded with pepperoni, sausage, bell peppers, onions, and black olives',
            'price' => 299.00,
            'image_url' => 'supreme.jpg',
            'category' => 'pizza',
            'is_featured' => 1
        ],
        [
            'name' => 'Vegetarian Pizza',
            'description' => 'Fresh vegetables including bell peppers, mushrooms, onions, and olives',
            'price' => 229.00,
            'image_url' => 'vegetarian.jpg',
            'category' => 'pizza',
            'is_featured' => 0
        ],
        [
            'name' => 'BBQ Chicken Pizza',
            'description' => 'Grilled chicken, BBQ sauce, red onions, and fresh cilantro',
            'price' => 279.00,
            'image_url' => 'bbq_chicken.jpg',
            'category' => 'pizza',
            'is_featured' => 0
        ],
        [
            'name' => 'Hawaiian Pizza',
            'description' => 'Ham, pineapple, and mozzarella cheese on a tomato base',
            'price' => 259.00,
            'image_url' => 'hawaiian.jpg',
            'category' => 'pizza',
            'is_featured' => 0
        ],
        [
            'name' => 'Garlic Breadsticks',
            'description' => 'Freshly baked breadsticks with garlic butter and herbs',
            'price' => 99.00,
            'image_url' => 'garlic_breadsticks.jpg',
            'category' => 'sides',
            'is_featured' => 0
        ],
        [
            'name' => 'Coca-Cola',
            'description' => 'Refreshing 500ml Coca-Cola',
            'price' => 59.00,
            'image_url' => 'coke.jpg',
            'category' => 'beverages',
            'is_featured' => 0
        ]
    ];

    // Insert products
    $stmt = $conn->prepare("INSERT INTO products (name, description, price, image_url, category, is_featured) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdssi", $name, $description, $price, $image_url, $category, $is_featured);

    foreach ($products as $product) {
        $name = $product['name'];
        $description = $product['description'];
        $price = $product['price'];
        $image_url = $product['image_url'];
        $category = $product['category'];
        $is_featured = $product['is_featured'];
        $stmt->execute();
    }

    echo "Sample products added successfully!";

    $conn->close();
?> 