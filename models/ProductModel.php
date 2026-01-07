<?php
class ProductModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Fetch product by ID
    public function getById($productId) {
        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        return $stmt->fetch();
    }

    // Decrease stock safely
    public function updateStock($productId, $quantity) {
        $stmt = $this->pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        return $stmt->execute([$quantity, $productId]);
    }

    // Optional: Lock row for transaction
    public function lockRow($productId) {
        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE id = ? FOR UPDATE");
        $stmt->execute([$productId]);
        return $stmt->fetch();
    }
}
?>
