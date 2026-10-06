<div class="page-header">
    <h1>RestoFlow</h1>
    <p>Découvrez notre carte et faites-vous livrer rapidement ! 🚴‍♂️</p>
</div>

<div class="layout-container">
    <div class="menu-content">
        <!-- Recommandations (Featured) -->
        <div class="recommendations-section">
            <h2>⭐ Nos Recommandations</h2>
            <div class="reco-grid">
                <?php foreach ($products as $p): ?>
                    <?php if ($p['featured'] == 1): ?>
                        <div class="reco-card">
                            <div class="reco-img">
                                <img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>" style="width:100%; height:100%; object-fit:cover;">
                            </div>
                            <div class="reco-info">
                                <h3><?= e($p['name']) ?></h3>
                                <p><?= e($p['description']) ?></p>
                                <div class="reco-footer">
                                    <span class="price"><?= format_price($p['price']) ?></span>
                                    <form method="POST" action="<?= url('cart-add') ?>">
                                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                        <input type="hidden" name="name" value="<?= e($p['name']) ?>">
                                        <input type="hidden" name="price" value="<?= $p['price'] ?>">
                                        <input type="hidden" name="image" value="<?= e($p['image']) ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn-add">+ Ajouter</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Notre Carte -->
        <div class="menu-section">
            <h2>📜 Notre Carte</h2>
            <?php 
            $cats = [];
            foreach ($products as $p) {
                $cname = $p['category_name'] ?? 'Divers';
                if (!isset($cats[$cname])) $cats[$cname] = [];
                $cats[$cname][] = $p;
            }
            foreach ($cats as $catName => $catProducts): 
            ?>
                <div class="category-block">
                    <h3 class="category-title"><?= e($catName) ?></h3>
                    <div class="menu-list">
                        <?php foreach ($catProducts as $p): ?>
                            <div class="menu-item">
                                <div class="item-info">
                                    <strong><?= e($p['name']) ?></strong>
                                    <p><?= e($p['description']) ?></p>
                                </div>
                                <div class="item-action">
                                    <span class="price"><?= format_price($p['price']) ?></span>
                                    <form method="POST" action="<?= url('cart-add') ?>">
                                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                        <input type="hidden" name="name" value="<?= e($p['name']) ?>">
                                        <input type="hidden" name="price" value="<?= $p['price'] ?>">
                                        <input type="hidden" name="image" value="<?= e($p['image']) ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn-add-circle">+</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Panier Latéral -->
    <aside class="cart-sidebar">
        <h3>🛒 Ma Commande</h3>
        <?php if (empty($_SESSION['cart'])): ?>
            <p class="empty-cart">Votre panier est vide.<br>Sélectionnez un plat pour commencer.</p>
        <?php else: ?>
            <div class="cart-items">
                <?php $subtotal = 0; ?>
                <?php foreach ($_SESSION['cart'] as $item): ?>
                    <div class="sidebar-cart-item">
                        <div class="sci-info">
                            <span class="sci-qty"><?= $item['quantity'] ?>x</span>
                            <span class="sci-name"><?= e($item['name']) ?></span>
                        </div>
                        <div class="sci-actions">
                            <span class="sci-price"><?= format_price($item['price'] * $item['quantity']) ?></span>
                            <a href="<?= url('cart-remove/' . $item['id']) ?>" class="sci-remove">&times;</a>
                        </div>
                    </div>
                    <?php $subtotal += $item['price'] * $item['quantity']; ?>
                <?php endforeach; ?>
            </div>
            
            <div class="cart-summary-sidebar">
                <div class="summary-line"><span>Sous-total</span><span><?= format_price($subtotal) ?></span></div>
                <div class="summary-line"><span>Livraison</span><span><?= format_price(1500) ?></span></div>
                <div class="summary-total"><span>Total</span><span><?= format_price($subtotal + 1500) ?></span></div>
            </div>
            
            <a href="<?= url('cart') ?>" class="btn-checkout-side">Commander</a>
        <?php endif; ?>
    </aside>
</div>