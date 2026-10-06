<div class="auth-form">
    <h1>Inscription</h1>
    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>
    <form method="POST">
        <label>Nom complet</label>
        <input type="text" name="name" required>
        <label>Email</label>
        <input type="email" name="email" required>
        <label>Téléphone</label>
        <input type="text" name="phone">
        <label>Adresse</label>
        <textarea name="address" rows="2"></textarea>
        <label>Mot de passe</label>
        <input type="password" name="password" required>
        <button type="submit" class="btn-primary">Créer mon compte</button>
    </form>
    <p class="auth-link">Déjà inscrit ? <a href="<?= url('login') ?>">Connexion</a></p>
</div>