<?php
session_start();

// Calcul du nombre d'articles pour le badge du header
$panier = $_SESSION['panier'] ?? [];
$panier_count = 0;
foreach ($panier as $item) {
    $panier_count += $item['quantite'];
}

$total = 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Panier | Los Pollos Hermanos</title>
    <link rel="stylesheet" href="../CSS/menu.css"> <style>
        /* Styles spécifiques pour le tableau du panier */
        .panier-wrapper { max-width: 900px; margin: 50px auto; padding: 20px; min-height: 60vh; }
        .panier-table { width: 100%; border-collapse: collapse; background: #1a1a1a; border-radius: 15px; overflow: hidden; margin-bottom: 30px; }
        .panier-table th { background: #242424; color: var(--orange); padding: 15px; text-align: left; text-transform: uppercase; font-size: 0.8rem; }
        .panier-table td { padding: 15px; border-bottom: 1px solid #333; color: white; }
        .total-card { background: #242424; padding: 20px; border-radius: 15px; text-align: right; border: 1px solid var(--orange); }
        .btn-retour { display: inline-block; margin-top: 20px; color: #888; text-decoration: none; transition: 0.3s; }
        .btn-retour:hover { color: var(--orange); }
    </style>
</head>
<body>

<header>
    <nav>
        <div class="logo-box">
            <a href="accueil.php"><img src="../IMAGES/logo.png" alt="Logo" class="nav-logo"></a>
        </div>
        <ul>
            <li><a href="accueil.php">Accueil</a></li>
            <li><a href="menu.php">Menu</a></li>
            <li class="panier-nav">
                <a href="panier.php" style="background: var(--orange); color: var(--noir); border-radius: 20px; padding: 5px 15px; font-weight: bold;">
                    🛒 Panier (<?= $panier_count ?>)
                </a>
            </li>
        </ul>
    </nav>
</header>

<main class="panier-wrapper">
    <h2 style="color: var(--orange); text-transform: uppercase; margin-bottom: 30px; text-align: center;">Votre Commande</h2>

    <?php if (empty($panier)): ?>
        <div style="text-align: center; padding: 50px;">
            <p style="font-size: 1.2rem; color: #888;">Votre panier est actuellement vide.</p>
            <a href="menu.php" class="add-btn" style="display: inline-block; margin-top: 20px; text-decoration: none;">VOIR LE MENU</a>
        </div>
    <?php else: ?>
        <table class="panier-table">
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Prix Unitaire</th>
                    <th>Quantité</th>
                    <th>Sous-total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($panier as $item): 
                    $sous_total = $item['prix'] * $item['quantite'];
                    $total += $sous_total;
                ?>
                <tr>
                    <td style="font-weight: bold;"><?= htmlspecialchars($item['nom']) ?></td>
                    <td><?= number_format($item['prix'], 2) ?>€</td>
                    <td>x <?= $item['quantite'] ?></td>
                    <td style="color: var(--orange); font-weight: bold;"><?= number_format($sous_total, 2) ?>€</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="total-card">
            <p style="font-size: 0.9rem; color: #888; margin-bottom: 5px;">MONTANT TOTAL À RÉGLER</p>
            <h3 style="font-size: 2rem; color: white;"><?= number_format($total, 2) ?>€</h3>
            
            <form action="paiement.php" method="POST">
                <button type="submit" class="add-btn" style="width: 100%; padding: 15px; margin-top: 20px; font-size: 1rem;">
                    PROCÉDER AU PAIEMENT SÉCURISÉ
                </button>
            </form>
        </div>
    <?php endif; ?>

    <center>
        <a href="menu.php" class="btn-retour">← Continuer mes achats</a>
    </center>
</main>

<footer>
    <p>&copy; 2026 Los Pollos Hermanos - Albuquerque, NM. Tasty is the word.</p>
</footer>

</body>
</html>