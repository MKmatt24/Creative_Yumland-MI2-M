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
    <?php if (isset($_SESSION['modification_id'])): ?>
        <div class="modification-banner">
            ⚠️ MODE MODIFICATION : Vous modifiez votre commande #<?= $_SESSION['modification_id'] ?>
        </div>
    <?php endif; ?>

    <section class="menu-hero">
        <div class="hero-header-flex">
            <h2>NOS MENUS LÉGENDAIRES</h2>
            <a href="panier.php" class="btn-cart-float">
                🛒 MON PANIER (<span id="cart-count-val"><?= $cart_count ?></span>)
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
            <?php 
                // Logique de disponibilité (ex: Menu Midi)
                $now = date('H:i');
                $debut = $m['heure_debut'] ?? '00:00';
                $fin = $m['heure_fin'] ?? '23:59';
                $dispo = ($now >= $debut && $now <= $fin);
            ?>
            <div class="menu-card">
                <div class="card-body">
                    <div class="badge-container">
                        <span class="badge">Édition Limitée</span>
                        <?php if (isset($m['min_personnes'])): ?>
                            <span class="badge badge-blue">👥 Min. <?= htmlspecialchars($m['min_personnes']) ?> pers.</span>
                        <?php endif; ?>
                        <?php if (isset($m['creneau'])): ?>
                            <span class="badge badge-yellow">🕒 <?= htmlspecialchars($m['creneau']) ?></span>
                        <?php endif; ?>
                    </div>
                    <h3><?= htmlspecialchars($m['nom'] ?? 'Menu Sans Nom') ?></h3>
                    <p><?= htmlspecialchars($m['description'] ?? '') ?></p>
                    
                    <div class="composition-box">
                        <p class="comp-title">Contenu du menu :</p>
                        <ul class="comp-list">
                            <?php foreach (($m['liste_plats'] ?? []) as $item): ?>
                                <li>✓ <?= htmlspecialchars($item) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <div class="card-footer">
                        <span class="price"><?= number_format(($m['prix'] ?? 0), 2) ?>€</span>
                        <form action="../TRAITEMENTS/ajouter_panier.php" method="POST" class="add-form">
                            <div class="qty-group">
                                <input type="hidden" name="nom" value="<?= htmlspecialchars($m['nom']) ?>">
                                <input type="hidden" name="prix" value="<?= $m['prix'] ?>">
                                <input type="number" name="quantite" value="<?= $m['min_personnes'] ?? 1 ?>" min="<?= $m['min_personnes'] ?? 1 ?>" class="qty-input">
                                <button type="submit" class="add-btn" <?= !$dispo ? 'disabled' : '' ?>>
                                    <?= $dispo ? 'AJOUTER' : 'INDISPONIBLE' ?>
                                </button>
                            </div>
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
                    <img src="<?= htmlspecialchars($p['image'] ?? '../IMAGES/default.png') ?>" alt="<?= htmlspecialchars($p['nom'] ?? 'Plat') ?>">
                    <div class="card-body">
                        <span class="badge"><?= htmlspecialchars($p['cat'] ?? 'Divers') ?></span>
                        <h3><?= htmlspecialchars($p['nom'] ?? 'Nom non défini') ?></h3>
                        <p><?= htmlspecialchars($p['desc'] ?? '') ?></p>
                        
                        <form action="../TRAITEMENTS/ajouter_panier.php" method="POST" class="add-form">
                            <?php if (!empty($p['ingredients']) || !empty($p['substitutions'])): ?>
                            <div class="customization-area">
                                <p class="comp-title">Personnaliser le plat :</p>
                                <div class="ingredients-checks">
                                    <?php foreach (($p['ingredients'] ?? []) as $ing): ?>
                                        <label class="check-item">
                                            <input type="checkbox" name="ingredients[]" value="<?= htmlspecialchars($ing) ?>" checked>
                                            <span><?= htmlspecialchars($ing) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                                
                                <?php if (isset($p['substitutions'])): ?>
                                    <div class="subs-area">
                                        <?php foreach ($p['substitutions'] as $original => $options): ?>
                                            <select name="sub_<?= md5($original) ?>" class="sub-select">
                                                <option value="<?= htmlspecialchars($original) ?>">Garder <?= htmlspecialchars($original) ?></option>
                                                <?php foreach ($options as $opt): ?>
                                                    <option value="<?= htmlspecialchars($opt) ?>">Remplacer <?= htmlspecialchars($original) ?> par <?= htmlspecialchars($opt) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>

                            <div class="card-footer">
                                <span class="price"><?= number_format(($p['prix'] ?? 0), 2) ?>€</span>
                                <div class="qty-group">
                                    <input type="hidden" name="nom" value="<?= htmlspecialchars($p['nom']) ?>">
                                    <input type="hidden" name="prix" value="<?= $p['prix'] ?>">
                                    <input type="number" name="quantite" value="1" min="1" class="qty-input">
                                    <button type="submit" class="add-btn">AJOUTER</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-results">Aucun plat ne correspond à votre recherche.</p>
        <?php endif; ?>
    </div>
</main>

<?php include '../LIB/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // On récupère tous les formulaires d'ajout au panier
    const addForms = document.querySelectorAll('.add-form');
    const cartCountSpan = document.getElementById('cart-count-val');

    addForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Empêche le rechargement de la page

            const formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mise à jour fluide du compteur
                    cartCountSpan.textContent = data.new_count;
                }
            })
            .catch(error => console.error('Erreur:', error));
        });
    });
});
</script>

</body>
</html>
