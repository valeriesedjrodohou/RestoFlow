<?php
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $user;

    public function __construct($db) {
        $this->user = new User($db);
    }

    public function login() {
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . url(''));
            exit;
        }
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $u = $this->user->login($email, $password);
            if ($u) {
                $_SESSION['user_id'] = $u['id'];
                $_SESSION['user_name'] = $u['name'];
                $_SESSION['user_role'] = $u['role'];
                if ($u['role'] === 'admin') {
                    header('Location: ' . url('admin/dashboard'));
                } else {
                    header('Location: ' . url(''));
                }
                exit;
            } else {
                $error = "Email ou mot de passe incorrect.";
            }
        }
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/login.php';
        require_once __DIR__ . '/../views/footer.php';
    }

    public function register() {
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . url(''));
            exit;
        }
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $phone = trim($_POST['phone']);
            $address = trim($_POST['address']);
            try {
                if ($this->user->register($name, $email, $password, $phone, $address)) {
                    header('Location: ' . url('login'));
                    exit;
                }
            } catch (PDOException $e) {
                $error = "Email déjà utilisé.";
            }
        }
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/register.php';
        require_once __DIR__ . '/../views/footer.php';
    }

    public function logout() {
        session_destroy();
        header('Location: ' . url(''));
        exit;
    }
}