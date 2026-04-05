<?php
session_start();

// 1. Chargement des données JSON avec sécurité
$json_path = '../DATA/menu.json';
$data = file_exists($json_path) ? json_decode(file_get_contents($json_path), true) : [];

$plats = $data['plats'] ?? [];
$menus = $data['menus'] ?? [];

// 2. Gestion des filtres (Recherche + Catégories)
$search = $_GET['search'] ?? '';
$cat_filter = $_GET['categorie'] ?? 'Tous';

// Filtrage des plats à la carte
$plats_a_afficher = array_filter($plats, function($p) use ($cat_filter, $search) {
    $matchCat = ($cat_filter === 'Tous' || (isset($p['cat']) && $p['cat'] === $cat_filter));
    $matchSearch = empty($search) || stripos(($p['nom'] ?? ''), $search) !== false;
    return $matchCat && $matchSearch;
});

// Filtrage des menus (formules)
$menus_a_afficher = array_filter($menus, function($m) use ($search) {
    return empty($search) || stripos(($m['nom'] ?? ''), $search) !== false;
});

// Extraction des catégories pour les boutons
$categories = array_unique(array_column($plats, 'cat'));

// Calcul du nombre d'articles dans le panier pour le bouton
$cart_count = 0;
if (isset($_SESSION['panier'])) {
    foreach ($_SESSION['panier'] as $item) {
        $cart_count += $item['quantite'];
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu | Los Pollos Hermanos</title>
    <link rel="stylesheet" href="../CSS/accueil.css">
    <link rel="stylesheet" href="../CSS/menu.css">
</head>
<body>

<?php include '../LIB/header.php'; ?>

<main>
    <section class="menu-hero">
        <div style="display: flex; justify-content: center; align-items: center; gap: 30px; margin-bottom: 20px; flex-wrap: wrap;">
            <h2 style="margin: 0;">NOS MENUS LÉGENDAIRES</h2>
            
            <a href="panier.php" class="btn-cart-float" style="
                background: transparent;
                border: 2px solid #ff6b35;
                color: #ff6b35;
                padding: 10px 20px;
                border-radius: 50px;
                text-decoration: none;
                font-weight: bold;
                transition: 0.3s;
                display: flex;
                align-items: center;
                gap: 10px;">
                🛒 MON PANIER (<?= $cart_count ?>)
            </a>
        </div>
        
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
                    <h3><?= htmlspecialchars($m['nom'] ?? 'Menu Sans Nom') ?></h3>
                    <p><?= htmlspecialchars($m['description'] ?? '') ?></p>
                    
                    <div class="composition-box">
                        <p style="font-size: 0.8rem; color: #888; margin-bottom: 5px;">Contenu du menu :</p>
                        <ul style="list-style: none; padding: 0; color: #ccc; font-size: 0.85rem;">
                            <?php foreach (($m['liste_plats'] ?? []) as $item): ?>
                                <li>✓ <?= htmlspecialchars($item) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <div class="card-footer" style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
                        <span class="price" style="font-size: 1.4rem; font-weight: bold; color: #fafafa;"><?= number_format(($m['prix'] ?? 0), 2) ?>€</span>
                        <form action="../TRAITEMENTS/ajouter_panier.php" method="POST" style="display: flex; gap: 5px;">
                            <input type="hidden" name="nom" value="<?= htmlspecialchars($m['nom']) ?>">
                            <input type="hidden" name="prix" value="<?= $m['prix'] ?>">
                            <input type="number" name="quantite" value="1" min="1" class="qty-input" style="width: 50px; background: #222; color: white; border: 1px solid #444; padding: 5px; border-radius: 5px;">
                            <button type="submit" class="add-btn" style="background: #ff6b35; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-weight: bold;">AJOUTER</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <h3 class="section-subtitle"><?= ($cat_filter === 'Tous') ? 'À la Carte' : htmlspecialchars($cat_filter) ?></h3>
    <div class="menu-container">
        <?php if (!empty($plats_a_afficher)): ?>
            <?php foreach ($plats_a_afficher as $p): ?>
                <div class="menu-card">
                    <img src="<?= htmlspecialchars($p['image'] ?? '../IMAGES/default.png') ?>" alt="<?= htmlspecialchars($p['nom'] ?? 'Plat') ?>" style="width: 100%; border-radius: 10px; margin-bottom: 15px;">
                    <div class="card-body">
                        <span class="badge" style="background: rgba(255, 107, 53, 0.2); color: #ff6b35; padding: 3px 8px; border-radius: 5px; font-size: 0.7rem;"><?= htmlspecialchars($p['cat'] ?? 'Divers') ?></span>
                        <h3 style="margin: 10px 0;"><?= htmlspecialchars($p['nom'] ?? 'Nom non défini') ?></h3>
                        <p style="color: #888; font-size: 0.9rem;"><?= htmlspecialchars($p['desc'] ?? '') ?></p>
                        
                        <div class="card-footer" style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
                            <span class="price" style="font-size: 1.4rem; font-weight: bold; color: #fafafa;"><?= number_format(($p['prix'] ?? 0), 2) ?>€</span>
                            <form action="../TRAITEMENTS/ajouter_panier.php" method="POST" style="display: flex; gap: 5px;">
                                <input type="hidden" name="nom" value="<?= htmlspecialchars($p['nom']) ?>">
                                <input type="hidden" name="prix" value="<?= $p['prix'] ?>">
                                <input type="number" name="quantite" value="1" min="1" class="qty-input" style="width: 50px; background: #222; color: white; border: 1px solid #444; padding: 5px; border-radius: 5px;">
                                <button type="submit" class="add-btn" style="background: #ff6b35; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-weight: bold;">AJOUTER</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; width: 100%; color: #888;">Aucun plat ne correspond à votre recherche.</p>
        <?php endif; ?>
    </div>
</main>

<?php include '../LIB/footer.php'; ?>

</body>
</html>