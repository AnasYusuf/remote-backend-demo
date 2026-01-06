<?php
header('Content-Type: application/json');
require __DIR__ . '/../config/database.php';

if (!isset($_GET['user_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'user_id is required']);
    exit;
}

$user_id = intval($_GET['user_id']);
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$status = isset($_GET['status']) ? $_GET['status'] : null;

try {
    $query = "SELECT id AS order_id, total_amount, status, unique_key 
              FROM orders 
              WHERE user_id = ?";
    $params = [$user_id];

    if ($status) {
        $query .= " AND status = ?";
        $params[] = $status;
    }

    $query .= " ORDER BY id DESC LIMIT ?";
    $params[] = $limit;

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($orders as &$order) {
        $stmt = $pdo->prepare("SELECT product_id, quantity, price FROM order_items WHERE order_id = ?");
        $stmt->execute([$order['order_id']]);
        $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode(['success' => true, 'orders' => $orders]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
