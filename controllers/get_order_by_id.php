<?php
header('Content-Type: application/json');
require __DIR__ . '/../config/database.php';

// Get order_id from query parameters
if (!isset($_GET['order_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'order_id is required']);
    exit;
}

$order_id = intval($_GET['order_id']);

try {
    // Fetch order
    $stmt = $pdo->prepare("SELECT id AS order_id, user_id, total_amount, status, unique_key FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        http_response_code(404);
        echo json_encode(['error' => 'Order not found']);
        exit;
    }

    // Fetch items
    $stmt = $pdo->prepare("SELECT product_id, quantity, price FROM order_items WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'order' => $order]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
