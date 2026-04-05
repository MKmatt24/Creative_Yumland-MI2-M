<?php
session_start();

// 1. Chargement des données 
$file = '../DATA/commande.json';
$commandes = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

// 2. Récupération de la dernière commande du client
$ma_commande = !empty($commandes) ? end($commandes) : null;

// 3. Mapping des statuts (Logique Dashboard)
$etapes = [
    'a_preparer' => ['msg' => 'Commande reçue', 'progress' => '20%'],
    'en_cours'   => ['msg' => 'En cuisine...', 'progress' => '50%'],
    'prete'      => ['msg' => 'Votre commande est prête !', 'progress' => '80%'],
    'livraison'  => ['msg' => 'En cours de livraison', 'progress' => '95%'],
    'termine'    => ['msg' => 'Bon appétit !', 'progress' => '100%']
];

$status_actuel = $ma_commande['statut_logistique'] ?? 'a_preparer';
$info_etape = $etapes[$status_actuel];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Suivi | Los Pollos Hermanos</title>
    <link rel="stylesheet" href="../CSS/accueil.css">
    <link rel="stylesheet" href="../CSS/menu.css">
    <meta http-equiv="refresh" content="20">
</head>
<body style="background: #0a0a0a; color: white;">

<header>
    <div class="header-container">
        <div class="logo-box"><img src="../IMAGES/logo.png" class="nav-logo"></div>
        <ul class="nav-links">
            <li><a href="accueil.php">Accueil</a></li>
            <li><a href="menu.php">Menu</a></li>
            <li><a href="suivi_commande.php" style="color: #ff6b35;">Mon Suivi</a></li>
        </ul>
    </div>
</header>

<main style="padding: 60px 20px; max-width: 600px; margin: 0 auto; text-align: center;">
    <?php if ($ma_commande): ?>
        <h2 style="color: #ff6b35;">SUIVI DE COMMANDE</h2>
        <p style="color: #888;">Réf : <?= htmlspecialchars($ma_commande['id']) ?></p>

        <div style="width: 100%; background: #222; height: 10px; border-radius: 5px; margin: 40px 0 10px 0; overflow: hidden;">
            <div style="width: <?= $info_etape['progress'] ?>; background: #ff6b35; height: 100%; transition: width 0.5s ease;"></div>
        </div>
        
        <div style="padding: 20px; border: 1px solid #333; background: #111; border-radius: 10px;">
            <h3 style="color: #4CAF50; font-size: 1.5rem;"><?= $info_etape['msg'] ?></h3>
            <p style="margin-top: 10px;">
                Type : <strong><?= $ma_commande['type_livraison'] === 'immediate' ? 'ASAP' : 'Programmée' ?></strong><br>
                Heure prévue : <strong><?= htmlspecialchars($ma_commande['horaire_souhaite']) ?></strong>
            </p>
        </div>

        <div style="margin-top: 30px; text-align: left; font-size: 0.9rem; color: #666;">
            <p>Détails :</p>
            <ul>
                <?php foreach ($ma_commande['articles'] as $art): ?>
                    <li><?= htmlspecialchars($art['nom']) ?> (x<?= $art['quantite'] ?>)</li>
                <?php endforeach; ?>
            </ul>
        </div>

    <?php else: ?>
        <p>Vous n'avez pas de commande en cours.</p>
        <a href="menu.php" class="btn-primary">Commander maintenant</a>
    <?php endif; ?>
</main>

</body>
</html>