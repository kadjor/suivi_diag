<div class="auth-form">
    <h2>Réinitialiser le mot de passe</h2>

    <form method="POST" action="/reset-password">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">

        <div class="form-group">
            <label for="password">Nouveau mot de passe</label>
            <input type="password" id="password" name="password" required autofocus>
        </div>

        <div class="form-group">
            <label for="password_confirm">Confirmer le mot de passe</label>
            <input type="password" id="password_confirm" name="password_confirm" required>
        </div>

        <button type="submit" class="btn btn-primary">Réinitialiser</button>
    </form>
</div>
