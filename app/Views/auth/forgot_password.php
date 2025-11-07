<div class="auth-form">
    <h2>Mot de passe oublié</h2>
    <p>Entrez votre adresse email pour recevoir un lien de réinitialisation.</p>

    <form method="POST" action="/forgot-password">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required autofocus>
        </div>

        <button type="submit" class="btn btn-primary">Envoyer le lien</button>
    </form>

    <div class="auth-links">
        <a href="/login">Retour à la connexion</a>
    </div>
</div>
