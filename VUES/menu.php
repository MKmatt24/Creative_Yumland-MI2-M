<?php
session_start();

// Définition du fuseau horaire pour correspondre à l'heure locale
date_default_timezone_set('Europe/Paris');

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
    <link rel="shortcut icon" type="image/png" href="../IMAGES/logo.png">
    <link rel="icon" type="image/png" href="../IMAGES/logo.png">
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
        <a href="panier.php" class="btn-cart-float">
            🛒 MON PANIER (<span id="cart-count-val"><?= $cart_count ?></span>)
        </a>
        <div class="hero-header-flex">
            <h2>NOS MENUS LÉGENDAIRES</h2>
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

    <?php if ($cat_filter === 'Tous'): ?>
    <div id="static-menus-section">
    <!-- PREMIÈRE CATÉGORIE : NOS MENUS & FORMULES -->
    <h3 class="section-subtitle">Nos Menus & Formules</h3>
    <div class="menu-container" style="margin-bottom: 40px;">
        <!-- Le Menu Mystère -->
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
                
                // Logique gérant les plages normales et celles traversant minuit
                $dispo = ($debut <= $fin) 
                    ? ($now >= $debut && $now <= $fin) 
                    : ($now >= $debut || $now <= $fin);
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
    </div>
    <?php endif; ?>

    <!-- SECONDE CATÉGORIE : PLATS À LA CARTE -->
    <h3 class="section-subtitle" id="plats-title">Nos Plats à la carte</h3>
    <div id="plats-container" class="menu-container">
        <!-- Rempli en JS -->
    </div>
</main>

<!-- MODALE DE PERSONNALISATION -->
<div id="plat-modal" class="modal">
    <div class="modal-content">
        <span class="close-modal" onclick="closeModal()">&times;</span>
        <div id="modal-body">
            <!-- Rempli dynamiquement au clic -->
        </div>
    </div>
</div>

<!-- NOTIFICATION AJOUT PANIER -->
<div id="cart-notification" class="cart-notification">
    Plat ajouté au panier !
</div>

<?php include '../LIB/footer.php'; ?>

<script>
let currentPlats = [];
let displayedPlats = []; // Pour stocker les plats actuellement affichés (triés/filtrés)
let currentMenus = [];
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
        currentMenus = data.menus || [];
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
    let sortedMenus = [...currentMenus];

    if (sortVal === 'prix-asc') {
        sorted.sort((a, b) => a.prix - b.prix);
        sortedMenus.sort((a, b) => a.prix - b.prix);
    }
    else if (sortVal === 'prix-desc') {
        sorted.sort((a, b) => b.prix - a.prix);
        sortedMenus.sort((a, b) => b.prix - a.prix);
    }
    else if (sortVal === 'ventes') {
        // On ne garde que les coups de coeur et on les trie par nombre de ventes
        sorted = sorted.filter(p => (p.tags || []).includes('coup de coeur'));
        sorted.sort((a, b) => b.ventes - a.ventes);
    }

    renderResults(sorted, sortedMenus);
}

