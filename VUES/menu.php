<?php
session_start();

// 1. Chargement des données JSON
$json_path = '../DATA/menu.json';
$data = json_decode(file_get_contents($json_path), true);
$plats = $data['plats'] ?? [];
$menus = $data['menus'] ?? [];

// 2. Gestion des filtres (Recherche + Catégories)
$search = $_GET['search'] ?? '';
$cat_filter = $_GET['categorie'] ?? 'Tous';

// Filtrage des plats à la carte
$plats_a_afficher = array_filter($plats, function($p) use ($cat_filter, $search) {
    $matchCat = ($cat_filter === 'Tous' || (isset($p['cat']) && $p['cat'] === $cat_filter));
    $matchSearch = empty($search) || stripos($p['nom'], $search) !== false;
    return $matchCat && $matchSearch;
});

// Filtrage des menus (formules)
$menus_a_afficher = array_filter($menus, function($m) use ($search) {
    return empty($search) || stripos($m['nom'], $search) !== false;
});

// Extraction des catégories pour les boutons
$categories = array_unique(array_column($plats, 'cat'));

// 3. Calcul du compteur panier
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
    <title>Menu | Los Pollos Hermanos</title>
    <link rel="stylesheet" href="../CSS/menu.css">
</head>
<body>

<header>
    <nav>
        <div class="logo-box">
            <a href="accueil.php"><img src="../IMAGES/logo.png" alt="Logo Los Pollos" class="nav-logo"></a>
        </div>
        <ul>
            <li><a href="accueil.php">Accueil</a></li>
            <li><a href="menu.php" class="active">Menu</a></li>
            <li><a href="profil.php">Mon Profil</a></li>
            <li class="panier-nav">
                <a href="panier.php" style="background: var(--orange); color: var(--noir); border-radius: 20px; padding: 5px 15px; font-weight: bold;">
                    🛒 Panier (<?= $panier_count ?>)
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
    <h3 class="section-subtitle">Nos Formules</h3>
    <div class="menu-container">
        <?php foreach ($menus_a_afficher as $m): ?>
            <div class="menu-card">
                <div class="card-body">
                    <span class="badge">Édition Limitée</span>
                    <h3><?= htmlspecialchars($m['nom']) ?></h3>
                    <p><?= htmlspecialchars($m['description']) ?></p>
                    
                    <div class="composition-box">
                        <p style="font-size: 0.8rem; color: #555; margin-bottom: 5px;">Contenu du menu :</p>
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
    <?php endif; ?>

    <h3 class="section-subtitle"><?= ($cat_filter === 'Tous') ? 'À la Carte' : htmlspecialchars($cat_filter) ?></h3>
    <div class="menu-container">
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
    </div>
</main>

<footer>
    <p>&copy; 2026 Los Pollos Hermanos - Albuquerque. Tous droits réservés.</p>
</footer>

</body>
</html>