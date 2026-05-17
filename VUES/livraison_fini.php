<?php include '../LIB/authentification.php';

//Vérification que l'utilisateur est bien un livreur
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'livreur') {
    header('Location: connexion.php?erreur=livreur');
    exit();
}

$id_commande = $_GET['id'] ?? null;

if (!$id_commande) {
    die("Aucune course sélectionnée.");
}

//Récupération des data du fichier JSON
$fichierCommandes = '../DATA/commande.json';
$commandesData = file_get_contents($fichierCommandes);
$commandes = json_decode($commandesData, true);

$commande_trouvee = null;

//Recherche de la commande pour l'affichage
foreach ($commandes as $cmd) {
    if ($cmd['id'] == $id_commande) {
        $commande_trouvee = $cmd;
        break;
    }
}

if (!$commande_trouvee) {
    die("Erreur : Commande introuvable.");
}

$gain_livreur = $commande_trouvee['gain_livreur'] ?? 0;
$distance_km = $commande_trouvee['distance_km'] ?? 0;
$temps_estime = $commande_trouvee['temps_minutes'] ?? 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Livraison Réussie - LOS POLLOS HERMANOS</title>
    <link rel="shortcut icon" type="image/png" href="../IMAGES/logo.png">
    <link rel="icon" type="image/png" href="../IMAGES/logo.png">
    <link rel="stylesheet" href="../CSS/livraison_fini.css">
    <link rel="stylesheet" href="../CSS/livraison.css">
    <script defer>
    document.addEventListener('DOMContentLoaded', function() {
        //Menu mobile
        const menuToggle = document.querySelector('.menu-toggle');
        const navMenu = document.querySelector('nav ul');

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
    
    <main class="success-page">
        
        <div class="success-container">
            <div class="check-animation">
                <svg viewBox="0 0 52 52" class="checkmark">
                    <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/>
                    <path class="checkmark__check" fill="none" d="M14.1 27.2 l7.1 7.2 16.7 -16.8"/>
                </svg>
            </div>

            <h1>Livraison Terminée !</h1>
            <p>Merci pour votre rapidité ⚡</p>

            <div class="earnings-card">
                <span class="label">Gain de la course</span>
                <span class="amount">+ <?= number_format($gain_livreur, 2, ',', ' ') ?> €</span>
                <hr>
                <div class="stats-row">
                    <div>
                        <span class="small-label">Temps</span>
                        <span class="value"><?= $temps_estime ?> min</span>
                    </div>
                    <div>
                        <span class="small-label">Distance</span>
                        <span class="value"><?= $distance_km ?> km</span>
                    </div>
                </div>
            </div>

            <div class="action-buttons">
                <a href="livraisons_en_attente.php" class="primary-btn">🏠 Retour à la page de livraison</a>
            </div>
        </div>

    </main>

    <?php include '../LIB/footer.php'; ?>

</body>
</html>