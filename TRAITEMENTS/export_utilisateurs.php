<?php
session_start();

// Vérifier que c'est un admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: connexion.php');
    exit;
}

// Charger les utilisateurs
$users = json_decode(file_get_contents('../DATA/users.json'), true);

// Nom du fichier
$filename = 'utilisateurs_' . date('Y-m-d_H-i-s') . '.csv';

// Headers pour forcer le téléchargement
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Ouvrir le flux de sortie
$output = fopen('php://output', 'w');

// En-têtes du CSV
fputcsv($output, ['ID', 'Nom', 'Prénom', 'Email', 'Rôle', 'Statut', 'Date inscription']);

// Données
foreach ($users as $user) {
    fputcsv($output, [
        $user['id'],
        $user['nom'],
        $user['prenom'],
        $user['email'],
        $user['role'],
        $user['statut'],
        $user['date_inscription'] ?? 'N/A'
    ]);
}

fclose($output);
exit;
?>