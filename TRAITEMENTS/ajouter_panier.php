<?php
session_start();

// On vérifie que les données arrivent bien du formulaire
if (isset($_POST['nom'], $_POST['prix'])) {
    
    // Initialisation du panier s'il n'existe pas
    if (!isset($_SESSION['panier'])) {
        $_SESSION['panier'] = [];
    }

    $nom = $_POST['nom'];
    $prix = (float)$_POST['prix'];
    $quantite = isset($_POST['quantite']) ? (int)$_POST['quantite'] : 1;
    
    // Récupération des ingrédients sélectionnés et substitutions
    $modifications = [];
    if (isset($_POST['ingredients'])) {
        $modifications['ingredients'] = $_POST['ingredients'];
    }
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'sub_') === 0) {
            $modifications['substitutions'][] = $value;
        }
    }

    // Est-ce que le produit est déjà dans le panier ?
    $trouve = false;
    foreach ($_SESSION['panier'] as &$item) {
        if ($item['nom'] === $nom && ($item['modifications'] ?? []) === $modifications) {
            $item['quantite'] += $quantite;
            $trouve = true;
            break;
        }
    }

    // Si c'est un nouveau produit, on l'ajoute
    if (!$trouve) {
        $_SESSION['panier'][] = [
            'nom' => $nom,
            'prix' => $prix,
            'quantite' => $quantite,
            'modifications' => $modifications
        ];
    }
}

// Calcul du nouveau total pour la réponse AJAX
$cart_count = 0;
if (isset($_SESSION['panier'])) {
    foreach ($_SESSION['panier'] as $item) {
        $cart_count += $item['quantite'];
    }
}

// Si c'est une requête AJAX, on renvoie du JSON et on arrête le script
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'new_count' => $cart_count]);
    exit();
}

// Redirection vers le menu pour voir le compteur s'actualiser
header('Location: ../VUES/menu.php');
exit();
