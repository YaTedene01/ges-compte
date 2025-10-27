<!DOCTYPE html>
<html>
<head>
    <title>Authentification de votre compte</title>
</head>
<body>
    <h1>Bonjour {{ $client->titulaire }},</h1>
    <p>Votre compte a été créé avec succès.</p>
    <p>Vos identifiants de connexion sont :</p>
    <ul>
        <li>Email : {{ $client->email }}</li>
        <li>Mot de passe : {{ $client->password }}</li>
    </ul>
    <p>Pour votre première connexion, utilisez le code suivant : {{ $client->code }}</p>
    <p>Merci de votre confiance.</p>
</body>
</html>