function renderResults(plats, menus) {
    displayedPlats = plats; // On garde une trace pour la modale
    const searchVal = searchInput.value.trim();
    const activeCat = document.querySelector('#cat-filters .cat-btn.active').dataset.cat;
    const staticMenus = document.getElementById('static-menus-section');
    const platsTitle = document.getElementById('plats-title');

    // Gestion de la visibilité des sections statiques (Menus et Titre Plats)
    if (staticMenus) {
        staticMenus.style.display = (searchVal === '' && activeCat === 'Tous') ? 'block' : 'none';
    }
    if (platsTitle) {
        platsTitle.style.display = (searchVal === '' && activeCat === 'Tous') ? 'block' : 'none';
    }

    if (plats.length === 0 && menus.length === 0) {
        container.innerHTML = '<p class="no-results">Désolé, Gustavo n\'a rien trouvé pour cette recherche.</p>';
        return;
    }

    let html = '';

    // Affichage des menus correspondants si on est en recherche ou filtrage
    if (searchVal !== '' || activeCat !== 'Tous') {
        if (menus.length > 0) {
            html += '<h3 class="section-subtitle" style="grid-column: 1/-1; margin-left:0; padding-left:0;">Menus & Formules</h3>';
            html += menus.map(m => {
                if (m.is_mystery_menu) {
                    return `
                    <div class="menu-card mystery-card">
                        <div class="mystery-icon-container">?</div>
                        <div class="card-body">
                            <span class="badge">SURPRISE</span>
                            <h3>${m.nom}</h3>
                            <p>${m.description || ''}</p>
                            
                            <div class="card-footer">
                                <span class="price">${parseFloat(m.prix).toFixed(2)}€</span>
                                <form action="../TRAITEMENTS/ajouter_panier.php" method="POST" class="add-form">
                                    <input type="hidden" name="nom" value="${m.nom}">
                                    <input type="hidden" name="prix" value="${m.prix}">
                                    <button type="submit" class="add-btn" style="border-radius: 8px;">TENTER MA CHANCE</button>
                                </form>
                            </div>
                        </div>
                    </div>`;
                } else {
                    const now = new Date();
                    const timeStr = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
                    const hDebut = m.heure_debut || '00:00';
                    const hFin = m.heure_fin || '23:59';
                    
                    const dispo = (hDebut <= hFin) 
                        ? (timeStr >= hDebut && timeStr <= hFin) 
                        : (timeStr >= hDebut || timeStr <= hFin);
                    
                    return `
                    <div class="menu-card">
                        <div class="card-body">
                            <div class="badge-container">
                                <span class="badge">Formule</span>
                                ${m.creneau ? `<span class="badge badge-yellow">🕒 ${m.creneau}</span>` : ''}
                            </div>
                            <h3>${m.nom}</h3>
                            <p>${m.description || ''}</p>
                            <div class="composition-box">
                                <ul class="comp-list">${(m.liste_plats || []).map(item => `<li>✓ ${item}</li>`).join('')}</ul>
                            </div>
                            <div class="card-footer">
                                <span class="price">${parseFloat(m.prix).toFixed(2)}€</span>
                                <form action="../TRAITEMENTS/ajouter_panier.php" method="POST" class="add-form">
                                    <div class="qty-group">
                                        <input type="hidden" name="nom" value="${m.nom}">
                                        <input type="hidden" name="prix" value="${m.prix}">
                                        <input type="number" name="quantite" value="1" min="1" class="qty-input">
                                        <button type="submit" class="add-btn" ${!dispo ? 'disabled' : ''}>${dispo ? 'AJOUTER' : 'INDISPONIBLE'}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>`;
                }
            }).join('');
            
            if (plats.length > 0) {
                html += '<h3 class="section-subtitle" style="grid-column: 1/-1; margin-left:0; padding-left:0; margin-top:40px;">Plats à la carte trouvés</h3>';
            }
        }
    }

    container.innerHTML = html + plats.map((p, index) => `
        <div class="menu-card" onclick="openPlatModal(${index})">
            <img src="${p.image || '../IMAGES/default.png'}" alt="${p.nom || 'Plat'}">
            <div class="card-body">
                <div class="flex-card-header">
                    <span class="badge">${p.cat || 'Plat'}</span>
                    <div class="tags-container">
                        ${(p.tags || []).map(t => `<span class="tag-pill ${t === 'coup de coeur' ? 'coup-de-coeur' : ''}">${t === 'coup de coeur' ? '❤️ ' : ''}${t}</span>`).join('')}
                    </div>
                </div>
                <h3>${p.nom || 'Sans nom'}</h3>
                <p>${p.desc || ''}</p>
                
                <div class="card-footer">
                    <span class="price">${parseFloat(p.prix || 0).toFixed(2)}€</span>
                    <button class="add-btn" style="border-radius: 8px;" onclick="event.stopPropagation(); openPlatModal(${index})">DÉTAILS</button>
                </div>
            </div>
        </div>
    `).join('');
    
    // Réattacher les événements pour le panier (pour les menus statiques)
    attachCartEvents();
}

