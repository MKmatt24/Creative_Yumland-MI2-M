<?php
session_start();

//Vérification si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit();
}

//Récupération des data du fichier JSON
$fichierCommandes = '../DATA/commande.json';
$commandesData = file_get_contents($fichierCommandes);
$commandes = json_decode($commandesData, true);

//Quand le livreur clique sur le bouton pour "finir" la commande
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id_commande'])) {
    $idCible = $_POST['id_commande'];
    $nouveauStatut = ($_POST['action'] === 'terminer') ? 'livrée' : 'abandonnée';

//Parcours des commandes pour modifier celle souhaité
    foreach ($commandes as &$cmd) {
        if ($cmd['id'] == $idCible) {
            $cmd['statut'] = $nouveauStatut;
            break;
        }
    }

//Sauvegarde du fichier JSON modifié et rafraichissement de la page pour vider le formulaire
    file_put_contents($fichierCommandes, json_encode($commandes, JSON_PRETTY_PRINT));
    header('Location: livraison.php');
    exit();
}

//Affichage de la commande en cours
$commandeEnCours = null;

foreach ($commandes as $cmd) {
    if ($cmd['statut'] == 'livraison' && $cmd['livreur_id'] == $_SESSION['user_id']) {
        $commandeEnCours = $cmd;
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Livraison en cours - LOS POLLOS HERMANOS</title>
    <link rel="icon" type="image/png" href="../IMAGES/logo.png">
    <link rel="stylesheet" href="../CSS/livraison.css">
</head>
<body>

    <header>
        <nav>
            <div class="logo">
                <div class="logo-box">
                    <a href="accueil.php">
                    <img src="../IMAGES/logo.png" alt="Logo Los Pollos" class="nav-logo">
                    </a>
                </div>
            </div>
            <button class="menu-toggle" aria-label="Toggle menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <ul>
                <li><a href="accueil.php">Accueil</a></li>
                <li><a href="menu.php">Menu</a></li>
                <li><a href="accueil.php#contact">Contact</a></li>
                <li><a href="Livraisons_en_attente.php">Livraisons en attente</a></li>
                <li><a href="profil.php">Mon Profil</a></li>
                <li><a href="Rewards.php">Mes Rémunérations</a></li>
                <li><a href="../TRAITEMENTS/deconnexion.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>

<main>
        <?php if ($commandeEnCours): ?>
            <section class="delivery-section" id="zone-livraison">
                
                <div class="delivery-header">
                    <h2>Commande #<?= htmlspecialchars($commandeEnCours['id']) ?></h2>
                    <span class="status-badge">En cours de livraison 🛵</span>
                </div>

                <div class="delivery-container">
                    <div class="customer-block">
                        <div class="customer-info">
                            <h3><?= htmlspecialchars($commandeEnCours['client']) ?></h3>
                        </div>
                        <a href="tel:0000000000" class="phone-btn" aria-label="Appeler le client">📞</a>
                    </div>

                    <hr class="divider">

                    <div class="address-block">
                        <label>📍 Adresse de livraison</label>
                        <p class="address-text"><?= htmlspecialchars($commandeEnCours['adresse']) ?></p>
                        <a href="https://maps.google.com/?q=<?= urlencode($commandeEnCours['adresse']) ?>" target="_blank" class="gps-btn">
                            🗺️ Ouvrir le GPS
                        </a>
                    </div>

                    <div class="access-grid">
                        <div class="access-item">
                            <span class="icon">🔢</span>
                            <span class="label">Digicode</span>
                            <span class="value"><?= htmlspecialchars($commandeEnCours['digicode'] ?? 'N/A') ?></span>
                        </div>
                        <div class="access-item">
                            <span class="icon">🏢</span>
                            <span class="label">Étage</span>
                            <span class="value"><?= htmlspecialchars($commandeEnCours['etage'] ?? 'N/A') ?></span>
                        </div>
                        <div class="access-item">
                            <span class="icon">🚪</span>
                            <span class="label">Appartement</span>
                            <span class="value"><?= htmlspecialchars($commandeEnCours['appartement'] ?? 'N/A') ?></span>
                        </div>
                    </div>

                    <div class="comment-block">
                        <label>💬 Commentaire client</label>
                        <div class="comment-box">
                            <?= htmlspecialchars($commandeEnCours['commentaire'] ?? 'Aucun commentaire laissé par le client.') ?>
                        </div>
                    </div>

                    <form method="POST" action="livraison.php" style="display: flex; gap: 10px; margin-top: 20px;">
                        <input type="hidden" name="id_commande" value="<?= $commandeEnCours['id'] ?>">
                        
                        <button type="submit" name="action" value="refuser" style="background: #242424; color: #888; border: 1px solid #444; width: 60px; border-radius: 10px; cursor: pointer; font-size: 1.2rem;" onclick="return confirm('Sûr de vouloir annuler ?');">❌</button>
                        
                        <button type="submit" name="action" value="terminer" class="finish-btn" style="flex: 1;">✅ Livraison Terminée</button>
                    </form>

                </div>
            </section>

        <?php else: ?>
            <section class="delivery-section" style="text-align: center; padding: 50px 20px;">
                <h2>Aucune livraison en cours</h2>
                <p style="color: #888; margin-bottom: 20px;">Vous n'avez aucune commande attribuée pour le moment.</p>
                <a href="Livraisons_en_attente.php" class="finish-btn" style="text-decoration: none; display: inline-block;">📡 Voir les commandes en attente</a>
            </section>
        <?php endif; ?>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>Aide au Livreur</h3>
                <p>Un problème ? Contactez le support :<br>01 06 26 60 66</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 Los Pollos Hermanos. Espace Livreur.</p>
        </div>
    </footer>
</body>
<script>
    const menuToggle = document.querySelector('.menu-toggle');
    const navMenu = document.querySelector('nav ul');

    window.addEventListener('load', function() {
            const cible = document.getElementById('zone-livraison');
            if (cible) {
                cible.scrollIntoView({ behavior: 'smooth' });
            }
        });

    menuToggle.addEventListener('click', () => {
        menuToggle.classList.toggle('active');
        navMenu.classList.toggle('active');
    });

    const navLinks = document.querySelectorAll('nav ul li a');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            menuToggle.classList.remove('active');
            navMenu.classList.remove('active');
        });
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('nav')) {
            menuToggle.classList.remove('active');
            navMenu.classList.remove('active');
        }
    });
</script>
</html>
