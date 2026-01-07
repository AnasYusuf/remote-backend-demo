<?php 
// Get request info
$request = rtrim($_SERVER['REQUEST_URI'], '/'); // remove trailing slash
$method = $_SERVER['REQUEST_METHOD'];

// Simple routing
switch (true) {
    case $request === '/orders' && $method === 'POST':
        require 'controllers/create_order.php';
        break;

    case $request === '/test' && $method === 'GET':
        echo json_encode(['status' => 'working']);
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
        break;
}
?>