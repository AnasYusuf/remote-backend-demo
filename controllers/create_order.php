<?php
header('Content-Type: application/json');
require __DIR__ . '/../config/database.php';  // ensure correct path to config.php

// Decode incoming JSON
$data = json_decode(file_get_contents('php://input'), true);

// Basic validation
if (!isset($data['user_id']) || !isset($data['items']) || !is_array($data['items'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

// Idempotency validation
if (!isset($data['unique_key']) || empty($data['unique_key'])) {
    http_response_code(400);
    echo json_encode(['error' => 'unique_key is required']);
    exit;
}

try {
    // Check if order with same unique_key already exists
    $stmt = $pdo->prepare("SELECT id FROM orders WHERE unique_key = ?");
    $stmt->execute([$data['unique_key']]);
    $existingOrder = $stmt->fetch();

    if ($existingOrder) {
        // Return existing order to ensure idempotency
        echo json_encode([
            'success' => true,
            'order_id' => $existingOrder['id'],
            'message' => 'Order already exists'
        ]);
        exit;
    }

    // Start transaction
    $pdo->beginTransaction();

    // 1. Calculate total price and validate stock
    $total = 0;
    foreach ($data['items'] as $item) {
        $stmt = $pdo->prepare("SELECT price, stock FROM products WHERE id = ?");
        $stmt->execute([$item['product_id']]);
        $product = $stmt->fetch();

        if (!$product) {
            throw new Exception("Product ID {$item['product_id']} not found");
        }

        // Quantity validation
        if ($item['quantity'] <= 0 || $item['quantity'] > $product['stock']) {
            throw new Exception("Invalid quantity for product ID {$item['product_id']}");
        }

        $total += $item['quantity'] * $product['price'];
    }

    // 2. Insert order with unique_key
    $stmt = $pdo->prepare(
        "INSERT INTO orders (user_id, total_amount, status, unique_key) VALUES (?, ?, 'pending', ?)"
    );
    $stmt->execute([$data['user_id'], $total, $data['unique_key']]);
    $order_id = $pdo->lastInsertId();

    // 3. Insert order items and update stock
    foreach ($data['items'] as $item) {
        $stmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
        $stmt->execute([$item['product_id']]);
        $product = $stmt->fetch();

        $stmt = $pdo->prepare(
            "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['quantity'] * $product['price']]);

        $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $stmt->execute([$item['quantity'], $item['product_id']]);
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'order_id' => $order_id
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
