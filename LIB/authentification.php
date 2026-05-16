<?php
//Initialisation de la session
session_start();

//Vérification si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit();
}

// Vérification du statut : expulsion si le compte est banni entre-temps
$users_db = json_decode(file_get_contents(__DIR__ . '/../DATA/users.json'), true);
foreach ($users_db as $u) {
    if ($u['id'] == $_SESSION['user_id'] && ($u['statut'] === 'suspendu' || $u['statut'] === 'inactif')) {
        session_destroy();
        header('Location: connexion.php?error=account_disabled');
        exit();
    }
}
?>
