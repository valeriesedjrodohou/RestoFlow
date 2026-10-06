<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RestoFlow - Commande et livraison en ligne</title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<header class="navbar">
    <div class="container nav-container">
        <a href="<?= url('') ?>" class="logo">
            <span class="logo-icon">🍽️</span> RestoFlow
        </a>
        <nav class="nav-links">
            <a href="<?= url('') ?>">Menu</a>
            <a href="<?= url('cart') ?>">Panier (<?= isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0 ?>)</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?= url('orders') ?>">Mes commandes</a>
                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    <a href="<?= url('admin/dashboard') ?>">Admin</a>
                <?php endif; ?>
                <a href="<?= url('logout') ?>" class="btn-logout">Déconnexion (<?= e($_SESSION['user_name']) ?>)</a>
            <?php else: ?>
                <a href="<?= url('login') ?>" class="btn-login">Connexion</a>
                <a href="<?= url('register') ?>" class="btn-register">Inscription</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container"></main>