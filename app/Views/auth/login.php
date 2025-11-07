<div class="auth-form">
    <h2>Connexion</h2>

    <form method="POST" action="/login">
        <div class="form-group">
            <label for="username">Nom d'utilisateur</label>
            <input type="text" id="username" name="username" required autofocus>
        </div>

        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" required>
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="remember"> Se souvenir de moi
            </label>
        </div>

        <button type="submit" class="btn btn-primary">Se connecter</button>
    </form>

    <div class="auth-links">
        <a href="/forgot-password">Mot de passe oublié ?</a>
    </div>
</div>
