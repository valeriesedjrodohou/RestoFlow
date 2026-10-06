<div class="page-header">
    <h1>Gestion des produits</h1>
</div>

<div class="admin-section">
    <h2>Ajouter un produit</h2>
    <form method="POST" action="<?= url('admin/products/create') ?>" class="product-form">
        <div class="form-row">
            <label>Nom</label>
            <input type="text" name="name" required>
        </div>
        <div class="form-row">
            <label>Prix (FCFA)</label>
            <input type="number" step="100" name="price" required>
        </div>
        <div class="form-row" style="grid-column: 1/-1;">
            <label>Description</label>
            <textarea name="description" rows="2"></textarea>
        </div>
        <div class="form-row" style="grid-column: 1/-1;">
            <label>URL de l'image</label>
            <input type="text" name="image" placeholder="https://exemple.com/image.jpg">
        </div>
        <div class="form-row">
            <label>Catégorie</label>
            <select name="category_id">
                <option value="">— Choisir —</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-row">
            <label>Options</label>
            <label class="checkbox-label"><input type="checkbox" name="available" checked> Disponible</label>
            <label class="checkbox-label"><input type="checkbox" name="featured" value="1"> Mis en avant (Recommandation)</label>
        </div>
        <div style="grid-column: 1/-1; text-align:right;">
            <button type="submit" class="btn-primary">Ajouter</button>
        </div>
    </form>
</div>

<div class="admin-section" style="margin-top: 30px;">
    <h2>Liste des produits</h2>
    <table class="admin-table">
        <thead><tr><th>Image</th><th>Nom</th><th>Prix</th><th>Catégorie</th><th>Statut</th><th>Actions</th></tr></thead>
        <tbody>
            <?php foreach ($products as $p): ?>
                <tr>
                    <td>
                        <?php if (!empty($p['image'])): ?>
                            <img src="<?= e($p['image']) ?>" alt="" style="width:40px; height:40px; object-fit:cover; border-radius:5px;">
                        <?php else: ?>
                            🍽️
                        <?php endif; ?>
                    </td>
                    <td><?= e($p['name']) ?></td>
                    <td><?= format_price($p['price']) ?></td>
                    <td><?= e($p['category_name'] ?? 'N/A') ?></td>
                    <td>
                        <?= $p['available'] ? '✅ Disponible' : '❌ Indisponible' ?>
                        <?= $p['featured'] ? ' (⭐ Top)' : '' ?>
                    </td>
                    <td><a href="<?= url('admin/products/delete/' . $p['id']) ?>" class="btn-remove" onclick="return confirm('Supprimer ?')">Supprimer</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>