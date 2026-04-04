<?php
session_start();

// 1. Chargement des données JSON
$json_path = '../data/menu.json';
if (!file_exists($json_path)) {
    die("Erreur : Le fichier de données du menu est introuvable.");
}

$data = json_decode(file_get_contents($json_path), true);
$plats = $data['plats'] ?? [];
$menus_complets = $data['menus'] ?? []; 

// 2. Logique de filtrage
$recherche = isset($_GET['recherche']) ? strtolower(trim($_GET['recherche'])) : '';
$categorie_active = $_GET['cat'] ?? 'Tout';

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
        <div class="logo-box">
            <a href="accueil.php"><img src="../IMAGES/logo.png" alt="Logo" class="nav-logo"></a>
        </div>
        <ul>
            <li><a href="accueil.php">Accueil</a></li>
            <li><a href="menu.php" class="active">Menu</a></li>
            <li><a href="profil.php">Mon Profil</a></li>
            <?php if(isset($_SESSION['user']['role']) && ($_SESSION['user']['role'] === 'restaurateur' || $_SESSION['user']['role'] === 'admin')): ?>
                <li><a href="commande.php">Gestion</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<main>
    <section class="menu-hero">
        <h2>Nos Menus Légendaires</h2>
        <form action="menu.php" method="GET" class="search-bar-container">
            <input type="text" name="recherche" value="<?php echo htmlspecialchars($recherche); ?>" placeholder="Rechercher un plaisir coupable...">
        </form>
    </section>

    <?php if ($categorie_active === 'Tout' && empty($recherche)): ?>
    <section class="menus-complets">
        <h2 class="section-title">Nos Formules</h2>
        <div class="menu-container">
            <?php foreach($menus_complets as $m): ?>
                <div class="menu-card special-menu">
                    <div class="card-body">
                        <span class="badge">Édition Limitée</span>
                        <h3><?php echo htmlspecialchars($m['nom']); ?></h3>
                        <p class="description"><?php echo htmlspecialchars($m['description']); ?></p>
                        
                        <div class="composition-box">
                            <strong>Contenu du menu :</strong>
                            <ul>
                                <?php foreach($m['liste_plats'] as $item): ?>
                                    <li><?php echo htmlspecialchars($item); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <div class="card-footer">
                            <span class="price"><?php echo number_format($m['prix'], 2); ?>€</span>
                            <form action="ajouter_panier.php" method="POST">
                                <input type="hidden" name="id_menu" value="<?php echo $m['id']; ?>">
                                <input type="number" name="quantite" value="1" min="1" class="qty-input">
                                <button type="submit" class="add-btn">Ajouter</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <hr style="border: 0; border-top: 1px solid #333; width: 80%; margin: 2rem auto;">
    <?php endif; ?>

    <div class="categories">
        <?php 
        $cats = ['Tout', 'Burgers', 'Poulet', 'Spécialités', 'Accompagnements', 'Desserts'];
        foreach($cats as $c): ?>
            <a href="menu.php?cat=<?php echo $c; ?>" class="cat-btn <?php echo ($categorie_active === $c) ? 'active' : ''; ?>">
                <?php echo $c; ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="menu-container">
        <?php if(empty($plats_filtres)): ?>
            <p style="text-align:center; grid-column: 1/-1; padding: 3rem;">Aucun plat ne correspond à votre recherche.</p>
        <?php else: ?>
            <?php foreach($plats_filtres as $p): ?>
                <div class="menu-card">
                    <img src="<?php echo htmlspecialchars($p['image']); ?>" alt="<?php echo htmlspecialchars($p['nom']); ?>">
                    <div class="card-body">
                        <span class="badge"><?php echo htmlspecialchars($p['cat']); ?></span>
                        <h3><?php echo htmlspecialchars($p['nom']); ?></h3>
                        <p><?php echo htmlspecialchars($p['desc']); ?></p>
                        
                        <div class="card-footer">
                            <span class="price"><?php echo number_format($p['prix'], 2); ?>€</span>
                            <form action="ajouter_panier.php" method="POST">
                                <input type="hidden" name="id_plat" value="<?php echo $p['id']; ?>">
                                <input type="number" name="quantite" value="1" min="1" class="qty-input">
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