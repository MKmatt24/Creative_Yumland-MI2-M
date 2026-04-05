<?php
session_start();

// 1. Chargement et sécurisation des données
$json_path = '../DATA/menu.json';
$plats = [];
$menus = [];

if (file_exists($json_path)) {
    $data = json_decode(file_get_contents($json_path), true);
    $plats = $data['plats'] ?? [];
    $menus = $data['menus'] ?? [];
}

// 2. Récupération des filtres (Recherche + Catégorie)
$search = $_GET['search'] ?? '';
$cat_filter = $_GET['categorie'] ?? 'Tous';

// 3. Logique de filtrage combinée pour les Plats
$plats_a_afficher = array_filter($plats, function($p) use ($cat_filter, $search) {
    $matchCat = ($cat_filter === 'Tous' || (isset($p['cat']) && $p['cat'] === $cat_filter));
    $matchSearch = empty($search) || 
                   stripos($p['nom'], $search) !== false || 
                   stripos($p['desc'], $search) !== false;
    return $matchCat && $matchSearch;
});

// 4. Logique de filtrage pour les Formules (Menus)
// On n'affiche les formules que si on est sur "Tous" ou si on fait une recherche spécifique
$menus_a_afficher = array_filter($menus, function($m) use ($search) {
    return empty($search) || 
           stripos($m['nom'], $search) !== false || 
           stripos($m['description'], $search) !== false;
});

// 5. Extraction des catégories pour les boutons de tri
$categories = array_unique(array_column($plats, 'cat'));

// 6. Calcul du total d'articles dans le panier pour le Header
$panier_count = 0;
if (isset($_SESSION['panier'])) {
    foreach ($_SESSION['panier'] as $item) {
        $panier_count += $item['quantite'];
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notre Menu | Los Pollos Hermanos</title>
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
            <li class="panier-nav">
                <a href="panier.php" style="background: var(--orange); color: var(--noir); border-radius: 20px; padding: 5px 15px; font-weight: bold;">
                    🛒 Mon Panier (<?= $panier_count ?>)
                </a>
            </li>
        </ul>
    </nav>
</header>

<main>
    <section class="menu-hero">
        <h2>NOS MENUS LÉGENDAIRES</h2>
        
        <div class="search-bar-container">
            <form method="GET" action="menu.php">
                <input type="hidden" name="categorie" value="<?= htmlspecialchars($cat_filter) ?>">
                <input type="text" name="search" placeholder="Rechercher un plaisir coupable..." value="<?= htmlspecialchars($search) ?>">
            </form>
        </div>

        <div class="categories">
            <a href="menu.php?categorie=Tous&search=<?= urlencode($search) ?>" 
               class="cat-btn <?= $cat_filter === 'Tous' ? 'active' : '' ?>">Tous</a>
            
            <?php foreach ($categories as $cat): ?>
                <a href="menu.php?categorie=<?= urlencode($cat) ?>&search=<?= urlencode($search) ?>" 
                   class="cat-btn <?= $cat_filter === $cat ? 'active' : '' ?>">
                    <?= htmlspecialchars($cat) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <?php if ($cat_filter === 'Tous' && !empty($menus_a_afficher)): ?>
    <section class="menus-complets">
        <h3 class="section-subtitle" style="color: var(--orange); margin-left: 20px;">Nos Formules</h3>
        <div class="menu-container">
            <?php foreach ($menus_a_afficher as $m): ?>
                <div class="menu-card special-menu">
                    <div class="card-body">
                        <span class="badge">Édition Limitée</span>
                        <h3><?= htmlspecialchars($m['nom']) ?></h3>
                        <p style="font-size: 0.85rem; color: #888; margin-bottom: 15px;"><?= htmlspecialchars($m['description'] ?? '') ?></p>
                        
                        <div class="composition-box">
                            <p style="font-size: 0.8rem; font-weight: bold; color: #555; margin-bottom: 5px;">Contenu du menu :</p>
                            <ul>
                                <?php foreach ($m['liste_plats'] as $item): ?>
                                    <li><?= htmlspecialchars($item) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <div class="card-footer">
                            <span class="price"><?= number_format($m['prix'], 2) ?>€</span>
                            <form action="../TRAITEMENTS/ajouter_panier.php" method="POST">
                                <input type="hidden" name="nom" value="<?= htmlspecialchars($m['nom']) ?>">
                                <input type="hidden" name="prix" value="<?= $m['prix'] ?>">
                                <input type="number" name="quantite" value="1" min="1" class="qty-input">
                                <button type="submit" class="add-btn">AJOUTER</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <section class="menus-complets">
        <h3 class="section-subtitle" style="color: var(--orange); margin-left: 20px; margin-top: 40px;">
            <?= ($cat_filter === 'Tous') ? 'Nos Plats à la Carte' : 'Catégorie : ' . htmlspecialchars($cat_filter) ?>
        </h3>
        
        <div class="menu-container">
            <?php if (empty($plats_a_afficher)): ?>
                <p style="text-align: center; width: 100%; color: #555;">Aucun plat trouvé pour votre recherche.</p>
            <?php else: ?>
                <?php foreach ($plats_a_afficher as $p): ?>
                    <div class="menu-card">
                        <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['nom']) ?>">
                        <div class="card-body">
                            <span class="badge"><?= htmlspecialchars($p['cat']) ?></span>
                            <h3><?= htmlspecialchars($p['nom']) ?></h3>
                            <p><?= htmlspecialchars($p['desc']) ?></p>
                            
                            <div class="card-footer">
                                <span class="price"><?= number_format($p['prix'], 2) ?>€</span>
                                <form action="../TRAITEMENTS/ajouter_panier.php" method="POST">
                                    <input type="hidden" name="nom" value="<?= htmlspecialchars($p['nom']) ?>">
                                    <input type="hidden" name="prix" value="<?= $p['prix'] ?>">
                                    <input type="number" name="quantite" value="1" min="1" class="qty-input">
                                    <button type="submit" class="add-btn">AJOUTER</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</main>

<footer>
    <p>&copy; 2026 Los Pollos Hermanos - Albuquerque. Tous droits réservés.</p>
</footer>

</body>
</html>