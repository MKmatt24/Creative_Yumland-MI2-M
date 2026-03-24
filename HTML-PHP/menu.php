<?php
session_start();

// 1. Chargement des données depuis le fichier JSON unique
$menu_json = file_get_contents('../data/menu.json');
$data = json_decode($menu_json, true);

$plats = $data['plats'];
$menus_complets = $data['menus']; // Les 3 menus demandés par le sujet

// 2. Logique de filtrage (Recherche depuis l'accueil ou Catégories)
$recherche = isset($_GET['recherche']) ? strtolower($_GET['recherche']) : '';
$categorie_active = isset($_GET['cat']) ? $_GET['cat'] : 'Tout';

// Fonction de filtrage
$plats_filtres = array_filter($plats, function($p) use ($recherche, $categorie_active) {
    $match_search = empty($recherche) || 
                    strpos(strtolower($p['nom']), $recherche) !== false || 
                    strpos(strtolower($p['desc']), $recherche) !== false;
    
    $match_cat = ($categorie_active === 'Tout') || ($p['cat'] === $categorie_active);
    
    return $match_search && $match_cat;
});
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu | Los Pollos Hermanos</title>
    <link rel="stylesheet" href="../CSS/menu.css">
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
        <ul>
            <li><a href="accueil.php">Accueil</a></li>
            <li><a href="menu.php" class="active">Menu</a></li>
            <li><a href="profil.php">Mon Profil</a></li>
            <li><a href="livraisons.php">Livraisons</a></li>
        </ul>
    </nav>
</header>

<main>
    <section class="menu-hero">
        <h2>Nos Menus Légendaires</h2>
        <p>Le goût inimitable du Nouveau-Mexique, livré chez vous.</p>
        
        <form action="menu.php" method="GET" class="search-bar-container">
            <input type="text" name="recherche" value="<?php echo htmlspecialchars($recherche); ?>" placeholder="Rechercher un plat...">
            <button type="submit" style="display:none"></button>
        </form>
    </section>

    <div class="categories">
        <?php 
        $cats = ['Tout', 'Burgers', 'Poulet', 'Spécialités', 'Accompagnements', 'Desserts'];
        foreach($cats as $c): 
            $active_class = ($categorie_active === $c) ? 'active' : '';
        ?>
            <a href="menu.php?cat=<?php echo $c; ?>" class="cat-btn <?php echo $active_class; ?>" style="text-decoration:none;">
                <?php echo $c; ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="menu-container" id="menu-grid">
        <?php if(empty($plats_filtres)): ?>
            <p style="grid-column: 1/-1; text-align: center; padding: 2rem; color: #666;">
                Aucun plat ne correspond à votre recherche "<?php echo htmlspecialchars($recherche); ?>".
            </p>
        <?php else: ?>
            <?php foreach($plats_filtres as $p): ?>
                <div class="menu-card">
                    <div class="card-image">
                        <img src="<?php echo $p['image']; ?>" alt="<?php echo $p['nom']; ?>">
                    </div>
                    <div class="card-body">
                        <span class="badge"><?php echo $p['cat']; ?></span>
                        <h3><?php echo $p['nom']; ?></h3>
                        <p><?php echo $p['desc']; ?></p>
                        
                        <small style="display:block; margin-bottom:10px; color:#888;">
                            <?php echo $p['infos']['calories']; ?> kcal | Allergènes: <?php echo implode(', ', $p['infos']['allergènes']); ?>
                        </small>

                        <div class="card-footer">
                            <span class="price"><?php echo number_format($p['prix'], 2); ?>€</span>
                            <form action="ajouter_panier.php" method="POST">
                                <input type="hidden" name="id_plat" value="<?php echo $p['id']; ?>">
                                <button type="submit" class="add-btn">+</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<footer>
    <p>&copy; 2026 LOS POLLOS HERMANOS - TASTE THE FAMILY</p>
</footer>

</body>
</html>