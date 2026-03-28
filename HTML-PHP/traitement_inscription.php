<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Charger les utilisateurs existants
    $users = json_decode(file_get_contents('../DATA/users.json'), true);
    
    // Créer nouvel utilisateur
    $newUser = [
        'id' => count($users) + 1,
        'nom' => $nom,
        'prenom' => $prenom,
        'email' => $email,
        'password' => $password,
        'role' => 'client',
        'statut' => 'actif',
        'date_inscription' => date('Y-m-d')
    ];
    
    $users[] = $newUser;
    
    // Sauvegarder
    file_put_contents('../DATA/users.json', json_encode($users, JSON_PRETTY_PRINT));
    
    // Redirection
    header('Location: connexion.php?success=1');
    exit;
}
?>