<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/MenuController.php';
require_once __DIR__ . '/../controllers/OrderController.php';

function base_url() {
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
    $dir = dirname($script);
    if (substr($dir, -7) === '/public') $dir = substr($dir, 0, -7);
    return rtrim($dir, '/');
}
function url($p = '') { return base_url() . '/' . ltrim($p, '/'); }
function asset($p = '') { return base_url() . '/public/' . ltrim($p, '/'); }
function e($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

// Formatage en FCFA
function format_price($amount) {
    return number_format($amount, 0, ',', ' ') . ' FCFA';
}

// Nouveaux statuts
function status_label($s) {
    $labels = [
        'pending' => 'En attente',
        'preparing' => 'En préparation',
        'delivering' => 'En livraison',
        'delivered' => 'Livré',
        'cancelled' => 'Annulé'
    ];
    return $labels[$s] ?? $s;
}

function payment_label($s) {
    $labels = [
        'card' => 'Carte bancaire',
        'orange_money' => 'Orange Money',
        'mtn_money' => 'MTN Money',
        'wave' => 'Wave',
        'cash_on_delivery' => 'Paiement à la livraison'
    ];
    return $labels[$s] ?? $s;
}

 $database = new Database();
 $db = $database->getConnection();

 $auth  = new AuthController($db);
 $menu  = new MenuController($db);
 $order = new OrderController($db);

 $url   = isset($_GET['url']) ? trim($_GET['url'], '/') : '';
 $parts = explode('/', $url);
 $c  = $parts[0] ?? '';
 $a  = $parts[1] ?? '';
 $p1 = $parts[2] ?? null;
 $p2 = $parts[3] ?? null;

switch ($c) {
    case '':
        $menu->index(); break;
    case 'login':     $auth->login(); break;
    case 'register':  $auth->register(); break;
    case 'logout':    $auth->logout(); break;

    case 'cart':       $order->cart(); break;
    case 'cart-add':   $order->addToCart(); break;
    case 'cart-update':$order->updateCart(); break;
    case 'cart-remove':$order->removeFromCart($a); break;
    case 'checkout':   $order->checkout(); break;
    case 'orders':     $order->myOrders(); break;
    case 'order':
        if ($a === 'confirmation' && $p1) $order->confirmation($p1);
        else { http_response_code(404); echo '404 - Introuvable'; }
        break;

    case 'admin':
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ' . url('login')); exit;
        }
        if ($a === 'dashboard') $order->adminDashboard();
        elseif ($a === 'products') {
            if ($p1 === 'create') $menu->createProduct();
            elseif ($p1 === 'delete' && $p2) $menu->deleteProduct($p2);
            else $menu->adminProducts();
        }
        elseif ($a === 'orders') {
            if ($p1 === 'update' && $p2) $order->updateOrderStatus($p2);
            else $order->adminOrders();
        }
        else { http_response_code(404); echo '404 - Introuvable'; }
        break;

    default:
        http_response_code(404);
        echo "<h1>404 - Page non trouvée</h1>";
}