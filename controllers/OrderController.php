<?php
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/User.php';

class OrderController {
    private $order;
    private $user;
    const DELIVERY_FEE = 1500.00;

    public function __construct($db) {
        $this->order = new Order($db);
        $this->user = new User($db);
    }

    public function cart() {
        $user = isset($_SESSION['user_id']) ? $this->user->findById($_SESSION['user_id']) : null;
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/cart.php';
        require_once __DIR__ . '/../views/footer.php';
    }

    public function addToCart() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['product_id'];
            $name = $_POST['name'];
            $price = $_POST['price'];
            $image = $_POST['image'];
            $qty = max(1, intval($_POST['quantity'] ?? 1));
            if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
            if (isset($_SESSION['cart'][$id])) {
                $_SESSION['cart'][$id]['quantity'] += $qty;
            } else {
                $_SESSION['cart'][$id] = compact('id','name','price','image') + ['quantity' => $qty];
            }
        }
        header('Location: ' . url('cart'));
        exit;
    }

    public function updateCart() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['cart'])) {
            foreach ($_POST['quantity'] as $id => $qty) {
                $qty = intval($qty);
                if ($qty <= 0) {
                    unset($_SESSION['cart'][$id]);
                } else {
                    $_SESSION['cart'][$id]['quantity'] = $qty;
                }
            }
        }
        header('Location: ' . url('cart'));
        exit;
    }

    public function removeFromCart($id) {
        if (isset($_SESSION['cart'][$id])) {
            unset($_SESSION['cart'][$id]);
        }
        header('Location: ' . url('cart'));
        exit;
    }

    public function checkout() {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['cart'])) {
            header('Location: ' . url('cart'));
            exit;
        }
        $u = $this->user->findById($_SESSION['user_id']);
        
        $customer_name = $u['name'];
        $customer_phone = !empty($_POST['delivery_phone']) ? $_POST['delivery_phone'] : $u['phone'];
        $delivery_address = !empty($_POST['delivery_address']) ? $_POST['delivery_address'] : $u['address'];
        $notes = $_POST['notes'] ?? '';
        $payment_method = $_POST['payment_method'] ?? 'cash_on_delivery';

        $subtotal = 0;
        foreach ($_SESSION['cart'] as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        $total = $subtotal + self::DELIVERY_FEE;

        $order_id = $this->order->create(
            $_SESSION['user_id'], $customer_name, $customer_phone, $delivery_address, 
            $notes, $subtotal, $total, self::DELIVERY_FEE, $payment_method
        );

        if ($order_id) {
            $this->order->addItems($order_id, array_values($_SESSION['cart']));
            unset($_SESSION['cart']);
            header('Location: ' . url('order/confirmation/' . $order_id));
            exit;
        }
        die("Erreur lors de la création de la commande.");
    }

    public function myOrders() {
        $this->requireLogin();
        $orders = $this->order->findByUser($_SESSION['user_id']);
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/orders.php';
        require_once __DIR__ . '/../views/footer.php';
    }

    public function confirmation($id) {
        $order = $this->order->find($id);
        if (!$order) { http_response_code(404); echo "Commande introuvable"; return; }
        $items = $this->order->items($id);
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/order-confirmation.php';
        require_once __DIR__ . '/../views/footer.php';
    }

    public function adminDashboard() {
        $this->requireAdmin();
        $orders = $this->order->all();
        $total_revenue = 0; $pending = 0; $delivering = 0;
        foreach ($orders as $o) {
            if ($o['status'] === 'delivered') $total_revenue += $o['total_amount'];
            if (in_array($o['status'], ['pending','preparing'])) $pending++;
            if ($o['status'] === 'delivering') $delivering++;
        }
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/admin/dashboard.php';
        require_once __DIR__ . '/../views/footer.php';
    }

    public function adminOrders() {
        $this->requireAdmin();
        $orders = $this->order->all();
        foreach ($orders as &$o) {
            $o['items'] = $this->order->items($o['id']);
        }
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/admin/orders.php';
        require_once __DIR__ . '/../views/footer.php';
    }

    public function updateOrderStatus($id) {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $status = $_POST['status'];
            $this->order->updateStatus($id, $status);
        }
        header('Location: ' . url('admin/orders'));
        exit;
    }

    private function requireLogin() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . url('login'));
            exit;
        }
    }

    private function requireAdmin() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ' . url('login'));
            exit;
        }
    }
}