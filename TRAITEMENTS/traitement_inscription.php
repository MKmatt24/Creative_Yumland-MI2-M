<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer TOUTES les données du formulaire
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $adresse = $_POST['adresse'] ?? '';
    $code_interphone = $_POST['interphone'] ?? '';
    $etage = $_POST['etage'] ?? '';
    $appartement = $_POST['appartement'] ?? '';
    $code_postal = $_POST['code-postal'] ?? '';
    $ville = $_POST['ville'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm-password'] ?? '';
    
    // FONCTION DE REDIRECTION EN CAS D'ERREUR
    function redirect_error($error_code) {
        $_SESSION['form_data'] = $_POST;
        unset($_SESSION['form_data']['password']);
        unset($_SESSION['form_data']['confirm-password']);
        header('Location: ../VUES/inscription.php?error=' . $error_code);
        exit;
    }

    // VALIDATION : Nom et Prénom (lettres uniquement)
    if (!preg_match("/^[a-zA-ZÀ-ÿ\s\-']+$/", $nom) || !preg_match("/^[a-zA-ZÀ-ÿ\s\-']+$/", $prenom)) {
        redirect_error('invalid_name');
    }

    // VALIDATION : Email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirect_error('invalid_email');
    }

    // VALIDATION : Téléphone (format français)
    $tel_cleaned = preg_replace('/[\s.\-]/', '', $telephone);
    if (!preg_match('/^(0|\+33)[1-9]\d{8}$/', $tel_cleaned)) {
        redirect_error('invalid_phone');
    }

    // VALIDATION : Code Postal (5 chiffres)
    if (!preg_match('/^\d{5}$/', $code_postal)) {
        redirect_error('invalid_zip');
    }

    // VALIDATION : Ville (lettres uniquement)
    if (!preg_match("/^[a-zA-ZÀ-ÿ\s\-']+$/", $ville)) {
        redirect_error('invalid_city');
    }
    
    // VALIDATION : Vérifier que les mots de passe correspondent
    if ($password !== $confirm_password) {
        redirect_error('password_mismatch');
    }
    
    // VALIDATION : Minimum 8 caractères
    if (strlen($password) < 8) {
        redirect_error('password_too_short');
    }
    
    // Charger les utilisateurs existants
    $users = json_decode(file_get_contents('../DATA/users.json'), true);
    
    // VALIDATION : Vérifier que l'email n'existe pas déjà
    foreach ($users as $user) {
        if ($user['email'] === $email) {
            redirect_error('email_exists');
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
        'etage' => $etage,
        'appartement' => $appartement,
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
    header('Location: ../VUES/connexion.php?success=1');
    exit;
}
?>