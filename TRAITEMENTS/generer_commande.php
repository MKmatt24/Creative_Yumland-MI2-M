<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

//Vérification que l'utilisateur est un livreur connecté
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'livreur') {
    echo json_encode(['success' => false, 'message' => 'Non autorisé. Role: ' . ($_SESSION['role'] ?? 'aucun')]);
    exit;
}

//Liste de clients fictifs pour la simulation
$clients = [
    ['nom' => 'Walter White', 'user_id' => 3, 'adresse' => '308 Negra Arroyo Lane, Albuquerque'],
    ['nom' => 'Jesse Pinkman', 'user_id' => 4, 'adresse' => '9809 Margo Street, Albuquerque'],
    ['nom' => 'Saul Goodman', 'user_id' => 5, 'adresse' => '160 Montgomery Blvd, Albuquerque'],
    ['nom' => 'Hank Schrader', 'user_id' => 6, 'adresse' => '4901 Cumbre Del Sur, Albuquerque'],
    ['nom' => 'Mike Ehrmantraut', 'user_id' => 7, 'adresse' => '204 Edith Blvd, Albuquerque'],
];

//Liste de plats du menu pour générer des articles aléatoires
$plats = [
    ['nom' => 'Le Seau Albuquerque', 'prix' => 19.99],
    ['nom' => 'Ailes Fuego', 'prix' => 9.50],
    ['nom' => 'Pollos Burger', 'prix' => 12.90],
    ['nom' => 'Quesadilla XL', 'prix' => 11.50],
    ['nom' => 'Nachos de la Loma', 'prix' => 8.90],
    ['nom' => 'Frites Maison', 'prix' => 4.50],
    ['nom' => 'Rice Pollos Bowl', 'prix' => 13.90],
    ['nom' => 'Spicy Wrap', 'prix' => 10.50],
    ['nom' => 'Hermano Tenders', 'prix' => 9.90],
    ['nom' => 'Burrito Breakfast', 'prix' => 11.90],
];

//Choix aléatoire d'un client
$client = $clients[array_rand($clients)];

//Choix aléatoire de 1 à 3 articles
$nbArticles = mt_rand(1, 3);
$articlesChoisis = [];
$prixTotal = 0;

for ($i = 0; $i < $nbArticles; $i++) {
    $plat = $plats[array_rand($plats)];
    $quantite = mt_rand(1, 2);
    $articlesChoisis[] = [
        'nom' => $plat['nom'],
        'prix' => $plat['prix'],
        'quantite' => $quantite
    ];
    $prixTotal += $plat['prix'] * $quantite;
}

//Récupération des commandes existantes pour générer un ID unique
$fichier = __DIR__ . '/../DATA/commande.json';
$contenu = file_get_contents($fichier);
$commandes = json_decode($contenu, true);
if (!is_array($commandes)) {
    $commandes = [];
}

//Calcul du prochain ID
$maxId = 0;
foreach ($commandes as $cmd) {
    if (isset($cmd['id']) && intval($cmd['id']) > $maxId) {
        $maxId = intval($cmd['id']);
    }
}

//Calcul de la distance et du gain dès la création (pour cohérence entre les pages)
$distanceKm = round(1.5 + mt_rand(0, 60) / 10, 1);
$gainLivreur = round(2.50 + ($distanceKm * 0.80), 2);
$tempsMinutes = round($distanceKm * 4);

//Création de la nouvelle commande
$nouvelleCommande = [
    'id' => $maxId + 1,
    'client' => $client['nom'],
    'user_id' => $client['user_id'],
    'type' => 'Livraison',
    'articles' => $articlesChoisis,
    'prix_total' => round($prixTotal, 2),
    'statut' => 'preparation',
    'adresse' => $client['adresse'],
    'date' => date('Y-m-d H:i'),
    'heure_retrait' => date('H:i', strtotime('+15 minutes')),
    'livreur_id' => null,
    'gain_livreur' => $gainLivreur,
    'distance_km' => $distanceKm,
    'temps_minutes' => $tempsMinutes,
    'statut_logistique' => 'en_preparation'
];

//Ajout de la commande et sauvegarde du fichier JSON
$commandes[] = $nouvelleCommande;
$resultat = file_put_contents($fichier, json_encode($commandes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

if ($resultat === false) {
    echo json_encode(['success' => false, 'message' => 'Erreur ecriture fichier.']);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'Commande #' . $nouvelleCommande['id'] . ' generee pour ' . $client['nom']
]);