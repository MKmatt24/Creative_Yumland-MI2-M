<?php
session_start();

// 1. Chargement des données JSON avec sécurité
$json_path = '../DATA/menu.json';
$data = file_exists($json_path) ? json_decode(file_get_contents($json_path), true) : [];

$plats = $data['plats'] ?? [];
$menus = $data['menus'] ?? [];

 // 2. Préparation des variables initiales
$search = $_GET['search'] ?? '';
$cat_filter = 'Tous';
$menus_a_afficher = $menus;

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
            <input type="text" id="ajax-search" placeholder="Rechercher un plaisir coupable..." value="<?= htmlspecialchars($search) ?>">
        </div>

        <div class="categories" id="cat-filters">
            <button class="cat-btn active" data-cat="Tous">Tous</button>
            <?php foreach ($categories as $cat): ?>
                <button class="cat-btn" data-cat="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></button>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="container container-menu-layout">
        <div class="filter-sidebar">
            <div class="checkbox-filters">
                <div style="width: 100%"><p class="filter-group-header">Régimes & Préférences</p></div>
                <label><input type="checkbox" class="diet-filter" value="vegetarien"> Végétarien</label>
                <label><input type="checkbox" class="diet-filter" value="vegan"> Vegan</label>
                <label><input type="checkbox" class="diet-filter" value="halal"> Halal</label>
                <label><input type="checkbox" class="diet-filter" value="sans_gluten"> Sans Gluten</label>
                
                <div style="width: 100%"><p class="filter-group-header">Saveurs</p></div>
                <label><input type="checkbox" class="taste-filter" value="salé"> Salé</label>
                <label><input type="checkbox" class="taste-filter" value="sucré"> Sucré</label>
                <label><input type="checkbox" class="taste-filter" value="épicé"> Épicé</label>
            </div>
        </div>

        <div class="sort-container">
            <div>
                <label for="sort-select" style="font-size: 0.8rem; color: #888;">Trier par :</label>
                <select id="sort-select" class="sort-select">
                    <option value="default">Pertinence</option>
                    <option value="prix-asc">Prix croissant</option>
                    <option value="prix-desc">Prix décroissant</option>
                    <option value="ventes">Les plus commandés</option>
                </select>
            </div>
        </div>
    </section>

    <?php if ($cat_filter === 'Tous' && empty($search)): ?>
    <!-- PREMIÈRE CATÉGORIE : NOS MENUS & FORMULES -->
    <h3 class="section-subtitle">Nos Menus & Formules</h3>
    <div class="menu-container" style="margin-bottom: 40px;">
        <!-- Le Menu Mystère (Inclus dans la catégorie Menus) -->
        <div class="menu-card mystery-card">
            <div class="mystery-icon-container">?</div>
            <div class="card-body">
                <span class="badge">SURPRISE</span>
                <h3>Le Menu Mystère</h3>
                <p>Faites confiance à l'instinct de Gustavo. Un plat, un accompagnement et un dessert choisis au hasard pour vous.</p>
                
                <div class="card-footer">
                    <span class="price">12,50€</span>
                    <form action="../TRAITEMENTS/ajouter_panier.php" method="POST" class="add-form">
                        <input type="hidden" name="nom" value="Menu Mystère">
                        <input type="hidden" name="prix" value="12.50">
                        <button type="submit" class="add-btn" style="border-radius: 8px;">TENTER MA CHANCE</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Les Formules classiques -->
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
    <hr class="separator" style="max-width: 1200px; margin: 40px auto;">
    <?php endif; ?>

    <!-- SECONDE CATÉGORIE : PLATS À LA CARTE -->
    <h3 class="section-subtitle">Nos Plats à la carte</h3>
    <div id="plats-container" class="menu-container">
        <!-- Rempli en JS -->
    </div>
</main>

<?php include '../LIB/footer.php'; ?>

