<?php
header('Content-Type: application/json');
require __DIR__ . '/../config/database.php';

require_once __DIR__ . '/../models/OrderModel.php';

$orderModel = new OrderModel($pdo);

// Get order_id from query parameters
if (!isset($_GET['order_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'order_id is required']);
    exit;
}

$order_id = intval($_GET['order_id']);

try {
    // Fetch order
    $order = $orderModel->getById($order_id);

    if (!$order) {
        http_response_code(404);
        echo json_encode(['error' => 'Order not found']);
        exit;
    }

    // Fetch items
    $order['items'] = $orderModel->getItems($order_id);

    echo json_encode(['success' => true, 'order' => $order]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
