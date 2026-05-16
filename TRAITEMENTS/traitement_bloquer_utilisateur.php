<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Accès refusé']);
    exit;
}

$id = $_POST['id'] ?? null;
$nouveauStatut = $_POST['statut'] ?? null;

if ($id && $nouveauStatut) {
    $file = '../DATA/users.json';
    $users = json_decode(file_get_contents($file), true);
    
    foreach ($users as &$user) {
        if ($user['id'] == $id) {
            $user['statut'] = $nouveauStatut;
            file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT));
            echo json_encode(['success' => true]);
            exit;
        }
    }
}

echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé']);
?>