<script>
let currentPlats = [];
const container = document.getElementById('plats-container');
const searchInput = document.getElementById('ajax-search');
const sortSelect = document.getElementById('sort-select');
const catButtons = document.querySelectorAll('#cat-filters .cat-btn');

async function fetchPlats() {
    container.classList.add('loading');
    
    const activeCat = document.querySelector('#cat-filters .cat-btn.active').dataset.cat;
    const diets = Array.from(document.querySelectorAll('.diet-filter:checked')).map(el => el.value).join(',');
    const tastes = Array.from(document.querySelectorAll('.taste-filter:checked')).map(el => el.value).join(',');
    
    const url = `../TRAITEMENTS/filtrer_menu.php?search=${encodeURIComponent(searchInput.value)}&categorie=${encodeURIComponent(activeCat)}&diet=${diets}&taste=${tastes}`;
    
    try {
        const response = await fetch(url);
        const data = await response.json();
        currentPlats = data.plats;
        applySortAndRender();
    } catch (error) {
        console.error("Erreur lors du filtrage :", error);
    } finally {
        container.classList.remove('loading');
    }
}

function applySortAndRender() {
    const sortVal = sortSelect.value;
    let sorted = [...currentPlats];

    if (sortVal === 'prix-asc') sorted.sort((a, b) => a.prix - b.prix);
    else if (sortVal === 'prix-desc') sorted.sort((a, b) => b.prix - a.prix);
    else if (sortVal === 'ventes') {
        // On ne garde que les coups de coeur et on les trie par nombre de ventes
        sorted = sorted.filter(p => (p.tags || []).includes('coup de coeur'));
        sorted.sort((a, b) => b.ventes - a.ventes);
    }

    renderPlats(sorted);
}

function renderPlats(plats) {
    if (plats.length === 0) {
        container.innerHTML = '<p class="no-results">Aucun plat ne correspond à vos critères.</p>';
        return;
    }

    container.innerHTML = plats.map(p => `
        <div class="menu-card">
            <img src="${p.image || '../IMAGES/default.png'}" alt="${p.nom}">
            <div class="card-body">
                <div class="flex-card-header">
                    <span class="badge">${p.cat}</span>
                    <div class="tags-container">
                        ${(p.tags || []).map(t => `<span class="tag-pill ${t === 'coup de coeur' ? 'coup-de-coeur' : ''}">${t === 'coup de coeur' ? '❤️ ' : ''}${t}</span>`).join('')}
                    </div>
                </div>
                <h3>${p.nom}</h3>
                <p>${p.desc}</p>
                
                <form action="../TRAITEMENTS/ajouter_panier.php" method="POST" class="add-form">
                    <div class="card-footer">
                        <span class="price">${parseFloat(p.prix).toFixed(2)}€</span>
                        <div class="qty-group">
                            <input type="hidden" name="nom" value="${p.nom}">
                            <input type="hidden" name="prix" value="${p.prix}">
                            <input type="number" name="quantite" value="1" min="1" class="qty-input">
                            <button type="submit" class="add-btn">AJOUTER</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    `).join('');
    
    // Réattacher les événements pour le panier
    attachCartEvents();
}

function attachCartEvents() {
    document.querySelectorAll('.add-form').forEach(form => {
        form.onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch(this.action, { 
                method: 'POST', 
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' } 
            })
                .then(res => res.json())
                .then(data => {
                    if(data.success) document.getElementById('cart-count-val').textContent = data.new_count;
                });
        };
    });
}

// Event Listeners
searchInput.addEventListener('input', fetchPlats);
sortSelect.addEventListener('change', applySortAndRender);
document.querySelectorAll('.diet-filter, .taste-filter').forEach(el => el.addEventListener('change', fetchPlats));
catButtons.forEach(btn => {
    btn.addEventListener('click', function() {
        catButtons.forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        fetchPlats();
    });
});

document.addEventListener('DOMContentLoaded', function() {
    // Premier chargement automatique des plats
    fetchPlats();
    attachCartEvents();
});
</script>

</body>
</html>
