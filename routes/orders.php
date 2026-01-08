<?php
$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($uri === '/orders' && $method === 'GET') {

    if (isset($_GET['user_id'])) {
        require 'controllers/get_order_by_userid.php';
        exit;
    }

    if (isset($_GET['order_id'])) {
        require 'controllers/get_order_by_orderid.php';
        exit;
    }

    http_response_code(400);
    echo json_encode(['error' => 'Missing required query parameter']);
    exit;
}

if ($uri === '/orders' && $method === 'POST') {
    require 'controllers/create_order.php';
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Route not found']);
