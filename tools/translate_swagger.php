<?php
// Simple script to translate a generated swagger.json file using a mapping.
// Usage: php tools/translate_swagger.php

$root = __DIR__ . '/..';
$storageSwagger = $root . '/storage/api-docs/swagger.json';
$publicSwagger = $root . '/public/swagger.json';

if (!file_exists($storageSwagger)) {
    echo "storage swagger.json not found: $storageSwagger\n";
    exit(1);
}

$mapping = [
    // Info
    'Bank Account Management API' => 'API de gestion des comptes bancaires',
    'API for managing bank accounts' => 'API pour gérer les comptes bancaires',
    'Production server' => 'Serveur de production',
    'Development server' => 'Serveur de développement',

    // Tags / endpoints
    'Accounts' => 'Comptes',
    'List accounts' => 'Lister les comptes',
    'Retrieve a list of accounts with filters and pagination' => 'Récupère la liste des comptes avec filtres et pagination',

    // Parameters / filters
    'Account type' => 'Type de compte',
    'Account status' => 'Statut du compte',
    'Search by holder name or account number' => 'Recherche par nom du titulaire ou numéro de compte',
    'Sort field' => 'Champ de tri',
    'Sort order' => 'Ordre de tri',
    'Items per page' => 'Éléments par page',

    // CRUD summaries and descriptions
    'Create a new account' => 'Créer un nouveau compte',
    'Create a new bank account with client verification' => 'Créer un nouveau compte bancaire avec vérification du client',
    'Get specific account' => 'Obtenir un compte spécifique',
    'Retrieve details of a specific account by its number' => 'Récupère les détails d\'un compte spécifique par son numéro',
    'Delete account' => 'Supprimer le compte',
    'Update account' => 'Mettre à jour le compte',

    // Responses / messages
    'List of accounts' => 'Liste des comptes',
    'Account details' => 'Détails du compte',
    'Account created successfully' => 'Compte créé avec succès',
    'Account updated successfully' => 'Compte mis à jour avec succès',
    'Account deleted successfully' => 'Compte supprimé avec succès',

    // Blocking endpoints
    'Block account' => 'Bloquer un compte',
    'Unblock account' => 'Débloquer un compte',
    'Block an active account and calculate blocking dates' => 'Bloquer un compte actif et calculer les dates de blocage',
    'Unblock a blocked account' => 'Débloquer un compte bloqué',
    'Suspicious activity detected' => 'Activité suspecte détectée',
    'Verification completed' => 'Vérification terminée',

    // Error and validation
    'error' => 'erreur',
    'Invalid request' => 'Requête invalide',
    'Invalid data' => 'Données invalides',
    'Validation error' => 'Erreur de validation',
    'Validation failed' => 'La validation a échoué',
    'Account not found' => 'Compte non trouvé',
    'Account not active or invalid data' => 'Compte non actif ou données invalides',
    'Account not blocked' => 'Compte non bloqué',
    'Unauthorized' => 'Non autorisé',
    'Too many requests' => 'Trop de requêtes',
    'Not found' => 'Non trouvé',

    // Misc common phrases
    'Account number' => 'Numéro de compte',
    'Application server' => 'Serveur applicatif',

    // Tag and docs
    'Bank account management endpoints' => "Points de terminaison de gestion des comptes bancaires",
    'API Documentation' => "Documentation de l'API",

    // Parameter/header descriptions
    'Content type' => 'Type de contenu',
    'Accept header' => 'En-tête Accept',
    'Language preference' => 'Préférence de langue',
    'Unique request identifier' => 'Identifiant unique de requête',
    'API version' => "Version de l'API",
];

$json = file_get_contents($storageSwagger);
if ($json === false) {
    echo "Failed to read $storageSwagger\n";
    exit(1);
}

$data = json_decode($json, true);
if ($data === null) {
    echo "Failed to decode JSON from $storageSwagger\n";
    exit(1);
}

function translateStrings(&$node, $mapping) {
    if (is_array($node)) {
        foreach ($node as $k => &$v) {
            translateStrings($v, $mapping);
        }
    } elseif (is_string($node)) {
        foreach ($mapping as $en => $fr) {
            // only replace whole phrases (case-sensitive)
            if (strpos($node, $en) !== false) {
                $node = str_replace($en, $fr, $node);
            }
        }
    }
}

translateStrings($data, $mapping);

// Update server URLs to ensure no duplicated /v1/v1 in servers array (if present)
if (!empty($data['servers']) && is_array($data['servers'])) {
    foreach ($data['servers'] as &$srv) {
        if (!empty($srv['url'])) {
            // normalize double v1 occurrences
            $srv['url'] = str_replace('/api/v1/v1', '/api/v1', $srv['url']);
        }
    }
}

$out = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
if ($out === false) {
    echo "Failed to encode translated JSON\n";
    exit(1);
}

file_put_contents($storageSwagger, $out);
echo "Wrote translated swagger.json to storage/api-docs/swagger.json\n";

if (file_exists($publicSwagger)) {
    file_put_contents($publicSwagger, $out);
    echo "Also updated public/swagger.json\n";
}

echo "Done.\n";
