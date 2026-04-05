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
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body style="background-color: #0a0a0a; color: white;">

<?php include '../LIB/header.php'; ?>

<main style="padding: 50px 20px; max-width: 800px; margin: 0 auto;">
    <h2 class="section-title" style="text-align: center; color: #ff6b35;">VOTRE PANIER</h2>

    <?php if (empty($panier)): ?>
        <div style="text-align: center; padding: 50px;">
            <p>Votre panier est vide.</p>
            <a href="menu.php" class="btn-primary" style="display: inline-block; margin-top: 20px; text-decoration:none; padding:15px 30px; background:#ff6b35; border-radius:5px; color:white; font-weight:bold;">VOIR LE MENU</a>
        </div>
    <?php else: ?>
        
        <div style="background: #111; border: 1px solid #333; border-radius: 10px; padding: 20px; margin-bottom: 30px;">
            <?php foreach ($panier as $item): ?>
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #222; padding: 10px 0;">
                    <span><?= htmlspecialchars($item['nom'] ?? 'Produit') ?> (x<?= $item['quantite'] ?? 1 ?>)</span>
                    <span style="color: #ff6b35;"><?= number_format(($item['prix'] ?? 0) * ($item['quantite'] ?? 1), 2) ?> €</span>
                </div>
            <?php endforeach; ?>
            <div style="text-align: right; margin-top: 20px; font-size: 1.2rem; font-weight: bold;">
                TOTAL : <span style="color: #ff6b35;"><?= $montant_formatte ?> €</span>
            </div>
        </div>

        <form action="../TRAITEMENTS/pre_paiement.php" method="POST">
            <div style="background: #111; padding: 25px; border: 1px solid #ff6b35; border-radius: 10px; margin-bottom: 30px;">
                <h3 style="color: #ff6b35; margin-top: 0; margin-bottom: 20px;">PLANIFICATION</h3>
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 10px; font-weight: bold;">Type de commande :</label>
                    <select name="type_commande" id="type_commande" onchange="toggleHoraire()" 
                            style="width: 100%; padding: 12px; background: #000; color: white; border: 1px solid #333; border-radius: 5px; font-size: 1rem;">
                        <option value="immediate">Préparation immédiate (ASAP)</option>
                        <option value="programmee">Programmer pour plus tard...</option>
                    </select>
                </div>

                <div id="choix_horaire" style="display: none; animation: fadeIn 0.3s;">
                    <label style="display: block; margin-bottom: 10px; font-weight: bold;">Heure souhaitée :</label>
                    <input type="time" name="heure_programmee" 
                           style="width: 100%; padding: 12px; background: #000; color: white; border: 1px solid #4CAF50; border-radius: 5px; font-size: 1rem;">
                    <p style="font-size: 0.8rem; color: #888; margin-top: 5px;">Note : Prévoyez un délai pour la préparation par nos équipes.</p>
                </div>
            </div>

            <form action="../TRAITEMENTS/appliquer_coupon.php" method="POST" style="margin: 20px 0; display: flex; gap: 10px;">
                <input type="text" name="code_coupon" placeholder="Code promo" style="padding: 10px; flex-grow: 1;">
                <button type="submit" class="btn-secondary" style="background: #444; color: white; border: none; padding: 10px 20px; cursor: pointer;">Appliquer</button>
            </form>

            <?php if(isset($_SESSION['coupon'])): ?>
                <p style="color: #2ecc71;">Coupon appliqué : -<?= $_SESSION['coupon']['valeur'] ?><?= $_SESSION['coupon']['type'] == 'pourcentage' ? '%' : '€' ?> 
                <a href="../TRAITEMENTS/supprimer_coupon.php" style="color: #e74c3c; font-size: 0.8rem;">(Retirer)</a></p>
            <?php endif; ?>

            <div style="text-align: center;">
                <button type="submit" class="btn-primary" style="width: 100%; padding: 20px; font-size: 1.2rem; background: #ff6b35; border: none; cursor: pointer; font-weight: bold; border-radius: 5px; color: white;">
                    PROCÉDER AU PAIEMENT SÉCURISÉ (<?= $montant_formatte ?> €)
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

<footer style="text-align: center; padding: 40px; color: #555;">
    <p>&copy; 2026 Los Pollos Hermanos - Albuquerque. Tous droits réservés.</p>
</footer>

</body>
</html>