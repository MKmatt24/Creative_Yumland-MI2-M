<?php
//Initialisation de la session avec paramètres sécurisés
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

//Régénération de l'ID de session pour prévenir le session fixation
if (!isset($_SESSION['_initiated'])) {
    session_regenerate_id(true);
    $_SESSION['_initiated'] = true;
}

//Vérification si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit();
}

// Vérification du statut : expulsion si le compte est banni entre-temps
$users_db = json_decode(file_get_contents(__DIR__ . '/../DATA/users.json'), true);
foreach ($users_db as $u) {
    if ($u['id'] == $_SESSION['user_id'] && ($u['statut'] === 'suspendu' || $u['statut'] === 'inactif')) {
        session_destroy();
        header('Location: connexion.php?error=account_disabled');
        exit();
    }
}

// Génération d'un token CSRF si absent
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Vérifie le token CSRF d'une requête POST.
 * À appeler dans chaque traitement POST.
 */
function verifier_csrf() {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Token de sécurité invalide. Rechargez la page.']);
        exit();
    }
}
?>
