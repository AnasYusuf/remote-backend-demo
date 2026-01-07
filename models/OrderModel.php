<?php
class OrderModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Fetch order by ID
    public function getById($orderId) {
        $stmt = $this->pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        return $stmt->fetch();
    }

    // Fetch order items by ID
    public function getItems($orderId) {
        $stmt = $this->pdo->prepare("SELECT product_id, quantity, price FROM order_items WHERE order_id = ?");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
        //$order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    //echo json_encode(['success' => true, 'order' => $order]);

    public function getByUserId($userId, $status, $limit){
        $query = "SELECT id AS order_id, total_amount, status, unique_key 
                  FROM orders 
                  WHERE user_id = ?";
        $params = [$userId];

        if ($status) {
            $query .= " AND status = ?";
            $params[] = $status;
        }

        $query .= " ORDER BY id DESC LIMIT ?";
        $params[] = $limit;

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Insert order and return order ID
    public function createOrder($userId, $total, $uniqueKey) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO orders (user_id, total_amount, status, unique_key) VALUES (?, ?, 'pending', ?)"
        );
        $stmt->execute([$userId, $total, $uniqueKey]);
        return $this->pdo->lastInsertId();
    }

    // Check if order exists by unique key
    public function existsByUniqueKey($uniqueKey) {
        $stmt = $this->pdo->prepare("SELECT * FROM orders WHERE unique_key = ?");
        $stmt->execute([$uniqueKey]);
        return $stmt->fetch();
    }

    // Insert order item
    public function insertOrderItem($orderId, $productId, $quantity, $price) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)"
        );
        return $stmt->execute([$orderId, $productId, $quantity, $price]);
    }
}
?>
