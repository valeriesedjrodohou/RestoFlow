<?php
require_once __DIR__ . '/../models/Product.php';

class MenuController {
    private $product;

    public function __construct($db) {
        $this->product = new Product($db);
    }

    public function index() {
        $products = $this->product->available();
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/menu.php';
        require_once __DIR__ . '/../views/footer.php';
    }

    public function adminProducts() {
        $products = $this->product->all();
        $categories = $this->product->categories();
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/admin/products.php';
        require_once __DIR__ . '/../views/footer.php';
    }

    public function createProduct() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $image = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $uploadDir = __DIR__ . '/../public/uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                $image = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', basename($_FILES['image']['name']));
                move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $image);
            }
            $available = isset($_POST['available']) ? 1 : 0;
            $this->product->create(
                $_POST['name'],
                $_POST['description'],
                $_POST['price'],
                $image,
                $_POST['category_id'] ?: null,
                $available
            );
        }
        header('Location: ' . url('admin/products'));
        exit;
    }

    public function deleteProduct($id) {
        $this->product->delete($id);
        header('Location: ' . url('admin/products'));
        exit;
    }
}