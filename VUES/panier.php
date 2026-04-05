<?php
session_start();

// Sécurité : Seul un client connecté peut accéder au panier
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'client') {
    header('Location: connexion.php');
    exit;
}

$total = 0;
if (isset($_SESSION['panier']) && !empty($_SESSION['panier'])) {
    foreach ($_SESSION['panier'] as $item) {
        $total += ($item['prix'] * $item['quantite']);
    }
}
$montant_formatte = number_format($total, 2, '.', '');
$panier_count = isset($_SESSION['panier']) ? array_sum(array_column($_SESSION['panier'], 'quantite')) : 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Panier | Los Pollos Hermanos</title>
    <link rel="stylesheet" href="../CSS/accueil.css">
    <link rel="stylesheet" href="../CSS/menu.css">
</head>
<body style="background-color: #0a0a0a; color: white;">

<header>
    <div class="header-container">
        <div class="logo-box">
            <a href="accueil.php"><img src="../IMAGES/logo.png" alt="Logo" class="nav-logo"></a>
        </div>
        <nav class="nav-links">
            <a href="accueil.php">Accueil</a>
            <a href="menu.php">Menu</a>
            <a href="panier.php" style="background-color: #ff6b35; color: white; padding: 5px 10px; border-radius: 5px;">
                🛒 Panier (<?= $panier_count ?>)
            </a>
            <a href="suivi_commande.php">Suivi</a>
            <a href="profil.php">Mon Profil</a>
            <a href="../TRAITEMENTS/deconnexion.php">Déconnexion</a>
        </nav>
    </div>
</header>

<main style="padding: 50px 20px; max-width: 800px; margin: 0 auto;">
    <h2 class="section-title" style="text-align: center; color: #ff6b35;">VOTRE PANIER</h2>

    <?php if (empty($_SESSION['panier'])): ?>
        <div style="text-align: center; padding: 50px;">
            <p>Votre panier est vide.</p>
            <a href="menu.php" class="btn-primary" style="display: inline-block; margin-top: 20px; text-decoration:none; padding:15px 30px;">VOIR LE MENU</a>
        </div>
    <?php else: ?>
        
        <div style="background: #111; border: 1px solid #333; border-radius: 10px; padding: 20px; margin-bottom: 30px;">
            <?php foreach ($_SESSION['panier'] as $item): ?>
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #222; padding: 10px 0;">
                    <span><?= htmlspecialchars($item['nom']) ?> (x<?= $item['quantite'] ?>)</span>
                    <span style="color: #ff6b35;"><?= number_format($item['prix'] * $item['quantite'], 2) ?> €</span>
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
                    <p style="font-size: 0.8rem; color: #888; margin-top: 5px;">Note : Prévoyez un délai pour la préparation.</p>
                </div>
            </div>

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

<style>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

</body>
</html>