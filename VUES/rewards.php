<?php include '../LIB/authentification.php';

//Vérification que l'utilisateur est bien un livreur
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'livreur') {
    header('Location: connexion.php?erreur=livreur');
    exit();
}

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
        if (($statut === 'livrée' || $statut === 'livree' || $statut === 'abandonnée' || $statut === 'annulée') && $cmd['livreur_id'] == $_SESSION['user_id']) {
            $cmd['statut_propre'] = ($statut === 'livrée' || $statut === 'livree') ? 'Livrée' : 'Annulée';
            $cmd['gain_livreur'] = floatval($cmd['gain_livreur'] ?? 0);
            
// Gestion de la date
            $raw_date = $cmd['date_commande'] ?? $cmd['date'] ?? date('Y-m-d H:i:s');
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
        $timeA = strtotime($a['date_commande'] ?? $a['date'] ?? '0');
        $timeB = strtotime($b['date_commande'] ?? $b['date'] ?? '0');
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
    <link rel="shortcut icon" type="image/png" href="../IMAGES/logo.png">
    <link rel="icon" type="image/png" href="../IMAGES/logo.png">
    <link rel="stylesheet" href="../CSS/rewards.css">
    <script defer>
    document.addEventListener('DOMContentLoaded', function() {
        //Menu mobile
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

        //Retraits des gains

        const withdrawBtn = document.querySelector('.withdraw-btn');
        if (withdrawBtn) {
            withdrawBtn.addEventListener('click', () => {
                const soldeEl = document.querySelector('.balance-amount');
                const solde = parseFloat(soldeEl.textContent.replace(',', '.').replace(/[^\d.]/g, ''));

                if (solde <= 0) {
                    afficherToast('Aucun gain à retirer.', 'error');
                    return;
                }

                if (!confirm('Retirer ' + solde.toFixed(2) + ' € vers votre compte bancaire ?')) return;

                withdrawBtn.disabled = true;
                withdrawBtn.textContent = '⏳ Transfert en cours...';

                const formData = new FormData();
                formData.append('action', 'retirer');
                formData.append('montant', solde);

                fetch('../TRAITEMENTS/retrait_gains.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        soldeEl.textContent = '0,00 €';
                        const progressFill = document.querySelector('.progress-fill');
                        if (progressFill) progressFill.style.width = '0%';
                        afficherToast('Transfert de ' + solde.toFixed(2) + ' € effectué !', 'success');
                        withdrawBtn.textContent = '✅ Transfert effectué';
                    } else {
                        afficherToast(data.message || 'Erreur lors du retrait.', 'error');
                        withdrawBtn.disabled = false;
                        withdrawBtn.textContent = '💸 Retirer mes gains';
                    }
                })
                .catch(() => {
                    afficherToast('Erreur réseau.', 'error');
                    withdrawBtn.disabled = false;
                    withdrawBtn.textContent = '💸 Retirer mes gains';
                });
            });
        }

        //Actualisation automatique toutes les 30 secondes

        setInterval(() => {
            fetch('rewards.php')
                .then(res => res.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    const newBalance = doc.querySelector('.balance-amount');
                    const currentBalance = document.querySelector('.balance-amount');
                    if (newBalance && currentBalance && newBalance.textContent !== currentBalance.textContent) {
                        currentBalance.textContent = newBalance.textContent;
                        currentBalance.style.transition = 'color 0.3s';
                        currentBalance.style.color = '#2ecc71';
                        setTimeout(() => { currentBalance.style.color = ''; }, 2000);
                    }

                    const newHistory = doc.querySelector('.history-list');
                    const currentHistory = document.querySelector('.history-list');
                    if (newHistory && currentHistory) {
                        currentHistory.innerHTML = newHistory.innerHTML;
                    }

                    const newProgress = doc.querySelector('.progress-fill');
                    const currentProgress = document.querySelector('.progress-fill');
                    if (newProgress && currentProgress) {
                        currentProgress.style.width = newProgress.style.width;
                    }
                })
                .catch(() => {});
        }, 30000);

        //Toast de notification

        function afficherToast(message, type) {
            const existing = document.querySelector('.toast-notification');
            if (existing) existing.remove();

            const toast = document.createElement('div');
            toast.className = 'toast-notification toast-' + type;
            toast.textContent = message;
            toast.style.cssText = 'position:fixed;bottom:30px;left:50%;transform:translateX(-50%) translateY(20px);padding:16px 28px;border-radius:10px;color:#fff;font-weight:bold;font-size:1rem;z-index:9999;opacity:0;transition:opacity 0.3s,transform 0.3s;box-shadow:0 4px 12px rgba(0,0,0,0.3);';
            toast.style.background = type === 'success' ? '#2ecc71' : '#e74c3c';
            document.body.appendChild(toast);
            setTimeout(() => { toast.style.opacity = '1'; toast.style.transform = 'translateX(-50%) translateY(0)'; }, 10);
            setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 300); }, 3500);
        }
    });
    </script>
</head>
<body>

    <?php include '../LIB/header.php'; ?>

    <div class="theme-switcher-container">
        <button onclick="cycleTheme()" id="theme-toggle-btn" class="theme-switcher-btn">
            🎨 Changer de style
        </button>
    </div>
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
</html>
