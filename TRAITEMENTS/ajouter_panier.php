<?php
session_start();

// On vérifie que les données arrivent bien du formulaire
if (isset($_POST['nom'], $_POST['prix'])) {
    
    // Initialisation du panier s'il n'existe pas
    if (!isset($_SESSION['panier'])) {
        $_SESSION['panier'] = [];
    }

    $nom = trim($_POST['nom']);
    $prix = (float)$_POST['prix'];
    $quantite = isset($_POST['quantite']) ? (int)$_POST['quantite'] : 1;

    // Récupération de l'image associée au produit depuis le JSON pour persistance en session
    $json_content = file_get_contents('../DATA/menu.json');
    $menu_json = json_decode($json_content, true);
    $image = '../IMAGES/default.png'; // Fallback par défaut

    // Recherche dans les plats
    foreach ($menu_json['plats'] ?? [] as $p) {
        if ($p['nom'] === $nom) {
            $image = $p['image'] ?? '../IMAGES/default.png';
            break;
        }
    }
    // Si c'est une formule (Menu), on cherche dans la section menus ou on met le logo
    if ($image === '../IMAGES/default.png') {
        foreach ($menu_json['menus'] ?? [] as $m) {
            if ($m['nom'] === $nom) { $image = '../IMAGES/logo.png'; break; }
        }
    }

    $modifications = [];

    // --- LOGIQUE DU MENU ALÉATOIRE ---
    if ($nom === 'Menu Mystère') {
        $image = '../IMAGES/logo.png'; // Image spécifique pour le mystère
        $plats_dispo = $menu_json['plats'] ?? [];

        // On sépare par catégories pour un menu cohérent
        $entrees_plats = array_filter($plats_dispo, fn($p) => in_array($p['cat'], ['Poulet', 'Burgers', 'Spécialités']));
        $accompagnements = array_filter($plats_dispo, fn($p) => $p['cat'] === 'Accompagnements');
        $desserts = array_filter($plats_dispo, fn($p) => $p['cat'] === 'Desserts');

        // Sécurisation de la sélection aléatoire (évite les erreurs si une catégorie est vide)
        $sel_plat = !empty($entrees_plats) ? $entrees_plats[array_rand($entrees_plats)]['nom'] : 'Poulet croustillant';
        $sel_acc = !empty($accompagnements) ? $accompagnements[array_rand($accompagnements)]['nom'] : 'Frites';
        $sel_dessert = !empty($desserts) ? $desserts[array_rand($desserts)]['nom'] : 'Sopapillas';

        // Sélection aléatoire
        $selection = [
            'Plat' => $sel_plat,
            'Accompagnement' => $sel_acc,
            'Dessert' => $sel_dessert
        ];
        $modifications['composition_aleatoire'] = $selection;
    }
    
    // Récupération des ingrédients sélectionnés et substitutions
    if (isset($_POST['ingredients'])) {
        $modifications['ingredients'] = $_POST['ingredients'];
    }
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'sub_') === 0) {
            $modifications['substitutions'][] = $value;
        }
    }

    // Normalisation des modifications pour assurer un regroupement robuste
    // On trie les tableaux d'ingrédients et de substitutions pour que l'ordre n'influence pas la comparaison
    if (isset($modifications['ingredients']) && is_array($modifications['ingredients'])) {
        sort($modifications['ingredients']);
    }
    if (isset($modifications['substitutions']) && is_array($modifications['substitutions'])) {
        sort($modifications['substitutions']);
    }
    // On trie les clés du tableau global pour une comparaison stricte (===) fiable entre tableaux PHP
    ksort($modifications);

    // Est-ce que le produit est déjà dans le panier ?
    $trouve = false;
    foreach ($_SESSION['panier'] as &$item) {
        if ($item['nom'] === $nom && ($item['modifications'] ?? []) === $modifications) {
            $item['quantite'] += $quantite;
            $trouve = true;
            break;
        }
    }
    unset($item); // Détruit la référence pour éviter d'écraser le dernier élément dans les boucles suivantes

    // Si c'est un nouveau produit, on l'ajoute
    if (!$trouve) {
        $_SESSION['panier'][] = [
            'nom' => $nom,
            'prix' => $prix,
            'image' => $image,
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
$isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');
if ($isAjax) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'new_count' => $cart_count]);
    exit();
}

// Redirection vers le menu pour voir le compteur s'actualiser
header('Location: ../VUES/menu.php');
exit();
