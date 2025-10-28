<?php
// Usage: php scripts/translate_swagger_fr.php /path/to/public/swagger.json
$file = $argv[1] ?? __DIR__ . "/../public/swagger.json";
if (!file_exists($file)) {
    fwrite(STDERR, "File not found: $file\n");
    exit(1);
}

$backup = dirname($file) . '/swagger.fr.backup.json';
copy($file, $backup);
echo "Backup created: $backup\n";

$content = file_get_contents($file);
$data = json_decode($content, true);
if ($data === null) {
    fwrite(STDERR, "Invalid JSON in $file\n");
    exit(1);
}

// Exact phrase translations (cover common phrases from your swagger)
$dict = [
    // info
    'Bank Account Management API' => 'API de gestion des comptes bancaires',
    'API for managing bank accounts' => 'API pour gérer les comptes bancaires',
    // servers
    'Production server' => 'Serveur de production',
    'Development server' => 'Serveur de développement',
    // auth endpoints
    'User login' => "Connexion utilisateur",
    "Authenticates a user and returns an access token" => "Authentifie un utilisateur et renvoie un jeton d'accès",
    'Login successful' => 'Connexion réussie',
    'Invalid credentials' => 'Identifiants invalides',
    'Refresh access token' => "Rafraîchir le jeton d'accès",
    'Generates a new access token using the refresh token' => "Génère un nouveau jeton d'accès en utilisant le jeton de rafraîchissement",
    'User logout' => "Déconnexion de l'utilisateur",
    "Revokes the current user's access token" => "Révoque le jeton d'accès de l'utilisateur courant",
    // accounts
    'List accounts' => 'Lister les comptes',
    'Retrieve a list of accounts with filters and pagination' => 'Récupère une liste de comptes avec filtres et pagination',
    'Account type' => 'Type de compte',
    'Account status' => 'Statut du compte',
    'Search by holder name or account number' => 'Rechercher par nom du titulaire ou numéro de compte',
    'Sort field' => 'Champ de tri',
    'Sort order' => 'Ordre de tri',
    'Items per page' => "Éléments par page",
    'Create a new account' => 'Créer un nouveau compte',
    'Create a new bank account with client verification' => "Créer un nouveau compte bancaire avec vérification du client",
    'Get specific account' => 'Obtenir un compte spécifique',
    "Retrieve details of a specific account by its number" => "Récupère les détails d'un compte spécifique par son numéro",
    'Delete account' => 'Supprimer un compte',
    "Soft delete an account, change status to 'ferme' and delete associated transactions" => "Suppression logique d'un compte : changer le statut en 'ferme' et supprimer les transactions associées",
    'Update account' => 'Mettre à jour le compte',
    'Update account and associated client information' => 'Met à jour le compte et les informations client associées',
    'Block account' => 'Bloquer le compte',
    'Block an active account and calculate blocking dates' => 'Bloquer un compte actif et calculer les dates de blocage',
    'Unblock account' => 'Débloquer le compte',
    'Unblock a blocked account' => 'Débloquer un compte bloqué',
    // generic responses
    'Account created successfully' => 'Compte créé avec succès',
    'Account deleted successfully' => 'Compte supprimé avec succès',
    'Account updated successfully' => 'Compte mis à jour avec succès',
    'Token refreshed successfully' => 'Jeton rafraîchi avec succès',
    'Logout successful' => 'Déconnexion réussie',
    // tags
    'Accounts' => 'Comptes',
    'Authentification' => 'Authentification',
    'User authentification' => 'Authentification utilisateur',
    // other
];

// Partial phrase replacements (word-level)
$partials = [
    'User' => 'Utilisateur',
    'user' => 'utilisateur',
    'token' => 'jeton',
    'access token' => "jeton d'accès",
    'refresh token' => 'jeton de rafraîchissement',
    'accounts' => 'comptes',
    'account' => 'compte',
    'list' => 'liste',
    'List' => 'Lister',
    'Create' => 'Créer',
    'Delete' => 'Supprimer',
    'Update' => 'Mettre à jour',
    'Block' => 'Bloquer',
    'Unblock' => 'Débloquer',
    'Authentication' => 'Authentification',
    'Authentification' => 'Authentification',
    'server' => 'serveur',
    'servers' => 'serveurs',
    'description' => 'description',
    'email' => 'email',
    'password' => 'mot de passe',
    'response' => 'réponse',
];

// Keys representing HTTP methods — do not treat keys; we only translate string VALUES
$httpMethods = ['get','post','put','delete','patch','options','head'];

function translate_value($s, $dict, $partials){
    $orig = $s;
    // exact match
    if (isset($dict[$s])) return $dict[$s];

    // try trimming whitespace
    $t = trim($s);
    if (isset($dict[$t])) return $dict[$t];

    // apply partial replacements (case-sensitive then case-insensitive)
    $res = $s;
    foreach ($dict as $k => $v) {
        // replace any exact phrase occurrences inside the string
        if (strpos($res, $k) !== false) {
            $res = str_replace($k, $v, $res);
        }
    }
    foreach ($partials as $k => $v) {
        if (stripos($res, $k) !== false) {
            // preserve case of first char
            $res = preg_replace_callback('/' . preg_quote($k, '/') . '/i', function($m) use ($v){
                // if original is capitalized
                if (ctype_upper($m[0][0])) return ucfirst($v);
                return $v;
            }, $res);
        }
    }

    // If nothing changed, return original
    if ($res === $orig) return $orig;
    return $res;
}

// Traverse and translate string values
function traverse_and_translate(&$node, $dict, $partials, $httpMethods){
    if (is_array($node)){
        foreach ($node as $key => &$val){
            // skip keys that are HTTP methods when iterating (we still translate the inner values)
            // but we DO translate the string values themselves; the user asked to keep HTTP verbs — keys remain unchanged

            if (is_string($val)){
                // translate strings
                $val = translate_value($val, $dict, $partials);
            } else {
                traverse_and_translate($val, $dict, $partials, $httpMethods);
            }
        }
        unset($val);
    }
}

traverse_and_translate($data, $dict, $partials, $httpMethods);

$new = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
if ($new === false) {
    fwrite(STDERR, "Failed to encode JSON\n");
    exit(1);
}

file_put_contents($file, $new);
echo "Translated swagger to French (best-effort) in: $file\n";
echo "Backup kept at: $backup\n";
