<?php include '../LIB/authentification.php'; 

$objectif_jour = 160.00;
$solde_disponible = 0;
$historique_commandes = [];

require_once '../LIB/fonctions.php';

//Récupération des data du fichier JSON
$fichierCommandes = '../DATA/commande.json';
$commandesData = file_get_contents($fichierCommandes);
$commandes = json_decode($commandesData, true);

//Filtrage des commandes et mis à jour des récompenses du livreur
if (is_array($commandes)) {
    foreach ($commandes as $cmd) {
        $statut = strtolower($cmd['statut'] ?? '');
//Pareil que pour livraison : 
        if (($statut === 'livrée' || $statut === 'abandonnée' || $statut === 'annulée') && $cmd['livreur_id'] == $_SESSION['user_id']) {
            $cmd['statut_propre'] = ($statut === 'livrée') ? 'Livrée' : 'Annulée';
            $cmd['gain_livreur'] = floatval($cmd['gain_livreur'] ?? 0);
            
// Gestion de la date 
            $raw_date = $cmd['date_commande'] ?? date('Y-m-d H:i:s');
            $timestamp = strtotime($raw_date);
            $cmd['heure_formatee'] = date('H\hi', $timestamp);
            $cmd['date_jour'] = date('Y-m-d', $timestamp);

            $historique_commandes[] = $cmd;

//Calcul du solde
            if ($cmd['statut_propre'] === 'Livrée') {
                $solde_disponible += $cmd['gain_livreur'];
            }
        }
    }

//Tri du tableau pour avoir les commandes les plus récentes en premier
    usort($historique_commandes, function($a, $b) {
        $timeA = strtotime($a['date_commande'] ?? '0');
        $timeB = strtotime($b['date_commande'] ?? '0');
        return $timeB <=> $timeA;
    });
}

//Calcul du pourcentage pour la barre de progression
$pourcentage_objectif = ($objectif_jour > 0) ? ($solde_disponible / $objectif_jour) * 100 : 0;
if ($pourcentage_objectif > 100) {$pourcentage_objectif = 100;}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Gains - LOS POLLOS HERMANOS</title>
    <link rel="icon" type="image/png" href="../IMAGES/logo.png">
    <link rel="stylesheet" href="../CSS/rewards.css"> 
</head>
<body>

    <?php include '../LIB/header.php'; ?>

    <main>
        <section class="dashboard-section">
            <div class="dashboard-container">
                
                <div class="dashboard-header">
                    <h1>Tableau de Bord Livreur</h1>
                    <p>Suivez vos revenus et votre historique de courses.</p>
                </div>

                <hr class="divider">

                <div class="dashboard-grid">
                    
                    <div class="earnings-column">
                        <div class="balance-card">
                            <span class="balance-label">Solde Disponible</span>
                            <h2 class="balance-amount"><?= number_format($solde_disponible, 2, ',', ' ') ?> €</h2>
                            <p class="balance-subtitle">Mise à jour à l'instant</p>
                            
                            <div class="goal-tracker">
                                <div class="goal-labels">
                                    <span>Objectif du jour</span>
                                    <span><?= number_format($objectif_jour, 2, ',', ' ') ?> €</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?= round($pourcentage_objectif) ?>%;"></div>
                                </div>
                            </div>

                            <button class="withdraw-btn">💸 Retirer mes gains</button>
                        </div>
                    </div>

                    <div class="history-column">
                        <h3>Historique de livraisons</h3>
                        <div class="history-list">
                            
                            <?php if (count($historique_commandes) > 0): ?>
                                <?php foreach ($historique_commandes as $cmd): ?>
                                    <?php 
                                        $estLivree = ($cmd['statut_propre'] === 'Livrée');
                                        $classeStatus = $estLivree ? 'status-success' : 'status-cancelled';
                                        $icone = $estLivree ? '✅' : '❌';
                                        $texteStatut = $estLivree ? '' : ' • Annulée';
                                        $classePrix = $estLivree ? 'earned' : 'missed';
                                        $gain = $estLivree ? "+ " . number_format($cmd['gain_livreur'], 2, ',', ' ') . " €" : number_format($cmd['gain_livreur'], 2, ',', ' ') . " €";
                                        $affichageHeure = formatHeureAffichage($cmd['date_jour'], $cmd['heure_formatee']);
                                    ?>
                                    
                                    <div class="history-card <?= $classeStatus ?>">
                                        <div class="history-icon"><?= $icone ?></div>
                                        <div class="history-details">
                                            <h4>Commande #<?= htmlspecialchars($cmd['id']) ?></h4>
                                            <span class="history-time"><?= $affichageHeure ?><?= $texteStatut ?></span>
                                        </div>
                                        <div class="history-price"><span class="<?= $classePrix ?>"><?= $gain ?></span></div>
                                    </div>

                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>Aucun historique de livraison pour le moment.</p>
                            <?php endif; ?>

                        </div>
                    </div>

                </div>
            </div>
        </section>
    </main>

    <?php include '../LIB/footer.php'; ?>

</body>
<script>
    const menuToggle = document.querySelector('.menu-toggle');
    const navMenu = document.querySelector('nav ul');

    menuToggle.addEventListener('click', () => {
        menuToggle.classList.toggle('active');
        navMenu.classList.toggle('active');
    });

    const navLinks = document.querySelectorAll('nav ul li a');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            menuToggle.classList.remove('active');
            navMenu.classList.remove('active');
        });
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('nav')) {
            menuToggle.classList.remove('active');
            navMenu.classList.remove('active');
        }
    });
</script>
</html>
