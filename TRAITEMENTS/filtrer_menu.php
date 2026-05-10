<?php
header('Content-Type: application/json');

$json_path = '../DATA/menu.json';
$data = file_exists($json_path) ? json_decode(file_get_contents($json_path), true) : [];
$plats = $data['plats'] ?? [];

// Récupération des filtres
$search = $_GET['search'] ?? '';
$cat = $_GET['categorie'] ?? 'Tous';
$diet = !empty($_GET['diet']) ? explode(',', $_GET['diet']) : [];
$taste = !empty($_GET['taste']) ? explode(',', $_GET['taste']) : [];

$resultats = array_filter($plats, function($p) use ($search, $cat, $diet, $taste) {
    // Filtre Catégorie
    if ($cat !== 'Tous' && $p['cat'] !== $cat) return false;

    // Filtre Recherche
    if (!empty($search) && stripos($p['nom'], $search) === false && stripos($p['desc'], $search) === false) return false;

    // Filtres Régime (Diet) - On vérifie dans les tags ou allergènes
    foreach ($diet as $d) {
        if ($d === 'sans_gluten' && !in_array('sans gluten', $p['tags'] ?? [])) return false;
        if ($d === 'vegetarien' && !in_array('végétarien', $p['tags'] ?? [])) return false;
        if ($d === 'vegan' && !in_array('vegan', $p['tags'] ?? [])) return false;
        if ($d === 'halal' && !in_array('halal', $p['tags'] ?? [])) return false;
    }

    // Filtres Goût (Taste)
    foreach ($taste as $t) {
        if (!in_array($t, $p['tags'] ?? [])) return false;
    }

    return true;
});

// On réindexe le tableau pour le JSON
echo json_encode([
    'success' => true,
    'plats' => array_values($resultats)
]);