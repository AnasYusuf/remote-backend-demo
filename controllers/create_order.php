<?php
header('Content-Type: application/json');
require __DIR__ . '/../config/database.php';  // ensure correct path to config.php

require_once __DIR__ . '/../models/OrderModel.php';
require_once __DIR__ . '/../models/ProductModel.php';

$orderModel = new OrderModel($pdo);
$productModel = new ProductModel($pdo);

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
    if ($orderModel->existsByUniqueKey($data['unique_key'])) {
        echo json_encode(['error' => 'Order already exists']);
        exit;
    }

    // Start transaction
    $pdo->beginTransaction();

    // 1. Calculate total price
    $total = 0;
    foreach ($data['items'] as $item) {
        $product = $productModel->lockRow($item['product_id']);
        $total += $item['quantity'] * $product['price'];
    }

    // 2. Insert order with unique_key
    $order_id = $orderModel->createOrder($data['user_id'], $total, $data['unique_key']);

    // 3. Insert order items and update stock(after checking current stock)
    foreach ($data['items'] as $item) {
        $product = $productModel->getById($item['product_id']); 
        $price = $product['price'];
        $stock = $product['stock'];

        $orderModel->insertOrderItem($order_id, $item['product_id'], $item['quantity'], $item['quantity'] * $price);

        if ($item['quantity'] <= 0 || $item['quantity'] > $stock) {
            throw new Exception("Invalid quantity for product ID {$item['product_id']}");
            //echo "Invalid quantity for product ID {$item['product_id']}";
        }
        
        $productModel->updateStock($item['product_id'], $item['quantity']);
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
