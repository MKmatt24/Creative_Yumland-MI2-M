<?php
session_start();


// 1. Chargement des données 
$file = '../DATA/commande.json';
$all_commandes = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

// 2. Récupération de la dernière commande DE CE CLIENT PRÉCIS
$nom_complet = ($_SESSION['user']['prenom'] ?? '') . " " . ($_SESSION['user']['nom'] ?? '');
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
    <link rel="stylesheet" href="../CSS/accueil.css">
    <link rel="stylesheet" href="../CSS/menu.css">
    <meta http-equiv="refresh" content="20">
</head>
<body style="background: #0a0a0a; color: white;">

<?php include '../LIB/header.php'; ?>

<main style="padding: 60px 20px; max-width: 600px; margin: 0 auto; text-align: center; min-height: 80vh;">
    <?php if ($ma_commande): ?>
        <h2 style="color: #ff6b35; text-transform: uppercase; letter-spacing: 2px;">Suivi de votre commande</h2>
        <p style="color: #888; margin-bottom: 30px;">Référence : <strong><?= htmlspecialchars($ma_commande['id']) ?></strong></p>

        <div style="width: 100%; background: #222; height: 12px; border-radius: 10px; margin: 20px 0; overflow: hidden; border: 1px solid #333;">
            <div style="width: <?= $info_etape['progress'] ?>; background: #ff6b35; height: 100%; transition: width 0.8s ease-in-out; box-shadow: 0 0 15px rgba(255, 107, 53, 0.5);"></div>
        </div>
        
        <div style="padding: 30px; border: 1px solid #ff6b35; background: #111; border-radius: 15px; margin-top: 20px;">
            <h3 style="color: #ff6b35; font-size: 1.6rem; margin: 0;"><?= $info_etape['msg'] ?></h3>
            
            <hr style="border: 0; border-top: 1px solid #333; margin: 20px 0;">
            
            <p style="font-size: 1rem; line-height: 1.6;">
                Mode : <strong><?= ($ma_commande['type_livraison'] ?? 'immediate') === 'immediate' ? 'ASAP (Le plus vite possible)' : 'Livraison programmée' ?></strong><br>
                Heure souhaitée : <span style="color: #ff6b35; font-weight: bold;"><?= htmlspecialchars($ma_commande['horaire_souhaite'] ?? 'Dès que possible') ?></span>
            </p>

            <?php if (!empty($ma_commande['livreur_id'])): ?>
                <p style="color: #3498db; font-weight: bold; margin-top: 10px;">
                    👤 Votre livreur : <?= htmlspecialchars($ma_commande['livreur_id']) ?>
                </p>
            <?php endif; ?>
        </div>

        <div style="margin-top: 40px; text-align: left; background: #000; padding: 20px; border-radius: 10px;">
            <p style="color: #ff6b35; font-weight: bold; text-transform: uppercase; font-size: 0.8rem; margin-bottom: 10px;">Récapitulatif :</p>
            <ul style="list-style: none; padding: 0; font-size: 0.9rem; color: #ccc;">
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

<footer style="text-align: center; padding: 40px; color: #444; font-size: 0.8rem;">
    <p>&copy; 2026 LOS POLLOS HERMANOS - ALBUQUERQUE</p>
</footer>

</body>
</html>