function openPlatModal(index) {
    const p = displayedPlats[index];
    const modal = document.getElementById('plat-modal');
    const body = document.getElementById('modal-body');

    // 1. Section "Enlever" (Ingrédients de base)
    const baseIngredientsHtml = (p.ingredients || []).length > 0 
        ? `<div class="customization-area">
            <p class="comp-title">Retirer des ingrédients :</p>
            <div class="ingredients-checks">
                ${p.ingredients.map(ing => `
                    <label class="check-item">
                        <input type="checkbox" name="ingredients[]" value="${ing}" checked>
                        <span class="ing-toggle">${ing}</span>
                    </label>
                `).join('')}
            </div>
           </div>`
        : '';

    // 2. Section "Ajouter" (Suppléments - par défaut pour les plats salés)
    const isSalty = ['Poulet', 'Burgers', 'Spécialités', 'Accompagnements'].includes(p.cat);
    const extras = isSalty ? ['Sauce Fromagère', 'Bacon croustillant', 'Piments Jalapeños', 'Oignons frits'] : ['Coulis Chocolat', 'Double Chantilly', 'Éclats de noisettes'];
    
    const extrasHtml = `<div class="customization-area extras-area">
        <p class="comp-title">Ajouter des suppléments :</p>
        <div class="ingredients-checks">
            ${extras.map(ex => `
                <label class="check-item check-extra">
                    <input type="checkbox" name="ingredients[]" value="+ ${ex}">
                    <span class="ing-toggle">${ex}</span>
                </label>
            `).join('')}
        </div>
    </div>`;

    body.innerHTML = `
        <div class="modal-header-plat">
            <img src="${p.image || '../IMAGES/default.png'}" class="modal-img">
            <div class="modal-titles">
                <h3>${p.nom}</h3>
                <span class="price">${parseFloat(p.prix).toFixed(2)}€</span>
            </div>
        </div>
        <p class="modal-desc">${p.desc || ''}</p>
        
        <form action="../TRAITEMENTS/ajouter_panier.php" method="POST" id="modal-add-form">
            ${baseIngredientsHtml}
            ${extrasHtml}
            <div class="modal-footer" style="margin-top:20px; text-align:center;">
                <input type="hidden" name="nom" value="${p.nom}">
                <input type="hidden" name="prix" value="${p.prix}">
                <div class="qty-group" style="justify-content:center; margin-bottom:20px;">
                    <label style="margin-right:15px; color:#aaa; font-weight:bold;">Quantité :</label>
                    <input type="number" name="quantite" value="1" min="1" class="qty-input" style="border-radius:8px; border-right:1px solid #333; width:70px;">
                </div>
                <button type="submit" class="btn-full" style="text-transform: uppercase;">Ajouter au panier</button>
            </div>
        </form>
    `;

    modal.style.display = 'flex';
    attachModalCartEvent();
}

function closeModal() {
    document.getElementById('plat-modal').style.display = 'none';
}

function attachModalCartEvent() {
    const form = document.getElementById('modal-add-form');
    form.onsubmit = function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch(this.action, { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                document.getElementById('cart-count-val').textContent = data.new_count;
                closeModal();
                // Optionnel : petite animation sur le panier
                const cartBtn = document.querySelector('.btn-cart-float');
                cartBtn.style.transform = 'scale(1.1)';
                setTimeout(() => cartBtn.style.transform = 'scale(1)', 200);
                // Afficher la notification
                const notification = document.getElementById('cart-notification');
                notification.textContent = `${formData.get('nom')} ajouté au panier !`;
                notification.classList.add('show');
                // Faire disparaître après 3 secondes
                setTimeout(() => notification.classList.remove('show'), 3000);
            }
        });
    };
}

// Fermer la modale si on clique à côté du contenu
window.onclick = function(event) {
    const modal = document.getElementById('plat-modal');
    if (event.target == modal) closeModal();
};

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
                    if(data.success) {
                        document.getElementById('cart-count-val').textContent = data.new_count;
                        // Afficher la notification aussi pour les menus
                        const notification = document.getElementById('cart-notification');
                        notification.textContent = `${formData.get('nom')} ajouté au panier !`;
                        notification.classList.add('show');
                        setTimeout(() => notification.classList.remove('show'), 3000);
                    }
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
