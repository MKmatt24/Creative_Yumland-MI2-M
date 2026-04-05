<?php
session_start();

// Initialiser la variable d'erreur
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Charger les utilisateurs
    $users = json_decode(file_get_contents('../DATA/users.json'), true);
    
    $login_successful = false;
    
    foreach ($users as $user) {
        if ($user['email'] === $email && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['nom'] = $user['nom'];
            $_SESSION['prenom'] = $user['prenom'];
            
            $login_successful = true;
            
            // Redirection selon le rôle
            if ($user['role'] === 'admin') {
                header('Location: admin.php');
            } else {
                header('Location: profil.php');
            }
            exit;
        }
    }
    
    // Si on arrive ici, la connexion a échoué
    if (!$login_successful) {
        $error = "Email ou mot de passe incorrect";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | Los Pollos Hermanos</title>
    <link rel="icon" type="image/png" href="../Images/logo.svg">
    <link rel="stylesheet" href="../CSS/connexion.css">
</head>
<body>

    <?php include '../LIB/header.php'; ?>

    <main>
        <section class="connexion-section">
            <div class="connexion-container">
                <h2>Connexion</h2>
                <p>Connectez-vous pour accéder à votre compte</p>
                
                <?php if (!empty($error)): ?>
                    <div style="background-color: rgba(255, 68, 68, 0.2); border: 2px solid #ff4444; color: #ff6b35; padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; text-align: center;">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['success'])): ?>
                    <div style="background-color: rgba(68, 255, 68, 0.2); border: 2px solid #44ff44; color: #44ff44; padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; text-align: center;">
                        Inscription réussie ! Vous pouvez maintenant vous connecter.
                    </div>
                <?php endif; ?>
                
                <form action="" method="post">
                    <div class="form-group">
                        <label for="email">Adresse email</label>
                        <input type="email" id="email" name="email" required placeholder="exemple@email.com" value="<?= htmlspecialchars($email ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" required placeholder="Votre mot de passe">
                    </div>

                    <div class="form-group checkbox-group">
                        <label>
                            <input type="checkbox" id="remember" name="remember">
                            <span>Se souvenir de moi</span>
                        </label>
                    </div>

                    <div class="form-group">
                        <button type="submit">Se connecter</button>
                    </div>
                </form>

                <div class="inscription-link">
                    <p>Vous n'avez pas de compte ? <a href="inscription.php">Inscrivez-vous ici</a></p>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>À propos</h3>
                <p>Los Pollos Hermanos - Le meilleur poulet frit en ville</p>
            </div>
            <div class="footer-section">
                <h3>Nous contacter</h3>
                <p>Email : contact@<span class="easter-egg" onclick="window.open('easteregg.html', '_blank')">chickenparadise</span>.fr</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025-2026 Los Pollos Hermanos. Tous droits réservés.</p>
        </div>
    </footer>
</body>
</html>