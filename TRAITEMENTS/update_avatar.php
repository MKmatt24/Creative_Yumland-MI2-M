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

//Vérification que l'utilisateur est connecté
$userId = $_POST['user_id'] ?? null;
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $userId) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé.']);
    exit;
}

//Vérification qu'un fichier a bien été envoyé
if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Aucun fichier reçu.']);
    exit;
}

$fichier = $_FILES['avatar'];

//Vérification du type MIME (uniquement des images)
$typesAutorises = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($fichier['type'], $typesAutorises)) {
    echo json_encode(['success' => false, 'message' => 'Format non autorisé. Utilisez JPG, PNG, GIF ou WEBP.']);
    exit;
}

//Vérification de la taille (max 2 Mo)
if ($fichier['size'] > 2 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'L\'image ne doit pas dépasser 2 Mo.']);
    exit;
}

//Génération d'un nom unique pour éviter les conflits
$extension = pathinfo($fichier['name'], PATHINFO_EXTENSION);
$nomFichier = 'avatar_' . $userId . '_' . time() . '.' . $extension;
$dossierDestination = '../IMAGES/avatars/';
$cheminComplet = $dossierDestination . $nomFichier;

//Déplacement du fichier uploadé vers le dossier avatars
if (!move_uploaded_file($fichier['tmp_name'], $cheminComplet)) {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement.']);
    exit;
}

//Mise à jour du chemin avatar dans le fichier users.json
$file = '../DATA/users.json';
$users = json_decode(file_get_contents($file), true);

foreach ($users as &$user) {
    if ($user['id'] == $userId) {
        //Suppression de l'ancien avatar s'il existe (sauf l'avatar par défaut)
        if (isset($user['avatar']) && file_exists($user['avatar']) && strpos($user['avatar'], 'avatar_anonyme') === false) {
            unlink($user['avatar']);
        }
        $user['avatar'] = $cheminComplet;
        break;
    }
}

//Sauvegarde du fichier JSON modifié
file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode(['success' => true, 'message' => 'Avatar mis à jour.', 'chemin' => $cheminComplet]);