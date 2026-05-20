<?php
session_start();

// 1. Sécurité : Seul un client connecté peut accéder au panier
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'client') {
    header('Location: connexion.php');
    exit;
}

// 2. Calcul du total et du compteur
$total = 0;
$panier = $_SESSION['panier'] ?? [];

if (!empty($panier)) {
    foreach ($panier as $item) {
        $total += (($item['prix'] ?? 0) * ($item['quantite'] ?? 0));
    }
}
$montant_formatte = number_format($total, 2, '.', '');

// 3. Réduction si coupon
$reduction = 0;
if (isset($_SESSION['coupon'])) {
    if ($_SESSION['coupon']['type'] === 'pourcentage') {
        $reduction = $total * ($_SESSION['coupon']['valeur'] / 100);
    } else {
        $reduction = $_SESSION['coupon']['valeur'];
    }
}
$total_final = max(0, $total - $reduction); // Le total ne peut pas être négatif
$montant_formatte = number_format($total_final, 2, '.', '');

// Chargement des données de référence pour comparer les ingrédients
$menu_data = json_decode(file_get_contents('../DATA/menu.json'), true);
$plats_ref = $menu_data['plats'] ?? [];

$coupon_error = '';
if (isset($_SESSION['coupon_error'])) {
    $coupon_error = $_SESSION['coupon_error'];
    unset($_SESSION['coupon_error']); // Effacer l'erreur après l'avoir affichée
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Panier | Los Pollos Hermanos</title>
    <link rel="shortcut icon" type="image/png" href="../IMAGES/logo.png">
    <link rel="icon" type="image/png" href="../IMAGES/logo.png">
    <link rel="stylesheet" href="../CSS/accueil.css">
    <link rel="stylesheet" href="../CSS/menu.css">
    <script>
    function toggleHoraire() {
        const type = document.getElementById('type_commande').value;
        const blockHoraire = document.getElementById('choix_horaire');
        blockHoraire.style.display = (type === 'programmee') ? 'block' : 'none';
    }

    /**
     * Met à jour la quantité d'un article dans le panier
     * @param {number} index - L'index de l'article dans la session
     * @param {string} action - 'plus' ou 'moins'
     */
    function updateQty(index, action) {
        const formData = new FormData();
        formData.append('index', index);
        formData.append('action', action);

        fetch('../TRAITEMENTS/modifier_panier.php', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(error => console.error('Erreur:', error));
    }
    </script>
</head>
<body class="page-dark">

<?php include '../LIB/header.php'; ?>

    <div class="theme-switcher-container">
        <button onclick="cycleTheme()" id="theme-toggle-btn" class="theme-switcher-btn">
            🎨 Changer de style
        </button>
    </div>
<main class="container-narrow">
    <h2 class="section-title">VOTRE PANIER</h2>

    <?php if (isset($_SESSION['modification_id'])): ?>
        <div class="modification-info-box">
            <p><strong>Mode Modification :</strong> Vous modifiez la commande #<?= $_SESSION['modification_id'] ?></p>
        </div>
    <?php endif; ?>

    <?php if (empty($panier)): ?>
        <div class="empty-cart">
            <div class="empty-cart-icon">🍗</div>
            <h3>Votre panier est vide</h3>
            <p>On dirait que Gustavo attend votre commande. Ne le faites pas patienter, l'excellence n'attend pas !</p>
            <a href="menu.php" class="btn-primary">DÉCOUVRIR LA CARTE</a>
        </div>
    <?php else: ?>
        
        <div class="summary-box">
            <?php foreach ($panier as $index => $item): ?>
                <div class="item-row">
                    <div class="item-info">
                        <div class="cart-img-wrapper">
                            <img src="<?= htmlspecialchars($item['image'] ?? '../IMAGES/default.png') ?>" alt="<?= htmlspecialchars($item['nom']) ?>" class="cart-item-img">
                        </div>
                        <div class="item-details">
                            <div class="item-main-info">
                                <span><?= htmlspecialchars($item['nom'] ?? 'Produit') ?></span>
                                
                                <div class="cart-qty-selector">
                                    <button type="button" class="cart-qty-btn" onclick="updateQty(<?= $index ?>, 'moins')">-</button>
                                    <span class="cart-qty-val"><?= $item['quantite'] ?? 1 ?></span>
                                    <button type="button" class="cart-qty-btn" onclick="updateQty(<?= $index ?>, 'plus')">+</button>
                                </div>
                                <span class="text-orange"><?= number_format(($item['prix'] ?? 0) * ($item['quantite'] ?? 1), 2) ?> €</span>
                            </div>

                        <?php 
                        $mods = $item['modifications'] ?? [];
                        $retraits = [];
                        $ajouts = [];
                        
                        // On cherche le plat original dans le JSON pour identifier les différences
                        foreach ($plats_ref as $p) {
                            if ($p['nom'] === ($item['nom'] ?? '')) {
                                $ing_choisis = $mods['ingredients'] ?? [];
                                // Si l'ingrédient original n'est pas dans la session, il a été retiré
                                foreach ($p['ingredients'] ?? [] as $ing_orig) {
                                    if (!in_array($ing_orig, $ing_choisis)) $retraits[] = $ing_orig;
                                }
                                // Les ajouts en session commencent par "+ " (voir ajouter_panier.php)
                                foreach ($ing_choisis as $ing) {
                                    if (strpos($ing, '+ ') === 0) $ajouts[] = str_replace('+ ', '', $ing);
                                }
                                break;
                            }
                        }
                        ?>

                        <?php if (($item['nom'] ?? '') === 'Menu Mystère' && isset($item['modifications']['composition_aleatoire'])): ?>
                            <div class="mystery-details">
                                <p>Composition aléatoire :</p>
                                <ul>
                                    <?php foreach ($item['modifications']['composition_aleatoire'] as $type_plat => $plat_choisi): ?>
                                        <li>- <?= htmlspecialchars($type_plat) ?> : <?= htmlspecialchars($plat_choisi) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php elseif (!empty($retraits) || !empty($ajouts)): ?>
                            <div class="mystery-details">
                                <?php if (!empty($retraits)): ?>
                                    <p><span style="color: #e74c3c;">Retiré :</span> <?= implode(', ', array_map('htmlspecialchars', $retraits)) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($ajouts)): ?>
                                    <p><span style="color: #2ecc71;">Ajouté :</span> <?= implode(', ', array_map('htmlspecialchars', $ajouts)) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        </div>
                    </div>
                    <form action="../TRAITEMENTS/supprimer_panier.php" method="POST" style="margin: 0;">
                        <input type="hidden" name="nom_produit" value="<?= htmlspecialchars($item['nom']) ?>">
                        <button type="submit" class="remove-item-btn" title="Supprimer cet article">🗑️</button>
                    </form>
                </div>
            <?php endforeach; ?>
            <div class="total-display">
                TOTAL : <span class="text-orange"><?= $montant_formatte ?> €</span>
            </div>
        </div>

        <div class="coupon-container">
            <label class="mb-10 text-orange">Code Promo / Coupon</label>
            
            <form action="../TRAITEMENTS/appliquer_coupon.php" method="POST" class="coupon-form">
                <input type="text" name="code_coupon" class="coupon-input" placeholder="Entrez votre code ici...">
                <button type="submit" class="coupon-btn">Appliquer</button>
            </form>

            <?php if(isset($_SESSION['coupon'])): ?>
                <div class="coupon-message">
                    <span>
                        ✅ Coupon <strong><?= $_SESSION['coupon']['valeur'] ?><?= $_SESSION['coupon']['type'] == 'pourcentage' ? '%' : '€' ?></strong> appliqué !
                    </span>
                    <a href="../TRAITEMENTS/supprimer_coupon.php" class="coupon-remove">Retirer</a>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($coupon_error)): ?>
            <div class="coupon-message error">
                <div class="error-wrapper">
                    <span class="error-icon">⚠️</span>
                    <span class="error-text"><?= htmlspecialchars($coupon_error) ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php 
            $action_form = isset($_SESSION['modification_id']) ? "../TRAITEMENTS/valider_modification.php" : "../TRAITEMENTS/pre_paiement.php";
        ?>

        <form action="<?= $action_form ?>" method="POST">
            <div class="plan-card">
                <h3>PLANIFICATION</h3>
                
                <div class="form-field">
                    <label>Type de commande :</label>
                    <select name="type_commande" id="type_commande" onchange="toggleHoraire()" class="planification-select">
                        <option value="immediate">Préparation immédiate (ASAP)</option>
                        <option value="programmee">Programmer pour plus tard...</option>
                    </select>
                </div>

                <div id="choix_horaire" class="choix-horaire">
                    <label>Heure souhaitée :</label>
                    <input type="time" name="heure_programmee" class="planification-time">
                    <p class="note-delay">Note : Prévoyez un délai pour la préparation par nos équipes.</p>
                </div>
            </div>

            <div class="plan-card">
                <h3>COMMENTAIRE AU LIVREUR</h3>
                <div class="form-field">
                    <label>Instructions de livraison (facultatif) :</label>
                    <textarea name="commentaire_livreur" class="planification-select" rows="2" placeholder="Ex: Sonnez 2 fois, laissez devant la porte..."></textarea>
                </div>
            </div>

            <div class="pay-btn-container">
                <button type="submit" class="btn-full">
                    <?php if (isset($_SESSION['modification_id'])): ?>
                        VALIDER LES MODIFICATIONS
                    <?php else: ?>
                        PROCÉDER AU PAIEMENT SÉCURISÉ (<?= $montant_formatte ?> €)
                    <?php endif; ?>
                </button>
            </div>
        </form>

    <?php endif; ?>
</main>

<?php include '../LIB/footer.php'; ?>

</body>
</html>
