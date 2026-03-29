<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer TOUTES les données du formulaire
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $adresse = $_POST['adresse'] ?? '';
    $code_interphone = $_POST['interphone'] ?? '';
    $code_postal = $_POST['code-postal'] ?? '';
    $ville = $_POST['ville'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm-password'] ?? '';
    
    // VALIDATION : Vérifier que les mots de passe correspondent
    if ($password !== $confirm_password) {
        header('Location: inscription.php?error=password_mismatch');
        exit;
    }
    
    // Charger les utilisateurs existants
    $users = json_decode(file_get_contents('../DATA/users.json'), true);
    
    // VALIDATION : Vérifier que l'email n'existe pas déjà
    foreach ($users as $user) {
        if ($user['email'] === $email) {
            header('Location: inscription.php?error=email_exists');
            exit;
        }
    }
    
    // Trouver le dernier ID pour auto-incrémenter correctement
    $maxId = 0;
    foreach ($users as $user) {
        if ($user['id'] > $maxId) {
            $maxId = $user['id'];
        }
    }
    
    // Créer nouvel utilisateur avec TOUS les champs
    $newUser = [
        'id' => $maxId + 1,
        'nom' => $nom,
        'prenom' => $prenom,
        'email' => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'telephone' => $telephone,
        'adresse' => $adresse,
        'code_interphone' => $code_interphone,
        'code_postal' => $code_postal,
        'ville' => $ville,
        'role' => 'client',
        'statut' => 'actif',
        'date_inscription' => date('Y-m-d')
    ];
    
    $users[] = $newUser;
    
    // Sauvegarder
    file_put_contents('../DATA/users.json', json_encode($users, JSON_PRETTY_PRINT));
    
    // Redirection vers connexion avec message de succès
    header('Location: connexion.php?success=1');
    exit;
}
?>