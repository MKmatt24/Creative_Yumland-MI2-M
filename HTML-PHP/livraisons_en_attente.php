<?php
session_start();

$_SESSION['user_id'] = 2; 

//Vérification si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit();
}

//Récupération des data du fichier JSON
$fichierCommandes = '../DATA/commande.json';
$commandesData = file_get_contents($fichierCommandes);
$commandes = json_decode($commandesData, true);

//Quand le livreur accepte une course
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id_commande'])) {
    if ($_POST['action'] === 'accepter') {
        $idCible = $_POST['id_commande'];

//Recherche de la commande pour modfier son statut
        foreach ($commandes as &$cmd) {
            if ($cmd['id'] == $idCible) {
                $cmd['statut'] = 'livraison';
                $cmd['livreur_id'] = $_SESSION['user_id']; 
                break;
            }
        }

//Sauvegarde du fichier JSON modifié et redirection vers la page de livraison
        file_put_contents($fichierCommandes, json_encode($commandes, JSON_PRETTY_PRINT));
        header('Location: livraison.php');
        exit();
    }
}

//Recherche des courses en préparation
$offres = [];

foreach ($commandes as $cmd) {
    if ($cmd['statut'] === 'preparation') {
        $offres[] = $cmd;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses Disponibles - LOS POLLOS HERMANOS</title>
    <link rel="stylesheet" href="../CSS/livraisons_en_attente.css"> 
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
                <li><a href="accueil.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="radar-section">
            
            <div class="status-banner">
                <div class="pulse-ring"></div>
                <h2>En ligne - Recherche de livraisons en attente...</h2>
            </div>

            <div class="offers-grid">
                
                <?php if (empty($offres)): ?>
                    <div class="empty-state" style="text-align: center; padding: 40px 20px; background: #242424; border-radius: 15px; border: 1px solid #333;">
                        <h3 style="color: #888;">Aucune course disponible</h3>
                        <p style="color: #666; font-size: 0.9rem; margin-top: 10px;">Le restaurant est calme pour le moment. Restez en ligne.</p>
                    </div>
                <?php else: ?>

                    <?php foreach ($offres as $offre): 
                        //Distance inventé qui est basée sur l'ID de la commande pour que chaque course ait des km différents sans passer par un vrai algo de GPS
                        $distanceKm = 1.5 + ($offre['id'] % 6);
                        //Calcul du gain : 2.50€ de forfait + 0.80€ / km
                        $gainEstime = 2.50 + ($distanceKm * 0.80);
                        //Calcul d'un temps estimé (environ 4 min par km)
                        $tempsEstime = round($distanceKm * 4);
                    ?>
                        <div class="offer-card">
                            <div class="offer-header">
                                <div class="price-block">
                                    <span class="price-label">Gains estimés (Commande #<?= htmlspecialchars($offre['id']) ?>)</span>
                                    <h3 class="offer-price"><?= number_format($gainEstime, 2, ',', ' ') ?> €</h3>
                                </div>
                                <div class="time-block">
                                    <span class="offer-time">⏱ ~<?= $tempsEstime ?> min</span>
                                    <span class="offer-distance">📍 <?= number_format($distanceKm, 1, ',', '') ?> km au total</span>
                                </div>
                            </div>

                            <div class="offer-route">
                                <div class="route-step pickup">
                                    <strong>Récupération :</strong> Los Pollos Hermanos
                                </div>
                                <div class="route-step dropoff">
                                    <strong>Livraison :</strong> <?= htmlspecialchars($offre['adresse']) ?>
                                </div>
                            </div>

                            <form method="POST" action="Livraisons_en_attente.php" class="offer-actions" style="display: flex; gap: 10px;">
                                <input type="hidden" name="id_commande" value="<?= $offre['id'] ?>">
                                
                                <button type="button" class="decline-btn" aria-label="Ignorer la course" onclick="this.closest('.offer-card').style.display='none';">❌</button>
                                
                                <button type="submit" name="action" value="accepter" class="accept-btn" style="flex: 1;">Accepter la course</button>
                            </form>
                        </div>
                    <?php endforeach; ?>

                <?php endif; ?>

            </div>
        </section>
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