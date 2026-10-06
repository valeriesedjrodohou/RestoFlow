-- ============================================================
-- RestoFlow - Base de données (v2 avec paiements + images réelles)
-- À importer dans phpMyAdmin (WAMP) :
--   1. Ouvrir http://localhost/phpmyadmin
--   2. Onglet "Importer"
--   3. Sélectionner ce fichier
--   4. Cliquer "Exécuter"
-- ============================================================

CREATE DATABASE IF NOT EXISTS `restoflow`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE `restoflow`;

-- Suppression des tables existantes (ordre : enfants d'abord)
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `sms_logs`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;

-- ----------------------------------------------------------
-- Table users
-- ----------------------------------------------------------
CREATE TABLE `users` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(100) NOT NULL,
  `email`      VARCHAR(150) NOT NULL UNIQUE,
  `phone`      VARCHAR(30)  NOT NULL,
  `password`   VARCHAR(255) NOT NULL,
  `address`    VARCHAR(255) DEFAULT NULL,
  `role`       ENUM('client','admin') NOT NULL DEFAULT 'client',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------
-- Table categories
-- ----------------------------------------------------------
CREATE TABLE `categories` (
  `id`   INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `icon` VARCHAR(50)  DEFAULT '🍽️'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------
-- Table products — avec image URL réelle
-- ----------------------------------------------------------
CREATE TABLE `products` (
  `id`          INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `name`        VARCHAR(150) NOT NULL,
  `description` TEXT,
  `price`       DECIMAL(10,2) NOT NULL,
  `image`       VARCHAR(500) DEFAULT NULL,
  `available`   TINYINT(1) DEFAULT 1,
  `featured`    TINYINT(1) DEFAULT 0,
  `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`)
    REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------
-- Table orders — avec colonnes paiement
-- ----------------------------------------------------------
CREATE TABLE `orders` (
  `id`               INT AUTO_INCREMENT PRIMARY KEY,
  `user_id`          INT NOT NULL,
  `customer_name`    VARCHAR(100) NOT NULL,
  `customer_phone`   VARCHAR(30)  NOT NULL,
  `delivery_address` TEXT NOT NULL,
  `notes`            TEXT,
  `subtotal`         DECIMAL(10,2) NOT NULL DEFAULT 0,
  `total_amount`     DECIMAL(10,2) NOT NULL,
  `delivery_fee`     DECIMAL(10,2) NOT NULL DEFAULT 1500.00,
  `status`           ENUM('pending','preparing','delivering','delivered','cancelled')
                     NOT NULL DEFAULT 'pending',
  `payment_method`   ENUM('card','orange_money','mtn_money','wave','cash_on_delivery')
                     DEFAULT NULL,
  `payment_status`   ENUM('unpaid','paid','failed','refunded')
                     NOT NULL DEFAULT 'unpaid',
  `payment_ref`      VARCHAR(100) DEFAULT NULL,
  `created_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`)
    REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------
-- Table order_items
-- ----------------------------------------------------------
CREATE TABLE `order_items` (
  `id`           INT AUTO_INCREMENT PRIMARY KEY,
  `order_id`     INT NOT NULL,
  `product_id`   INT NOT NULL,
  `product_name` VARCHAR(150) NOT NULL,
  `quantity`     INT NOT NULL DEFAULT 1,
  `unit_price`   DECIMAL(10,2) NOT NULL,
  CONSTRAINT `fk_items_order`   FOREIGN KEY (`order_id`)   REFERENCES `orders`(`id`)   ON DELETE CASCADE,
  CONSTRAINT `fk_items_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------
-- Table payments — pour tracer les tentatives de paiement
-- ----------------------------------------------------------
CREATE TABLE `payments` (
  `id`             INT AUTO_INCREMENT PRIMARY KEY,
  `order_id`       INT NOT NULL,
  `method`         ENUM('card','orange_money','mtn_money','wave','cash_on_delivery') NOT NULL,
  `amount`         DECIMAL(10,2) NOT NULL,
  `status`         ENUM('pending','success','failed','refunded') NOT NULL DEFAULT 'pending',
  `transaction_id` VARCHAR(100) DEFAULT NULL,
  `provider_ref`   VARCHAR(255) DEFAULT NULL,
  `phone`          VARCHAR(30)  DEFAULT NULL,
  `card_last4`     VARCHAR(4)   DEFAULT NULL,
  `error_message`  TEXT,
  `created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_payments_order` FOREIGN KEY (`order_id`)
    REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------
-- Table sms_logs — journal des SMS "envoyés" (simulation)
-- ----------------------------------------------------------
CREATE TABLE `sms_logs` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `phone`      VARCHAR(30) NOT NULL,
  `message`    TEXT NOT NULL,
  `type`       VARCHAR(50) DEFAULT 'notification',
  `status`     ENUM('sent','failed','simulated') NOT NULL DEFAULT 'simulated',
  `order_id`   INT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Données de démonstration
-- ============================================================

INSERT INTO `categories` (`name`, `icon`) VALUES
('Entrées',  '🥗'),
('Plats',    '🍽️'),
('Burgers',  '🍔'),
('Pizzas',   '🍕'),
('Desserts', '🍰'),
('Boissons', '🥤');

-- Produits avec VRAIES images (URLs stables OSS)
INSERT INTO `products` (`category_id`, `name`, `description`, `price`, `image`, `featured`) VALUES
-- Entrées
(1, 'Salade César',        'Laitue romaine, croûtons dorés, parmesan affiné, sauce César crémeuse.', 3500.00, 'https://sfile.chatglm.cn/images-ppt/2910c6409d9d.jpg', 1),
(1, 'Soupe du jour',       'Soupe de légumes frais mijotée lentement, servie avec pain grillé.',     2500.00, 'https://sfile.chatglm.cn/images-ppt/80ad06133bca.jpg', 0),
(1, 'Crevettes pil-pil',   'Crevettes sautées à l''ail, piment doux et huile d''olive vierge.',      5500.00, 'https://sfile.chatglm.cn/images-ppt/7f50a5d4424b.jpg', 1),
-- Plats
(2, 'Poulet braisé',       'Demi-poulet braisé au charbon, frites maison, salade et sauce piri-piri.',6000.00, 'https://sfile.chatglm.cn/images-ppt/a9275b7772e3.jpg', 1),
(2, 'Poisson braisé',      'Poisson entier braisé, attiéké, oignons, tomates, piment frais.',         7000.00, 'https://sfile.chatglm.cn/images-ppt/3629d6e9b7e4.jpg', 1),
(2, 'Riz sauce arachide',  'Riz parfumé accompagné de sauce d''arachide onctueuse et viande tendre.', 5000.00, 'https://sfile.chatglm.cn/images-ppt/c9ebdac99343.jpg', 0),
(2, 'Attiéké poisson',     'Attieké moelleux, poisson frit croustillant, oignons, tomates, piment.',  4500.00, 'https://sfile.chatglm.cn/images-ppt/e6a728f02896.jpg', 1),
-- Burgers
(3, 'Cheeseburger',        'Steak haché 150g, cheddar fondu, salade, tomate, oignon, sauce maison.',  4000.00, 'https://sfile.chatglm.cn/images-ppt/e8a183d25cd4.jpg', 1),
(3, 'Double Cheeseburger', 'Deux steaks juteux, double cheddar, bacon croustillant, sauce barbecue.', 6000.00, 'https://sfile.chatglm.cn/images-ppt/5110af25cdbb.jpg', 1),
(3, 'Chicken Burger',      'Filet de poulet pané croustillant, cheddar, salade, sauce ranch.',        4500.00, 'https://sfile.chatglm.cn/images-ppt/c4ebfdef99dc.jpg', 0),
-- Pizzas
(4, 'Margherita',          'Tomate San Marzano, mozzarella fior di latte, basilic frais, huile d''olive.', 5000.00, 'https://sfile.chatglm.cn/images-ppt/69872a41ef27.jpeg', 1),
(4, 'Reine',               'Jambon cru, champignons frais, mozzarella, sauce tomate, origan.',        6000.00, 'https://sfile.chatglm.cn/images-ppt/1a217e7ff043.png', 0),
(4, 'Calzone',             'Pizza fermée garnie de jambon, champignons, mozzarella et ricotta.',      6500.00, 'https://sfile.chatglm.cn/images-ppt/f8ac4303c485.jpg', 0),
(4, 'Quatre fromages',     'Mozzarella, gorgonzola, parmesan, chèvre sur base crème.',                7000.00, 'https://sfile.chatglm.cn/images-ppt/47448d47e955.jpg', 0),
-- Desserts
(5, 'Tiramisu',            'Mascarpone onctueux, café espresso, biscuits imbibés, cacao amer.',       3000.00, 'https://sfile.chatglm.cn/images-ppt/c2dcfc1ad6bd.jpg', 1),
(5, 'Fondant au chocolat', 'Cœur coulant au chocolat noir 70%, boule de glace vanille.',              3500.00, 'https://sfile.chatglm.cn/images-ppt/2658e704d5eb.jpg', 1),
(5, 'Salade de fruits',    'Fruits frais de saison, sirop léger à la menthe.',                        2500.00, 'https://sfile.chatglm.cn/images-ppt/373f1686dd04.jpg', 0),
-- Boissons
(6, 'Coca-Cola 33cl',      'Boisson gazeuse fraîche, servie bien fraîche avec glaçons.',              1000.00, 'https://sfile.chatglm.cn/images-ppt/ff2d27e62c9d.jpg', 0),
(6, 'Jus d''orange frais', 'Jus d''orange pressé minute, 100% fruit, sans sucre ajouté.',             1500.00, 'https://sfile.chatglm.cn/images-ppt/aa84a8bafccf.jpg', 0),
(6, 'Bissap',              'Jus d''hibiscus maison, menthe fraîche, note de vanille.',                1200.00, 'https://sfile.chatglm.cn/images-ppt/e89c733bcee8.png', 1),
(6, 'Eau minérale 50cl',   'Eau de source plate, servie fraîche.',                                     800.00, 'https://sfile.chatglm.cn/images-ppt/0cb04239418e.jpg', 0);

-- Comptes utilisateurs
INSERT INTO `users` (`name`, `email`, `phone`, `password`, `address`, `role`) VALUES
('Administrateur', 'admin@restoflow.com', '00000000',
 '$2y$10$MoXYVDUIPc0w/QKNHUTc6eIeI0UlxftTCL0ckaX7pZ1kh6m1AKnhy',
 NULL, 'admin'),
('Client Démo', 'client@demo.com', '0700000000',
 '$2y$10$QW/WSjHTCz3e4xLUcy5loe1UkJQiJHr/J65YmEWATLhw0YDAOSMH.',
 'Rue des Lagunes, Abidjan', 'client');
