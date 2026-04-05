<?php
session_start();

// Chargement des données JSON
$accueil_data = json_decode(file_get_contents('../DATA/accueil.json'), true);
$hero = $accueil_data['hero'];
$histoire = $accueil_data['histoire'];
$contact = $accueil_data['contact'];
$incontournables = $accueil_data['incontournables'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Los Pollos Hermanos | Albuquerque</title>
    <link rel="icon" type="image/png" href="../IMAGES/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../CSS/accueil.css">
</head>
<body>

    <?php include '../LIB/header.php'; ?>

    <main>
        <section id="top" class="main-title-screen">
            <div class="title-wrap">
                <h1 class="huge-title">LOS POLLOS<br><span class="outline">HERMANOS</span></h1>
                
                <div class="search-container">
                    <form action="menu.php" method="GET" class="search-form">
                        <input type="text" name="recherche" placeholder="Rechercher un plaisir coupable (ex: Poulet)...">
                        <button type="submit">🔍 CHERCHER</button>
                    </form>
                </div>

                <div class="scroll-indicator">
                    <div class="arrow"></div>
                </div>
            </div>
        </section>

        <section class="hero">
            <div class="hero-split-container">
                <div class="hero-image-wrapper">
                    <img src="<?php echo $hero['image_gus']; ?>" alt="Gustavo Fring" class="gus-img">
                </div>
                <div class="hero-content">
                    <div class="badge-style"><?php echo $hero['badge']; ?></div>
                    <h2 class="hero-main-title"><?php echo $hero['titre']; ?></h2>
                    <p class="hero-subtitle"><?php echo $hero['sous_titre']; ?></p>
                    <div class="hero-buttons">
                        <a href="menu.php" class="btn-primary">Découvrir la carte</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="main-footer">
        <div class="footer-grid">
            <div class="footer-section">
                <img src="../IMAGES/logo.png" alt="Logo" class="footer-mini-logo">
                <p>Le meilleur poulet frit de tout l'État. Une tradition familiale, un goût inégalé.</p>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                </div>
            </div>

            <div class="footer-section">
                <h3>Navigation</h3>
                <ul>
                    <li><a href="accueil.php">Accueil</a></li>
                    <li><a href="menu.php">Menu</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="livraisons.php">Livraisons</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h3>Nous contacter</h3>
                <p><i class="fas fa-map-marker-alt"></i> Albuquerque, New Mexico</p>
                <p><i class="fas fa-phone"></i> +1 505-148-2900</p>
                <p><i class="fas fa-envelope"></i> contact@lospolloshermanos.fr</p>
            </div>
        </div>
        
        <div class="footer-copyright">
            <p>&copy; 2026 Los Pollos Hermanos. Tous droits réservés.</p>
        </div>
    </footer>

</body>
</html>