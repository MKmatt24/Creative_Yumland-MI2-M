<?php
// Routeur pour le serveur PHP intégré (php -S localhost:5000 router.php)
// Bloque l'accès direct au dossier DATA/ depuis le navigateur

$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

// Bloquer l'accès direct aux fichiers du dossier DATA
if (preg_match('#^/DATA/#i', $path)) {
    http_response_code(403);
    echo '403 Forbidden';
    return true;
}

// Pour tout le reste, laisser le serveur PHP gérer normalement
return false;