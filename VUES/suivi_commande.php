<?php
session_start();


// 1. Chargement des données 
$file = '../DATA/commande.json';
$all_commandes = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

// 2. Récupération de la dernière commande DE CE CLIENT PRÉCIS
$nom_complet = ($_SESSION['prenom'] ?? '') . " " . ($_SESSION['nom'] ?? '');
$mes_commandes = array_filter($all_commandes, function($c) use ($nom_complet) {
    return ($c['client'] ?? '') === $nom_complet;
});

// On prend la toute dernière de la liste filtrée
$ma_commande = !empty($mes_commandes) ? end($mes_commandes) : null;

// 3. Mapping des statuts (doit matcher avec update_statut.php)
$etapes = [
    'a_preparer'  => ['msg' => 'Commande reçue - En attente', 'progress' => '20%'],
    'paye'        => ['msg' => 'Paiement confirmé', 'progress' => '30%'],
    'preparation' => ['msg' => 'Le chef Gus est en cuisine... 🍳', 'progress' => '60%'],
    'livraison'   => ['msg' => 'En route pour la livraison ! 🚚', 'progress' => '90%'],
    'livree'      => ['msg' => 'Livrée ! Bon appétit ! 🍗', 'progress' => '100%']
];

// Récupération du statut actuel (on vérifie les deux clés possibles)
$status_actuel = $ma_commande['statut'] ?? ($ma_commande['statut_logistique'] ?? 'a_preparer');
$info_etape = $etapes[$status_actuel] ?? ['msg' => 'Traitement en cours...', 'progress' => '10%'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Suivi | Los Pollos Hermanos</title>
    <link rel="shortcut icon" type="image/png" href="../IMAGES/logo.png">
    <link rel="icon" type="image/png" href="../IMAGES/logo.png">
    <link rel="stylesheet" href="../CSS/accueil.css">
    <link rel="stylesheet" href="../CSS/menu.css">
    <meta http-equiv="refresh" content="20">
</head>
<body class="page-dark">

<?php include '../LIB/header.php'; ?>

<main class="container-tracking">
    <?php if ($ma_commande): ?>
        <h2 class="text-orange">Suivi de votre commande</h2>
        <p class="mb-30">Référence : <strong><?= htmlspecialchars($ma_commande['id']) ?></strong></p>

        <div class="progress-container">
            <div class="progress-bar-fill" style="width: <?= $info_etape['progress'] ?>;"></div>
        </div>
        
        <?php if ($status_actuel === 'paye' || $status_actuel === 'a_preparer'): ?>
            <div style="margin-bottom: 20px;">
                <a href="../TRAITEMENTS/preparer_modification.php?id=<?= urlencode($ma_commande['id']) ?>" class="btn-secondary-full" style="width: auto; padding: 10px 20px;">✍️ MODIFIER MA COMMANDE</a>
            </div>
        <?php endif; ?>

        <div class="status-box">
            <h3><?= $info_etape['msg'] ?></h3>
            
            <hr class="separator">
            
            <p>
                Mode : <strong><?= ($ma_commande['type_livraison'] ?? 'immediate') === 'immediate' ? 'ASAP (Le plus vite possible)' : 'Livraison programmée' ?></strong><br>
                Heure souhaitée : <span class="text-orange"><strong><?= htmlspecialchars($ma_commande['horaire_souhaite'] ?? 'Dès que possible') ?></strong></span>
            </p>

            <?php if (!empty($ma_commande['livreur_id'])): ?>
                <p class="mt-10" style="color: #3498db; font-weight: bold;">
                    👤 Votre livreur : <?= htmlspecialchars($ma_commande['livreur_id']) ?>
                </p>
            <?php endif; ?>
        </div>

        <div class="recap-box">
            <p class="text-orange mb-10">Récapitulatif :</p>
            <ul class="comp-list">
                <?php foreach (($ma_commande['articles'] ?? []) as $art): ?>
                    <li style="padding: 5px 0; border-bottom: 1px solid #111;">
                        <span style="color: white;"><?= htmlspecialchars($art['nom']) ?></span> 
                        <span style="float: right;">x<?= $art['quantite'] ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <p style="text-align: right; margin-top: 15px; font-weight: bold;">
                Total réglé : <?= number_format(($ma_commande['prix_total'] ?? 0), 2) ?> €
            </p>
        </div>

    <?php else: ?>
        <div style="padding: 50px 0;">
            <div style="font-size: 4rem; margin-bottom: 20px;">🍗</div>
            <p style="font-size: 1.2rem; color: #888;">Vous n'avez aucune commande active pour le moment.</p>
            <a href="menu.php" class="btn-primary" style="display: inline-block; margin-top: 25px; padding: 15px 35px; text-decoration: none; background: #ff6b35; color: black; font-weight: bold; border-radius: 30px;">VOIR LA CARTE</a>
        </div>
    <?php endif; ?>
</main>

<?php include '../LIB/footer.php'; ?>

</body>
</html>
