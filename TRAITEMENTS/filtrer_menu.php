<?php
header('Content-Type: application/json');

$json_path = '../DATA/menu.json';
$data = file_exists($json_path) ? json_decode(file_get_contents($json_path), true) : [];
$plats = $data['plats'] ?? [];
$menus = $data['menus'] ?? [];

// Create a dummy "Menu Mystère" entry and add it to the menus array
$mystery_menu = [
    "id" => "mystery_menu_item", // Unique ID to identify it later in JS
    "nom" => "Le Menu Mystère",
    "description" => "Faites confiance à l'instinct de Gustavo. Un plat, un accompagnement et un dessert choisis au hasard parmi nos meilleures recettes.",
    "prix" => 12.50,
    "min_personnes" => 1,
    "creneau" => "Toute la journée",
    "heure_debut" => "00:00",
    "heure_fin" => "23:59",
    "liste_plats" => ["Plat aléatoire", "Accompagnement aléatoire", "Dessert aléatoire"], // Placeholder for display
    "is_mystery_menu" => true // Custom flag for JS rendering
];

// Add it to the menus array before filtering
$menus[] = $mystery_menu;

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

// Filtre pour les menus (uniquement si aucun filtre de régime/goût spécifique n'est actif)
$resultats_menus = [];
if (empty($diet) && empty($taste)) {
    $resultats_menus = array_filter($menus, function($m) use ($search, $cat) {
        if ($cat !== 'Tous') return false; // Les menus ne sont affichés que dans la vue globale
        if (empty($search)) return true;
        
        $foundInPlats = false;
        foreach(($m['liste_plats'] ?? []) as $platNom) {
            if (stripos($platNom, $search) !== false) { $foundInPlats = true; break; }
        }
        
        return stripos($m['nom'], $search) !== false || stripos($m['description'], $search) !== false || $foundInPlats;
    });
}

// On réindexe le tableau pour le JSON
echo json_encode([
    'success' => true,
    'plats' => array_values($resultats),
    'menus' => array_values($resultats_menus)
]);