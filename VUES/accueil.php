<?php
// 1. Démarrage de la session pour l'utilisateur
session_start();

// 2. Chargement des données JSON
// On suppose que tes fichiers sont dans un dossier /data à la racine
$accueil_data = json_decode(file_get_contents('../DATA/accueil.json'), true);
$menu_data = json_decode(file_get_contents('../DATA/menu.json'), true);

// Extraction des infos du Hero et de l'Histoire depuis accueil.json
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
    <link rel="stylesheet" href="../CSS/accueil.css">
</head>
<body>

    <?php include '../LIB/header.php'; ?>

    <header>
        <div class="header-container">
            <div class="logo-box">
                <a href="accueil.php">
                    <img src="../IMAGES/logo.png" alt="Logo Los Pollos" class="nav-logo">
                </a>
            </div>
            
            <nav class="nav-links">
                <a href="accueil.php">Accueil</a>
                <a href="menu.php">Menu</a>
                <a href="profil.php"> Mon Profil</a>
                <a href="Livraison.php">Livraisons</a>
                <?php if(!isset($_SESSION['user'])): ?>
                    <a href="connexion.php">Connexion</a>
                <?php else: ?>
                    <a href="../TRAITEMENTS/deconnexion.php">Déconnexion</a>
                <?php endif; ?>
            </nav>

            <div class="header-right-spacer"></div>
        </div>
    </header>

    <main>
        <section id="top" class="main-title-screen">
            <div class="title-wrap">
                <h1 class="huge-title">LOS POLLOS<br><span class="outline">HERMANOS</span></h1>
                
                <div class="search-container" style="text-align: center; margin-top: 30px;">
                    <form action="menu.php" method="GET" style="display: flex; justify-content: center; gap: 10px;">
                        <input type="text" name="recherche" placeholder="Rechercher un plat (ex: Burger)..." 
                               style="padding: 12px 20px; width: 350px; border-radius: 25px; border: none; font-size: 1rem;">
                        <button type="submit" class="btn-primary" style="border-radius: 25px; cursor: pointer;">🔍 Chercher</button>
                    </form>
                </div>

                <div class="scroll-indicator">
                    <p>SCROLL</p>
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
                        <a href="#histoire" class="btn-secondary">Notre histoire</a>
                    </div>
                </div>
            </div>
        </section>

        <section class="section-padding">
            <div class="container">
                <h2 class="section-title">Nos Incontournables</h2>
                <div class="contact-grid">
                    <?php foreach ($incontournables as $item): ?>
                        <div class="contact-card">
                            <h3><?php echo $item['type']; ?></h3>
                            <p><?php echo $item['nom_plat']; ?></p>
                            <a href="menu.php?id=<?php echo $item['id_reference']; ?>" style="color: #e74c3c; font-weight: bold; text-decoration: none;">Voir le plat →</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section id="histoire" class="section-padding">
            <div class="container">
                <span class="badge-style"><?php echo $histoire['date_fondation']; ?></span>
                <h2 class="section-title">Notre Histoire</h2>
                <div class="history-content">
                    <div class="history-text">
                        <p><?php echo $histoire['description']; ?></p>
                        <blockquote class="quote">"<?php echo $histoire['citation']; ?>"</blockquote>
                    </div>
                </div>
            </div>
        </section>

        <section id="contact" class="section-padding">
            <div class="container">
                <h2 class="section-title">Nous trouver</h2>
                <div class="contact-grid">
                    <div class="contact-card">
                        <h3>📍 Albuquerque</h3>
                        <p><?php echo $contact['adresse']; ?></p>
                    </div>
                    <div class="contact-card">
                        <h3>📞 Téléphone</h3>
                        <p><?php echo $contact['telephone']; ?></p>
                    </div>
                    <div class="contact-card">
                        <h3>⏰ Horaires</h3>
                        <p><?php echo $contact['horaires']; ?></p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include '../LIB/footer.php'; ?>

</body>
</html>

