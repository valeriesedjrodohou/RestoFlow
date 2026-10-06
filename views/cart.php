<?php $user = $user ?? null; ?>
<div class="page-header">
    <h1>Mon Panier</h1>
</div>

<?php if (empty($_SESSION['cart'])): ?>
    <p class="empty">Votre panier est vide. <a href="<?= url('') ?>">Voir le menu</a></p>
<?php else: ?>
    <form method="POST" action="<?= url('cart-update') ?>">
        <table class="cart-table">
            <thead>
                <tr><th>Produit</th><th>Prix</th><th>Quantité</th><th>Total</th><th></th></tr>
            </thead>
            <tbody>
                <?php $subtotal = 0; foreach ($_SESSION['cart'] as $item): ?>
                    <tr>
                        <td>
                            <div class="cart-item">
                                <?php if (!empty($item['image'])): ?>
                                    <img src="<?= e($item['image']) ?>" alt="" style="width:50px; height:50px; object-fit:cover; border-radius:8px;">
                                <?php endif; ?>
                                <span><?= e($item['name']) ?></span>
                            </div>
                        </td>
                        <td><?= format_price($item['price']) ?></td>
                        <td><input type="number" name="quantity[<?= $item['id'] ?>]" value="<?= $item['quantity'] ?>" min="1" class="qty-input"></td>
                        <td><?= format_price($item['price'] * $item['quantity']) ?></td>
                        <td><a href="<?= url('cart-remove/' . $item['id']) ?>" class="btn-remove">Supprimer</a></td>
                    </tr>
                    <?php $subtotal += $item['price'] * $item['quantity']; endforeach; ?>
            </tbody>
        </table>
        <div class="cart-summary">
            <p>Sous-total : <strong><?= format_price($subtotal) ?></strong></p>
            <p>Frais de livraison : <strong><?= format_price(1500) ?></strong></p>
            <p class="total">Total : <strong><?= format_price($subtotal + 1500) ?></strong></p>
            <button type="submit" class="btn-update">Mettre à jour le panier</button>
        </div>
    </form>

    <?php if ($user): ?>
        <form method="POST" action="<?= url('checkout') ?>" class="checkout-form">
            <h2>Finaliser la commande</h2>
            <label>Téléphone</label>
            <input type="text" name="delivery_phone" value="<?= e($user['phone']) ?>" required>
            <label>Adresse complète de livraison</label>
            <textarea name="delivery_address" rows="3" required><?= e($user['address']) ?></textarea>
            <label>Notes pour le livreur (optionnel)</label>
            <textarea name="notes" rows="2" placeholder="Ex : sonner à l'interphone, sans porc, etc."></textarea>
            
            <label>Mode de paiement</label>
            <select name="payment_method" class="rf-status-select" style="width: 100%; margin-bottom: 20px;">
                <option value="cash_on_delivery">Paiement à la livraison (Espèces)</option>
                <option value="orange_money">Orange Money</option>
                <option value="mtn_money">MTN Money</option>
                <option value="wave">Wave</option>
                <option value="card">Carte bancaire</option>
            </select>

            <button type="submit" class="btn-checkout">Confirmer la commande</button>
        </form>
    <?php else: ?>
        <p class="info">Veuillez vous <a href="<?= url('login') ?>">connecter</a> pour finaliser votre commande.</p>
    <?php endif; ?>
<?php endif; ?>