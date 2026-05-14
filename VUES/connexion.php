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
    // Attendre que le DOM soit complètement chargé
    document.addEventListener('DOMContentLoaded', function() {
        
        // === CHANGEMENT DE THÈME ===
        const themeToggle = document.getElementById('theme-toggle');
        
        if (themeToggle) {
            const themeLink = document.createElement('link');
            themeLink.rel = 'stylesheet';
            themeLink.id = 'theme-stylesheet';
            document.head.appendChild(themeLink);

            // Charger le thème depuis le cookie
            function loadTheme() {
                const savedTheme = getCookie('theme');
                if (savedTheme === 'dark') {
                    themeLink.href = '../CSS/dark_theme.css';
                    themeToggle.textContent = '☀️';
                } else {
                    themeLink.href = '';
                    themeToggle.textContent = '🌙';
                }
            }

            // Changer le thème
            themeToggle.addEventListener('click', () => {
                const currentTheme = getCookie('theme');
                if (currentTheme === 'dark') {
                    setCookie('theme', 'light', 365);
                    themeLink.href = '';
                    themeToggle.textContent = '🌙';
                } else {
                    setCookie('theme', 'dark', 365);
                    themeLink.href = '../CSS/dark_theme.css';
                    themeToggle.textContent = '☀️';
                }
            });

            // Fonctions pour gérer les cookies
            function setCookie(name, value, days) {
                const d = new Date();
                d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
                document.cookie = name + "=" + value + ";expires=" + d.toUTCString() + ";path=/";
            }

            function getCookie(name) {
                const nameEQ = name + "=";
                const ca = document.cookie.split(';');
                for(let i = 0; i < ca.length; i++) {
                    let c = ca[i];
                    while (c.charAt(0) === ' ') c = c.substring(1, c.length);
                    if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
                }
                return null;
            }

            // Charger le thème au chargement de la page
            loadTheme();
        }

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
                
                // Validation email
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    e.preventDefault();
                    alert('❌ Veuillez entrer une adresse email valide.');
                    return false;
                }
                
                // Validation mot de passe
                if (password.length < 6) {
                    e.preventDefault();
                    alert('❌ Le mot de passe doit contenir au moins 6 caractères.');
                    return false;
                }
            });
        }
        
    }); // Fin du DOMContentLoaded
    </script>
</head>
<body>

    <!-- Bouton changement de thème -->
    <button id="theme-toggle" style="position: fixed; top: 20px; right: 20px; z-index: 9999; padding: 10px 15px; background: #ff6b35; color: white; border: none; border-radius: 50%; cursor: pointer; font-size: 1.2rem;" title="Changer le thème">
        🌙
    </button>

    <?php include '../LIB/header.php'; ?>

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