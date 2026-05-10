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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Panier | Los Pollos Hermanos</title>
    <link rel="stylesheet" href="../CSS/accueil.css">
    <link rel="stylesheet" href="../CSS/menu.css">
</head>
<body class="page-dark">

<?php include '../LIB/header.php'; ?>

<main class="container-narrow">
    <h2 class="section-title">VOTRE PANIER</h2>

    <?php if (isset($_SESSION['modification_id'])): ?>
        <div class="modification-info-box">
            <p><strong>Mode Modification :</strong> Vous modifiez la commande #<?= $_SESSION['modification_id'] ?></p>
        </div>
    <?php endif; ?>

    <?php if (empty($panier)): ?>
        <div class="empty-cart">
            <p>Votre panier est vide.</p>
            <a href="menu.php" class="btn-primary mt-20">VOIR LE MENU</a>
        </div>
    <?php else: ?>
        
        <div class="summary-box">
            <?php foreach ($panier as $item): ?>
                <div class="item-row">
                    <div class="item-info">
                        <span><?= htmlspecialchars($item['nom'] ?? 'Produit') ?> (x<?= $item['quantite'] ?? 1 ?>)</span>
                        <span class="text-orange"><?= number_format(($item['prix'] ?? 0) * ($item['quantite'] ?? 1), 2) ?> €</span>
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

<script>
function toggleHoraire() {
    const type = document.getElementById('type_commande').value;
    const blockHoraire = document.getElementById('choix_horaire');
    blockHoraire.style.display = (type === 'programmee') ? 'block' : 'none';
}
</script>

<?php include '../LIB/footer.php'; ?>

</body>
</html>
