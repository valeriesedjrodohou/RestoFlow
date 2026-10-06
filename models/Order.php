<?php
class Order {
    private $conn;
    private $table = "orders";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($user_id, $customer_name, $customer_phone, $delivery_address, $notes, $subtotal, $total_amount, $delivery_fee, $payment_method) {
        $query = "INSERT INTO " . $this->table . " 
                  (user_id, customer_name, customer_phone, delivery_address, notes, subtotal, total_amount, delivery_fee, payment_method, payment_status) 
                  VALUES (:user_id, :customer_name, :customer_phone, :delivery_address, :notes, :subtotal, :total_amount, :delivery_fee, :payment_method, 'unpaid')";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':customer_name', $customer_name);
        $stmt->bindParam(':customer_phone', $customer_phone);
        $stmt->bindParam(':delivery_address', $delivery_address);
        $stmt->bindParam(':notes', $notes);
        $stmt->bindParam(':subtotal', $subtotal);
        $stmt->bindParam(':total_amount', $total_amount);
        $stmt->bindParam(':delivery_fee', $delivery_fee);
        $stmt->bindParam(':payment_method', $payment_method);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function addItems($order_id, $items) {
        $query = "INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price) 
                  VALUES (:order_id, :product_id, :product_name, :quantity, :unit_price)";
        $stmt = $this->conn->prepare($query);
        foreach ($items as $item) {
            $stmt->bindParam(':order_id', $order_id);
            $stmt->bindParam(':product_id', $item['id']);
            $stmt->bindParam(':product_name', $item['name']);
            $stmt->bindParam(':quantity', $item['quantity']);
            $stmt->bindParam(':unit_price', $item['price']);
            $stmt->execute();
        }
        return true;
    }

    public function findByUser($user_id) {
        $query = "SELECT * FROM " . $this->table . " WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id) {
        $query = "SELECT o.*, u.name AS user_name, u.email 
                  FROM " . $this->table . " o 
                  JOIN users u ON o.user_id = u.id 
                  WHERE o.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function items($order_id) {
        $query = "SELECT * FROM order_items WHERE order_id = :order_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $order_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function all() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table . " SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':status', $status);
        return $stmt->execute();
    }
}