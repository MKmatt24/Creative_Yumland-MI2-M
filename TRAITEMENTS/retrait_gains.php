<?php
session_start();
header('Content-Type: application/json');

//Vérification que la requête est bien en POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

//Vérification que le livreur est bien connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non authentifié.']);
    exit;
}

//Récupération du montant envoyé depuis le formulaire
$montant = floatval($_POST['montant'] ?? 0);

if ($montant <= 0) {
    echo json_encode(['success' => false, 'message' => 'Aucun montant à retirer.']);
    exit;
}

//Récupération des data du fichier JSON
$fichier = '../DATA/commande.json';
$commandes = json_decode(file_get_contents($fichier), true);

$totalRetire = 0;

//Parcours des commandes du livreur pour mettre les gains à 0 et marquer comme retiré
foreach ($commandes as &$cmd) {
    if (($cmd['livreur_id'] ?? '') == $_SESSION['user_id']) {
        $statut = strtolower($cmd['statut'] ?? '');
        if (($statut === 'livrée' || $statut === 'livree') && isset($cmd['gain_livreur']) && floatval($cmd['gain_livreur']) > 0) {
            $totalRetire += floatval($cmd['gain_livreur']);
            $cmd['gain_retire'] = true;
            $cmd['gain_livreur'] = 0;
        }
    }
}

//Vérification qu'il y avait bien des gains à retirer
if ($totalRetire <= 0) {
    echo json_encode(['success' => false, 'message' => 'Aucun gain disponible.']);
    exit;
}

//Sauvegarde du fichier JSON modifié
file_put_contents($fichier, json_encode($commandes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode([
    'success' => true,
    'message' => 'Retrait effectué.',
    'montant_retire' => $totalRetire
]);
