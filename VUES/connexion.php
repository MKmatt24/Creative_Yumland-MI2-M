<?php
session_start();

// Initialiser la variable d'erreur
$error = '';

// Vérifier si l'utilisateur est redirigé ici suite à un bannissement
if (isset($_GET['error']) && $_GET['error'] === 'account_disabled') {
    $error = "Votre compte a été suspendu par l'administrateur. Session terminée.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Charger les utilisateurs
    $users = json_decode(file_get_contents('../DATA/users.json'), true);
    
    $login_successful = false;
    
    foreach ($users as $user) {
        if ($user['email'] === $email && password_verify($password, $user['password'])) {
            // VÉRIFICATION DU STATUT
            if ($user['statut'] === 'suspendu' || $user['statut'] === 'inactif') {
                $error = "Ce compte est suspendu. Accès refusé.";
                break;
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['nom'] = $user['nom'];
            $_SESSION['prenom'] = $user['prenom'];
            
            $login_successful = true;
            
            // Redirection selon le rôle
            if ($user['role'] === 'admin') {
                header('Location: admin.php');
            } elseif ($user['role'] === 'restaurateur') {
                header('Location: commande.php');
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
    <link rel="shortcut icon" type="image/png" href="../IMAGES/logo.png">
    <link rel="icon" type="image/png" href="../IMAGES/logo.png">
    <link rel="stylesheet" href="../CSS/connexion.css">

    <script>

    // --- 2. INITIALISATION (Une fois que la page est chargée) ---
    document.addEventListener('DOMContentLoaded', function() {
        

        // === AFFICHER/MASQUER MOT DE PASSE ===
        const passwordInput = document.getElementById('password');
        if (passwordInput) {
            const passwordGroup = passwordInput.parentElement;
            passwordGroup.style.position = 'relative';
            
            const togglePassword = document.createElement('span');
            togglePassword.innerHTML = '👁️';
            togglePassword.style.cssText = 'position: absolute; right: 15px; top: 38px; cursor: pointer; font-size: 1.3rem; user-select: none; z-index: 10;';
            
            passwordGroup.appendChild(togglePassword);

            togglePassword.addEventListener('click', () => {
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    togglePassword.innerHTML = '🙈';
                } else {
                    passwordInput.type = 'password';
                    togglePassword.innerHTML = '👁️';
                }
            });
        }

        // === VALIDATION DU FORMULAIRE ===
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', (e) => {
                const email = document.getElementById('email').value;
                const password = document.getElementById('password').value;
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                
                if (!emailRegex.test(email)) {
                    e.preventDefault();
                    alert('❌ Veuillez entrer une adresse email valide.');
                } else if (password.length < 6) {
                    e.preventDefault();
                    alert('❌ Le mot de passe doit contenir au moins 6 caractères.');
                }
            });
        }

        //COMPTEUR DE CARACTÈRES
        const pwdInput = document.getElementById('password');
        if (pwdInput) {
            const counter = document.createElement('div');
            counter.style.fontSize = '0.8rem';
            counter.style.marginTop = '5px';
            counter.style.textAlign = 'right';
            pwdInput.parentElement.appendChild(counter);

            const updateCounter = () => {
                counter.textContent = `${pwdInput.value.length} / 8`;
                counter.style.color = pwdInput.value.length >= 8 ? 'var(--orange)' : '#888';
            };
            pwdInput.addEventListener('input', updateCounter);
            updateCounter();
        }
    });
</script>
</head>
<body>
    
    <?php include '../LIB/header.php'; ?>

    <div class="theme-switcher-container">
        <button onclick="cycleTheme()" id="theme-toggle-btn" class="theme-switcher-btn">
            🎨 Changer de style
        </button>
    </div>



    <main>
        <section class="connexion-section">
            <div class="connexion-container">
                <h2>Connexion</h2>
                <p>Connectez-vous pour accéder à votre compte</p>
                
                <?php if (!empty($error)): ?>
                    <div class="error-message">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['success'])): ?>
                    <div class="success-message">
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
                        <input type="password" id="password" name="password" required placeholder="Votre mot de passe" maxlength="8">
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