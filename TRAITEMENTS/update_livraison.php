<?php
session_start();
header('Content-Type: application/json');

//Vérification que la requête est bien en POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

//Récupération des données envoyées par le formulaire
$action = $_POST['action'] ?? null;
$idCommande = $_POST['id_commande'] ?? null;

//Vérification que toutes les données nécessaires sont présentes
if (!$action || !$idCommande) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes.']);
    exit;
}

//Vérification que le livreur est bien connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non authentifié.']);
    exit;
}

//Récupération des data du fichier JSON
$fichier = '../DATA/commande.json';
$commandes = json_decode(file_get_contents($fichier), true);

$found = false;
$gainLivreur = 0;

//Recherche de la commande et exécution de l'action demandée
foreach ($commandes as &$cmd) {
    if ($cmd['id'] == $idCommande) {
        $found = true;

        switch ($action) {
            //Accepter une course : le livreur prend la commande en charge
            case 'accepter':
                if (($cmd['statut'] ?? '') !== 'preparation') {
                    echo json_encode(['success' => false, 'message' => 'Cette commande n\'est plus disponible.']);
                    exit;
                }
                $cmd['statut'] = 'livraison';
                $cmd['statut_logistique'] = 'en_livraison';
                $cmd['livreur_id'] = $_SESSION['user_id'];
                break;

            //Terminer une course : on récupère le gain déjà calculé à la création
            case 'terminer':
                if (($cmd['livreur_id'] ?? '') != $_SESSION['user_id']) {
                    echo json_encode(['success' => false, 'message' => 'Cette commande ne vous est pas assignée.']);
                    exit;
                }
                $cmd['statut'] = 'livrée';
                $cmd['statut_logistique'] = 'livree';
                //Récupération du gain déjà stocké (ou calcul de secours si absent)
                $gainLivreur = floatval($cmd['gain_livreur'] ?? 0);
                if ($gainLivreur <= 0) {
                    $distanceKm = round(1.5 + mt_rand(0, 60) / 10, 1);
                    $gainLivreur = round(2.50 + ($distanceKm * 0.80), 2);
                    $cmd['gain_livreur'] = $gainLivreur;
                    $cmd['distance_km'] = $distanceKm;
                    $cmd['temps_minutes'] = round($distanceKm * 4);
                }
                break;

            //Abandonner une course : le livreur renonce à la livraison
            case 'abandonner':
                if (($cmd['livreur_id'] ?? '') != $_SESSION['user_id']) {
                    echo json_encode(['success' => false, 'message' => 'Cette commande ne vous est pas assignée.']);
                    exit;
                }
                $cmd['statut'] = 'abandonnée';
                $cmd['statut_logistique'] = 'abandonnee';
                break;

            default:
                echo json_encode(['success' => false, 'message' => 'Action inconnue.']);
                exit;
        }
        break;
    }
}

if (!$found) {
    echo json_encode(['success' => false, 'message' => 'Commande introuvable.']);
    exit;
}

//Sauvegarde du fichier JSON modifié
file_put_contents($fichier, json_encode($commandes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode([
    'success' => true,
    'message' => 'Statut mis à jour.',
    'action' => $action,
    'gain_livreur' => $gainLivreur
]);