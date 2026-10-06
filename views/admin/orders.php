<?php
 $statusLabels = [
    'pending' => 'En attente',
    'preparing' => 'En préparation',
    'delivering' => 'En livraison',
    'delivered' => 'Livré',
    'cancelled' => 'Annulé'
];
 $currentFilter = $_GET['status'] ?? 'all';
?>

<div class="admin-header-flex">
    <h1 class="page-title">Gestion des commandes</h1>
    <span class="badge-count">🛍️ <?= count($orders) ?> commande(s)</span>
</div>

<div class="rf-filter-tabs">
    <a href="<?= url('admin/orders') ?>" class="rf-filter-tab <?= $currentFilter === 'all' ? 'active' : '' ?>">Toutes</a>
    <?php foreach ($statusLabels as $key => $label): ?>
        <a href="<?= url('admin/orders?status=' . $key) ?>" class="rf-filter-tab <?= $currentFilter === $key ? 'active' : '' ?>">
            <?= e($label) ?>
        </a>
    <?php endforeach; ?>
</div>

<?php if (empty($orders)): ?>
    <div class="rf-empty">
        <div class="rf-empty-icon">📭</div>
        <h2>Aucune commande</h2>
        <p>Aucune commande ne correspond à ce filtre pour le moment.</p>
    </div>
<?php else: ?>
    <div class="orders-list-admin">
        <?php foreach ($orders as $o): ?>
            <?php if ($currentFilter !== 'all' && $o['status'] !== $currentFilter) continue; ?>
            
            <article class="rf-admin-order-card">
                <div class="rf-admin-order-top">
                    <div>
                        <h5 class="rf-order-title">Commande #<?= (int)$o['id'] ?></h5>
                        <p class="rf-muted">
                            📅 <?= date('d/m/Y à H:i', strtotime($o['created_at'])) ?>
                            &nbsp;•&nbsp; 👤 <?= e($o['customer_name']) ?>
                            &nbsp;•&nbsp; 📞 <?= e($o['customer_phone']) ?>
                        </p>
                        <p class="rf-muted">
                            <small>
                                💳 Paiement : 
                                <?php if ($o['payment_status'] === 'paid'): ?>
                                    <span class="rf-tag rf-tag-success"><?= e(payment_label($o['payment_method'])) ?></span>
                                <?php elseif ($o['payment_method'] === 'cash_on_delivery'): ?>
                                    <span class="rf-tag rf-tag-warning">💵 À la livraison</span>
                                <?php else: ?>
                                    <span class="rf-tag rf-tag-danger">Non payé</span>
                                <?php endif; ?>
                            </small>
                        </p>
                    </div>
                    <span class="order-status status-<?= $o['status'] ?>">
                        <?= status_label($o['status']) ?>
                    </span>
                </div>

                <div class="rf-order-body">
                    <div class="rf-order-items">
                        <strong>Articles :</strong>
                        <?php foreach ($o['items'] as $it): ?>
                            <div class="rf-item-line">
                                <span><strong><?= (int)$it['quantity'] ?>×</strong> <?= e($it['product_name']) ?></span>
                                <span class="rf-item-price"><?= format_price($it['unit_price'] * $it['quantity']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="rf-order-info">
                        <div class="rf-info-line">📍 <strong>Adresse :</strong><br><?= e($o['delivery_address']) ?></div>
                        <?php if (!empty($o['notes'])): ?>
                            <div class="rf-info-line rf-notes">📝 <em><?= e($o['notes']) ?></em></div>
                        <?php endif; ?>
                        <div class="rf-totals">
                            <div class="rf-total-line">
                                <span>Livraison</span>
                                <span><?= format_price($o['delivery_fee']) ?></span>
                            </div>
                            <div class="rf-total-main">
                                <span>Total</span>
                                <span><?= format_price($o['total_amount']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <form method="post" action="<?= url('admin/orders/update/' . $o['id']) ?>" class="rf-order-actions">
                    <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
                    
                    <div class="rf-form-group">
                        <label>Changer le statut :</label>
                        <select name="status" class="rf-status-select">
                            <?php foreach ($statusLabels as $k => $label): ?>
                                <option value="<?= $k ?>" <?= $o['status'] === $k ? 'selected' : '' ?>>
                                    <?= e($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn-primary btn-sm">✔️ Mettre à jour</button>
                </form>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>