<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Connexion' ?> - Suivi Diagnostics</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/auth.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <h1>Suivi Diagnostics</h1>
                <p>Plateforme de gestion de diagnostics immobiliers</p>
            </div>

            <?php include __DIR__ . '/../partials/flash_messages.php'; ?>

            <div class="auth-content">
                <?= $content ?>
            </div>

            <div class="auth-footer">
                <p>&copy; <?= date('Y') ?> Suivi Diagnostics - Tous droits réservés</p>
            </div>
        </div>
    </div>

    <script src="/assets/js/main.js"></script>
</body>
</html>
