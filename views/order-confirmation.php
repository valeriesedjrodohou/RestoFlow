<div class="confirmation">
    <div class="success-icon">✅</div>
    <h2>Merci pour votre commande #<?= $order['id'] ?>!</h2>
    <p>Votre commande a bien été enregistrée. Notre équipe la prépare puis un livreur vous l'apportera à l'adresse indiquée.</p>

    <div class="order-detail">
        <h3>Récapitulatif</h3>
        <table class="cart-table">
            <thead>
                <tr><th>Produit</th><th>Qté</th><th>Prix</th><th>Total</th></tr>
            </thead>
            <tbody>
                <?php foreach ($items as $it): ?>
                    <tr>
                        <td><?= e($it['product_name']) ?></td>
                        <td><?= $it['quantity'] ?></td>
                        <td><?= format_price($it['unit_price']) ?></td>
                        <td><?= format_price($it['unit_price'] * $it['quantity']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="summary-lines">
            <p>Frais de livraison : <span><?= format_price($order['delivery_fee']) ?></span></p>
            <p class="total-line">Total à payer : <strong><?= format_price($order['total_amount']) ?></strong></p>
            <p><small>Mode de paiement : <?= payment_label($order['payment_method']) ?></small></p>
        </div>
    </div>

    <div class="delivery-info">
        <h3>Informations de livraison</h3>
        <p><strong>Adresse :</strong> <?= e($order['delivery_address']) ?></p>
        <p><strong>Téléphone :</strong> <?= e($order['customer_phone']) ?></p>
        <?php if (!empty($order['notes'])): ?>
            <p><strong>Notes :</strong> <?= e($order['notes']) ?></p>
        <?php endif; ?>
        <p><strong>Statut :</strong>
            <span class="order-status status-<?= $order['status'] ?>"><?= status_label($order['status']) ?></span>
        </p>
    </div>

    <div class="confirmation-actions">
        <a href="<?= url('') ?>" class="btn-primary">Retour au menu</a>
        <a href="<?= url('orders') ?>" class="btn-link">Voir mes commandes</a>
    </div>
</div>