<?php
session_start();
// Chargement des données du JSON
$json_content = file_get_contents('../DATA/menu.json');
$data = json_decode($json_content, true);
$plats = $data['plats'] ?? [];

// Logique de recherche et filtrage
$search = $_GET['search'] ?? '';
$cat_filter = $_GET['categorie'] ?? 'Tous';

$plats_filtres = array_filter($plats, function($item) use ($search, $cat_filter) {
    $match_search = empty($search) || stripos($item['nom'], $search) !== false;
    $match_cat = ($cat_filter === 'Tous') || ($item['cat'] === $cat_filter);
    return $match_search && $match_cat;
});

$categories = array_unique(array_column($plats, 'cat'));
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Menu | Los Pollos Hermanos</title>
    <link rel="stylesheet" href="../CSS/menu.css">
    <style>
        /* Agencement des cartes */
        .menu-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; padding: 20px; }
        .menu-card { background: #111; border: 1px solid #333; border-radius: 8px; overflow: hidden; position: relative; transition: 0.3s; }
        .menu-card:hover { border-color: #ff6b35; transform: translateY(-5px); }
        .menu-card img { width: 100%; height: 180px; object-fit: cover; }
        .menu-info { padding: 15px; color: white; }
        
        /* Style du bouton "Plus" (+) quand on est connecté */
        .btn-plus {
            position: absolute;
            bottom: 15px;
            right: 15px;
            background: #ff6b35;
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s;
        }
        .btn-plus:hover { background: #e55a2b; transform: scale(1.1); }

        /* Style du lien de connexion quand on n'est pas connecté */
        .btn-auth-link {
            color: #ff6b35;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
        }
        .btn-auth-link:hover { text-decoration: underline; }

        .search-section { text-align: center; padding: 20px; background: #0a0a0a; border-bottom: 1px solid #222; }
        .search-bar { padding: 10px; width: 60%; border-radius: 20px; border: 1px solid #333; background: #000; color: white; }
    </style>
</head>
<body>

<header>
    <nav>
        <div class="logo-box"><a href="accueil.php"><img src="../IMAGES/logo.png" alt="Logo" class="nav-logo"></a></div>
        <ul>
            <li><a href="accueil.php">Accueil</a></li>
            <li><a href="menu.php" class="active">Menu</a></li>
            <?php if(isset($_SESSION['user'])): ?>
                <li><a href="panier.php">Mon Panier</a></li>
                <li><a href="deconnexion.php">Déconnexion</a></li>
            <?php else: ?>
                <li><a href="connexion.php">Connexion</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<div class="search-section">
    <form method="GET">
        <input type="text" name="search" class="search-bar" placeholder="Rechercher un délice..." value="<?= htmlspecialchars($search) ?>">
        <div style="margin-top: 10px;">
            <a href="menu.php?categorie=Tous" class="btn-auth-link" style="margin: 0 10px;">Tous</a>
            <?php foreach ($categories as $cat): ?>
                <a href="menu.php?categorie=<?= urlencode($cat) ?>" class="btn-auth-link" style="margin: 0 10px;"><?= $cat ?></a>
            <?php endforeach; ?>
        </div>
    </form>
</div>

<main class="menu-grid">
    <?php foreach ($plats_filtres as $item): ?>
        <div class="menu-card">
            <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['nom']) ?>">
            <div class="menu-info">
                <h3 style="margin: 0; font-size: 1.1rem;"><?= htmlspecialchars($item['nom']) ?></h3>
                <p style="color: #888; font-size: 0.85rem; margin: 10px 0;"><?= htmlspecialchars($item['desc']) ?></p>
                <p style="color: #ff6b35; font-weight: bold;"><?= number_format($item['prix'], 2) ?>€</p>
                
                <?php if(isset($_SESSION['user'])): ?>
                    <form action="../TRAITEMENTS/ajouter_panier.php" method="POST">
                        <input type="hidden" name="nom" value="<?= htmlspecialchars($item['nom']) ?>">
                        <input type="hidden" name="prix" value="<?= $item['prix'] ?>">
                        <button type="submit" class="btn-plus" title="Ajouter au panier">+</button>
                    </form>
                <?php else: ?>
                    <a href="connexion.php?error=auth_required" class="btn-auth-link">Se connecter pour commander</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</main>

</body>
</html>