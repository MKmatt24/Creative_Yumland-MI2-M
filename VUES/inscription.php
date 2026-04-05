<?php
    $error_message = '';
    $success_message = '';

    if (isset($_GET['error'])) {
        switch ($_GET['error']) {
            case 'password_mismatch':
                $error_message = 'Les mots de passe ne correspondent pas.';
                break;
            case 'email_exists':
                $error_message = 'Cette adresse email est déjà utilisée.';
                break;
        }
    }

    if (isset($_GET['success'])) {
        $success_message = 'Inscription réussie ! Vous pouvez maintenant vous connecter.';
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S'inscrire | Los Pollos Hermanos</title>
    <link rel="icon" type="image/png" href="../Images/logo.svg">
    <link rel="stylesheet" href="../CSS/inscription.css">
</head>
<body>

    <?php include '../LIB/header.php'; ?>

    <main>
        <section class="inscription-section">
            <div class="inscription-container">
                <h2>Créer un compte</h2>

                <?php if ($error_message): ?>
                    <div style="background-color: #ff4444; color: white; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                        <?= htmlspecialchars($error_message) ?>
                    </div>
                <?php endif; ?>

                <?php if ($success_message): ?>
                    <div style="background-color: #44ff44; color: #1a1a1a; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                        <?= htmlspecialchars($success_message) ?>
                    </div>
                <?php endif; ?>

                <p>Rejoignez Los Pollos Hermanos et profitez d'offres exclusives</p>
                
                <form action="../TRAITEMENTS/traitement_inscription.php" method="post">
                    <div class="form-group">
                        <label for="nom">Nom</label>
                        <input type="text" id="nom" name="nom" required placeholder="Votre nom">
                    </div>

                    <div class="form-group">
                        <label for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom" required placeholder="Votre prénom">
                    </div>

                    <div class="form-group">
                        <label for="email">Adresse email</label>
                        <input type="email" id="email" name="email" required placeholder="exemple@email.com">
                    </div>

                    <div class="form-group">
                        <label for="telephone">Numéro de téléphone</label>
                        <input type="tel" id="telephone" name="telephone" required placeholder="06 12 34 56 78">
                    </div>

                    <div class="form-group">
                        <label for="adresse">Adresse</label>
                        <input type="text" id="adresse" name="adresse" required placeholder="Numéro et nom de rue">
                    </div>

                    <div class="form-group">
                        <label for="interphone">Code interphone / Étage</label>
                        <input type="text" id="interphone" name="interphone" placeholder="Ex: B32, 4ème étage">
                    </div>

                    <div class="form-group">
                        <label for="code-postal">Code postal</label>
                        <input type="text" id="code-postal" name="code-postal" required placeholder="75000">
                    </div>

                    <div class="form-group">
                        <label for="ville">Ville</label>
                        <input type="text" id="ville" name="ville" required placeholder="Albuquerque">
                    </div>

                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" required placeholder="Minimum 8 caractères">
                    </div>

                    <div class="form-group">
                        <label for="confirm-password">Confirmer le mot de passe</label>
                        <input type="password" id="confirm-password" name="confirm-password" required placeholder="Retapez votre mot de passe">
                    </div>

                    <div class="form-group">
                        <input type="checkbox" id="newsletter" name="newsletter">
                        <label for="newsletter">Je souhaite recevoir les offres et actualités par email</label>
                    </div>

                    <div class="form-group">
                        <input type="checkbox" id="cgu" name="cgu" required>
                        <label for="cgu">J'accepte les conditions générales d'utilisation</label>
                    </div>

                    <div class="form-group">
                        <button type="submit">S'inscrire</button>
                    </div>
                </form>

                <div class="connexion-link">
                    <p>Vous avez déjà un compte ? <a href="connexion.php">Connectez-vous ici</a></p>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>À propos</h3>
                <p>Los Pollos Hermanos - Taste the Family</p>
            </div>
            <div class="footer-section">
                <h3>Nous contacter</h3>
                <p>Email : contact@lospolloshermanos.fr</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025-2026 Los Pollos Hermanos. Tous droits réservés.</p>
        </div>
    </footer>
</body>
</html>
