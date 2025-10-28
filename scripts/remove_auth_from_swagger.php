<?php
// Usage: php scripts/remove_auth_from_swagger.php /path/to/public/swagger.json
$file = $argv[1] ?? __DIR__ . "/../public/swagger.json";
if (!file_exists($file)) {
    fwrite(STDERR, "File not found: $file\n");
    exit(1);
}

$backup = dirname($file) . '/swagger.noauth.backup.json';
copy($file, $backup);
echo "Backup created: $backup\n";

$content = file_get_contents($file);
$data = json_decode($content, true);
if ($data === null) {
    fwrite(STDERR, "Invalid JSON in $file\n");
    exit(1);
}

$authTagNames = ['Authentification', 'Authentication', 'Auth']; // tolerate variants
$authPathPrefixes = ['/v1/authentication', '/api/auth'];

// Remove paths that are auth-related or have the auth tag
foreach (array_keys($data['paths'] ?? []) as $path) {
    $removePath = false;
    // prefix match
    foreach ($authPathPrefixes as $prefix) {
        if (strpos($path, $prefix) === 0) {
            $removePath = true;
            break;
        }
    }
    if ($removePath) {
        unset($data['paths'][$path]);
        continue;
    }

    // remove operations tagged with auth, or strip 401 responses inside operations
    foreach ($data['paths'][$path] as $method => &$operation) {
        // if tags present and intersect with authTagNames => mark removal of whole path
        if (isset($operation['tags']) && is_array($operation['tags'])) {
            foreach ($operation['tags'] as $t) {
                if (in_array($t, $authTagNames, true)) {
                    $removePath = true;
                    break 2;
                }
            }
        }

        // Remove 401 response if present
        if (isset($operation['responses']) && isset($operation['responses']['401'])) {
            unset($operation['responses']['401']);
        }
    }
    unset($operation);
    if ($removePath) {
        unset($data['paths'][$path]);
    }
}

// Remove auth-related schemas
$schemasToRemove = ['LoginRequest', 'AuthResponse'];
if (isset($data['components']['schemas']) && is_array($data['components']['schemas'])) {
    foreach ($schemasToRemove as $s) {
        if (isset($data['components']['schemas'][$s])) {
            unset($data['components']['schemas'][$s]);
        }
    }
}

// Remove AuthorizationHeader param if still present
if (isset($data['components']['parameters']['AuthorizationHeader'])) {
    unset($data['components']['parameters']['AuthorizationHeader']);
}

// Remove tag entries named 'Authentification' (or variants)
if (isset($data['tags']) && is_array($data['tags'])) {
    $data['tags'] = array_values(array_filter($data['tags'], function($tag) use ($authTagNames) {
        return !in_array($tag['name'] ?? '', $authTagNames, true);
    }));
}

// Also remove top-level 'security' if present
if (isset($data['security'])) {
    unset($data['security']);
}

// Re-encode
$new = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
if ($new === false) {
    fwrite(STDERR, "Failed to encode JSON\n");
    exit(1);
}

file_put_contents($file, $new);
echo "Auth removed from: $file\n";
echo "If you want stricter removal (e.g., 401 in examples), tell me.\n";
