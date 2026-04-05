<?php
session_start();
$panier = $_SESSION['panier'] ?? [];
$total = 0;
foreach ($panier as $i) { $total += $i['prix'] * $i['quantite']; }
$montant_form = number_format($total, 2, '.', '');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="stylesheet" href="../CSS/menu.css">
    <link rel="stylesheet" href="../CSS/panier.css">
</head>
<body>
<main class="panier-container">
    <h2 class="panier-title">Votre Panier</h2>
    <table class="panier-table">
        <?php foreach ($panier as $i): ?>
        <tr>
            <td><?= htmlspecialchars($i['nom']) ?></td>
            <td>x<?= $i['quantite'] ?></td>
            <td><?= number_format($i['prix'] * $i['quantite'], 2) ?>€</td>
        </tr>
        <?php endforeach; ?>
    </table>
    <div class="total-section">
        <span class="total-label">TOTAL</span>
        <span class="total-amount"><?= $montant_form ?>€</span>
    </div>
    <form action="paiement.php" method="POST">
        <button type="submit" class="btn-pay">PAYER LA COMMANDE</button>
    </form>
</main>
</body>
</html>