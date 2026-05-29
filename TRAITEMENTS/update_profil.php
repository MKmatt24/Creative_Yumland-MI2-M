<?php
include __DIR__ . '/../LIB/authentification.php';
header('Content-Type: application/json');

//Vérification que la requête est bien en POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

//Vérification du token CSRF
verifier_csrf();

//Récupération des données envoyées par le formulaire
$userId = $_POST['user_id'] ?? null;
$champ = $_POST['champ'] ?? null;
$valeur = $_POST['valeur'] ?? null;

//Vérification que toutes les données nécessaires sont présentes
if (!$userId || !$champ || $valeur === null) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes.']);
    exit;
}

//Vérification que l'utilisateur connecté est bien celui qui modifie son profil
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $userId) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé.']);
    exit;
}

//Nettoyage de la valeur contre les injections XSS
$valeur = trim($valeur);
if ($champ !== 'date_naissance' && $champ !== 'objectif_jour') {
    $valeur = strip_tags($valeur);
}

//Liste des champs que l'utilisateur a le droit de modifier
$champsAutorises = ['nom_complet', 'email', 'telephone', 'date_naissance', 'adresse', 'objectif_jour'];
if (!in_array($champ, $champsAutorises)) {
    echo json_encode(['success' => false, 'message' => 'Champ non modifiable.']);
    exit;
}

//Récupération des data du fichier JSON
$file = '../DATA/users.json';
$users = json_decode(file_get_contents($file), true);

//Recherche de l'utilisateur et modification du champ demandé
$found = false;
foreach ($users as &$user) {
    if ($user['id'] == $userId) {
        $found = true;

        switch ($champ) {
            //Nom complet : on sépare en prénom et nom
            case 'nom_complet':
                $parts = explode(' ', trim($valeur), 2);
                $user['prenom'] = $parts[0];
                $user['nom'] = $parts[1] ?? '';
                break;

            //Email : validation du format + vérification qu'il n'est pas déjà pris
            case 'email':
                if (!filter_var($valeur, FILTER_VALIDATE_EMAIL)) {
                    echo json_encode(['success' => false, 'message' => 'Email invalide.']);
                    exit;
                }
                foreach ($users as $other) {
                    if ($other['id'] != $userId && strtolower($other['email']) === strtolower($valeur)) {
                        echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé.']);
                        exit;
                    }
                }
                $user['email'] = $valeur;
                break;

            //Téléphone : validation du format français
            case 'telephone':
                $cleaned = preg_replace('/[\s.\-]/', '', $valeur);
                if (!preg_match('/^(0|\+33)[1-9]\d{8}$/', $cleaned)) {
                    echo json_encode(['success' => false, 'message' => 'Numéro invalide.']);
                    exit;
                }
                $user['telephone'] = $valeur;
                break;

            //Date de naissance : validation du format
            case 'date_naissance':
                $date = DateTime::createFromFormat('Y-m-d', $valeur);
                if (!$date) {
                    echo json_encode(['success' => false, 'message' => 'Date invalide.']);
                    exit;
                }
                $user['date_naissance'] = $valeur;
                break;

            //Adresse : pas de validation spécifique
            case 'adresse':
                $user['adresse'] = $valeur;
                break;

            //Objectif du jour : validation entre 10 et 500€
            case 'objectif_jour':
                $obj = floatval($valeur);
                if ($obj < 10 || $obj > 500) {
                    echo json_encode(['success' => false, 'message' => 'Objectif entre 10 et 500 €.']);
                    exit;
                }
                $user['objectif_jour'] = $obj;
                break;
        }
        break;
    }
}

if (!$found) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur introuvable.']);
    exit;
}

//Sauvegarde du fichier JSON modifié
file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode(['success' => true, 'message' => 'Profil mis à jour.']);
