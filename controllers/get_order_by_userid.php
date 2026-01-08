<?php
header('Content-Type: application/json');
require __DIR__ . '/../config/database.php';

require_once __DIR__ . '/../models/OrderModel.php';

$orderModel = new OrderModel($pdo);

if (!isset($_GET['user_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'user_id is required']);
    exit;
}

$user_id = intval($_GET['user_id']);
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$status = isset($_GET['status']) ? $_GET['status'] : null;

try {
    $orders =  $orderModel->getByUserId($user_id, $status, $limit);

    foreach ($orders as &$order) {
        $order['items'] = $orderModel->getItems($order['order_id']);
    }

    echo json_encode(['success' => true, 'orders' => $orders]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
