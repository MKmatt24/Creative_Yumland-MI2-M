<?php
session_start();
header('Content-Type: application/json');

//Vérification que la requête est bien en POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

//Vérification que l'utilisateur est un livreur connecté
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'livreur') {
    echo json_encode(['success' => false, 'message' => 'Non autorisé.']);
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
    ['nom' => 'Rice & Pollos Bowl', 'prix' => 13.90],
    ['nom' => 'Spicy Wrap', 'prix' => 10.50],
    ['nom' => 'Hermano Tenders', 'prix' => 9.90],
    ['nom' => 'Burrito Breakfast', 'prix' => 11.90],
];

//Choix aléatoire d'un client
$client = $clients[array_rand($clients)];

//Choix aléatoire de 1 à 4 articles
$nbArticles = mt_rand(1, 4);
$articlesChoisis = [];
$prixTotal = 0;
$indices = array_rand($plats, $nbArticles);
if (!is_array($indices)) $indices = [$indices];

foreach ($indices as $i) {
    $quantite = mt_rand(1, 3);
    $articlesChoisis[] = [
        'nom' => $plats[$i]['nom'],
        'prix' => $plats[$i]['prix'],
        'quantite' => $quantite
    ];
    $prixTotal += $plats[$i]['prix'] * $quantite;
}

//Récupération des commandes existantes pour générer un ID unique
$fichier = '../DATA/commande.json';
$commandes = json_decode(file_get_contents($fichier), true);

//Calcul du prochain ID
$maxId = 0;
foreach ($commandes as $cmd) {
    if ($cmd['id'] > $maxId) $maxId = $cmd['id'];
}

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
    'heure_retrait' => date('H:i', strtotime('+' . mt_rand(10, 30) . ' minutes')),
    'livreur_id' => null,
    'gain_livreur' => null,
    'statut_logistique' => 'en_preparation'
];

//Ajout de la commande et sauvegarde du fichier JSON
$commandes[] = $nouvelleCommande;
file_put_contents($fichier, json_encode($commandes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode([
    'success' => true,
    'message' => 'Commande #' . $nouvelleCommande['id'] . ' générée pour ' . $client['nom'],
    'commande' => $nouvelleCommande
]);