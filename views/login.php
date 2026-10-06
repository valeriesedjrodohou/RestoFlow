<div class="auth-form">
    <h1>Connexion</h1>
    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>
    <form method="POST">
        <label>Email</label>
        <input type="email" name="email" required>
        <label>Mot de passe</label>
        <input type="password" name="password" required>
        <button type="submit" class="btn-primary">Se connecter</button>
    </form>
    <p class="auth-link">Pas de compte ? <a href="<?= url('register') ?>">S'inscrire</a></p>
</div>