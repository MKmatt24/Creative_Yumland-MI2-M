<?php
session_start();
header('Content-Type: application/json');

// Vérification que la requête est bien en POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

// Sécurité : vérification si l'utilisateur est bloqué
if (isset($_SESSION['user_id'])) {
    $users = json_decode(file_get_contents('../DATA/users.json'), true);
    foreach ($users as $u) {
        if ($u['id'] == $_SESSION['user_id'] && (($u['est_bloque'] ?? false) || ($u['statut'] ?? '') === 'suspendu')) {
            echo json_encode(['success' => false, 'message' => 'Votre compte est suspendu.']);
            exit();
        }
    }
}

// Récupération de l'index du produit et de l'action (plus ou moins)
$index = isset($_POST['index']) ? (int)$_POST['index'] : -1;
$action = $_POST['action'] ?? '';

if (!isset($_SESSION['panier']) || !isset($_SESSION['panier'][$index])) {
    echo json_encode(['success' => false, 'message' => 'Produit introuvable dans le panier.']);
    exit;
}

// Modification de la quantité
if ($action === 'plus') {
    $_SESSION['panier'][$index]['quantite']++;
} elseif ($action === 'moins') {
    $_SESSION['panier'][$index]['quantite']--;
    
    // Si la quantité tombe à 0, on retire l'article du panier
    if ($_SESSION['panier'][$index]['quantite'] <= 0) {
        array_splice($_SESSION['panier'], $index, 1);
    }
}

// Recalcul des totaux pour une mise à jour fluide de l'interface
$nouveau_total = 0;
$nouveau_compteur = 0;
foreach ($_SESSION['panier'] as $item) {
    $nouveau_total += (float)$item['prix'] * (int)$item['quantite'];
    $nouveau_compteur += (int)$item['quantite'];
}

echo json_encode([
    'success' => true,
    'nouveau_compteur' => $nouveau_compteur,
    'nouveau_total' => number_format($nouveau_total, 2, ',', ' ') . ' €'
]);