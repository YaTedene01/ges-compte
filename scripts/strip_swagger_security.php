<?php
// Usage: php scripts/strip_swagger_security.php /path/to/public/swagger.json
$file = $argv[1] ?? __DIR__ . "/../public/swagger.json";
if (!file_exists($file)) {
    fwrite(STDERR, "File not found: $file\n");
    exit(1);
}

$content = file_get_contents($file);
$data = json_decode($content, true);
if ($data === null) {
    fwrite(STDERR, "Invalid JSON in $file\n");
    exit(1);
}

function strip_security(&$node){
    if (is_array($node)){
        // Remove 'security' keys on this level
        if (array_key_exists('security', $node)){
            unset($node['security']);
        }

        // If components exist here, remove securitySchemes inside
        if (isset($node['components']) && is_array($node['components']) && isset($node['components']['securitySchemes'])){
            unset($node['components']['securitySchemes']);
        }

        // If components.parameters contains AuthorizationHeader, remove it
        if (isset($node['components']) && isset($node['components']['parameters']) && is_array($node['components']['parameters']) && isset($node['components']['parameters']['AuthorizationHeader'])){
            unset($node['components']['parameters']['AuthorizationHeader']);
        }

        // Recurse through children
        foreach ($node as &$child){
            strip_security($child);
        }
    }
}

strip_security($data);

$new = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
if ($new === false){
    fwrite(STDERR, "Failed to encode JSON\n");
    exit(1);
}

file_put_contents($file, $new);
echo "Stripped security from: $file\n";
