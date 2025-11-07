<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Erreur serveur</title>
    <style>
        body{font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background:linear-gradient(135deg,#fa709a 0%,#fee140 100%);color:#333;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:20px}
        .error-container{text-align:center;max-width:600px;background:#fff;padding:40px;border-radius:10px;box-shadow:0 10px 40px rgba(0,0,0,0.2)}
        h1{font-size:100px;margin:0;color:#fa709a}
        h2{font-size:28px;margin:20px 0;color:#333}
        p{font-size:16px;margin:20px 0;color:#666}
        a{display:inline-block;margin-top:20px;padding:12px 30px;background:#fa709a;color:#fff;text-decoration:none;border-radius:5px;font-weight:bold;transition:transform 0.2s}
        a:hover{transform:translateY(-2px);box-shadow:0 4px 15px rgba(250,112,154,0.4)}
    </style>
</head>
<body>
    <div class="error-container">
        <h1>500</h1>
        <h2>Erreur serveur</h2>
        <p>Une erreur inattendue s'est produite. Nos équipes ont été notifiées.</p>
        <p>Veuillez réessayer ultérieurement.</p>
        <a href="<?= url('/dashboard') ?>">Retour à l'accueil</a>
    </div>
</body>
</html>
