<div class="page-header">
    <h1>Mes commandes</h1>
</div>

<?php if (empty($orders)): ?>
    <p class="empty">Aucune commande pour le moment.</p>
<?php else: ?>
    <div class="orders-list">
        <?php foreach ($orders as $o): ?>
            <div class="order-card">
                <div class="order-header">
                    <span class="order-num">Commande #<?= $o['id'] ?></span>
                    <span class="order-status status-<?= $o['status'] ?>"><?= status_label($o['status']) ?></span>
                </div>
                <p><small><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></small></p>
                <p>Total : <strong><?= number_format($o['total'], 2) ?> €</strong></p>
                <p>Adresse : <?= e($o['delivery_address']) ?></p>
                <a href="<?= url('order/confirmation/' . $o['id']) ?>" class="btn-link">Voir détails →</a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>