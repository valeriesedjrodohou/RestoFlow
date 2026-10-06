<div class="page-header">
    <h1>Tableau de bord</h1>
    <p>Bonjour <?= e($_SESSION['user_name']) ?> 👋</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <h3>Revenus (commandes livrées)</h3>
        <p class="stat-value"><?= format_price($total_revenue) ?></p>
    </div>
    <div class="stat-card">
        <h3>En attente / préparation</h3>
        <p class="stat-value"><?= $pending ?></p>
    </div>
    <div class="stat-card">
        <h3>En cours de livraison</h3>
        <p class="stat-value"><?= $delivering ?></p>
    </div>
    <div class="stat-card">
        <h3>Total commandes</h3>
        <p class="stat-value"><?= count($orders) ?></p>
    </div>
</div>

<div class="admin-nav">
    <a href="<?= url('admin/products') ?>" class="btn-primary">Gérer les produits</a>
    <a href="<?= url('admin/orders') ?>" class="btn-primary">Gérer les commandes</a>
</div>

<div class="admin-section">
    <h2>Dernières commandes</h2>
    <table class="admin-table">
        <thead><tr><th>#</th><th>Client</th><th>Total</th><th>Statut</th><th>Date</th></tr></thead>
        <tbody>
            <?php foreach (array_slice($orders, 0, 5) as $o): ?>
                <tr>
                    <td><?= $o['id'] ?></td>
                    <td><?= e($o['customer_name']) ?></td>
                    <td><?= format_price($o['total_amount']) ?></td>
                    <td><span class="order-status status-<?= $o['status'] ?>"><?= status_label($o['status']) ?></span></td>
                    <td><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($orders)): ?><tr><td colspan="5">Aucune commande</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>