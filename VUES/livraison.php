<?php include '../LIB/authentification.php';

//Vérification que l'utilisateur est bien un livreur
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'livreur') {
    header('Location: connexion.php?erreur=livreur');
    exit();
}

//Récupération des data du fichier JSON
$fichierCommandes = '../DATA/commande.json';
$commandesData = file_get_contents($fichierCommandes);
$commandes = json_decode($commandesData, true);

//Quand le livreur clique sur le bouton pour "finir" la commande
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id_commande'])) {
    $idCible = $_POST['id_commande'];
    $nouveauStatut = ($_POST['action'] === 'terminer') ? 'livrée' : 'abandonnée';

//Parcours des commandes pour modifier celle souhaité
foreach ($commandes as &$cmd) {
    if ($cmd['id'] == $idCible) {
        $cmd['statut'] = $nouveauStatut;

//Si la commande est bien livrée, on calcule et on sauvegarde le gain
        if ($nouveauStatut === 'livrée') {
//On reprend le même calcul que dans livraisons_en_attente.php
            $distanceKm = 1.5 + mt_rand(0, 6);
            $gainEstime = 2.50 + ($distanceKm * 0.80);
//On sauvegarde la clé gain_livreur
            $cmd['gain_livreur'] = $gainEstime;
        }
        break;
    }
}

//Sauvegarde du fichier JSON modifié et rafraichissement de la page pour vider le formulaire
    file_put_contents($fichierCommandes, json_encode($commandes, JSON_PRETTY_PRINT));
    header('Location: livraison.php');
    exit();
}

//Affichage de la commande en cours
$commandeEnCours = null;

foreach ($commandes as $cmd) {
    if (($cmd['statut'] ?? '') === 'livraison' && ($cmd['livreur_id'] ?? '') == $_SESSION['user_id']) {
        $commandeEnCours = $cmd;
        break;
    }
}

$clientInfo = null;
if ($commandeEnCours && isset($commandeEnCours['user_id'])) {
    $usersData = json_decode(file_get_contents('../DATA/users.json'), true);
    foreach ($usersData as $user) {
        if ($user['id'] == $commandeEnCours['user_id']) {
            $clientInfo = $user;
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Livraison en cours - LOS POLLOS HERMANOS</title>
    <link rel="shortcut icon" type="image/png" href="../IMAGES/logo.png">
    <link rel="icon" type="image/png" href="../IMAGES/logo.png">
    <link rel="stylesheet" href="../CSS/livraison.css">
    <script defer>
    document.addEventListener('DOMContentLoaded', function() {
        //Menu mobile
        const menuToggle = document.querySelector('.menu-toggle');
        const navMenu = document.querySelector('nav ul');

        const cible = document.getElementById('zone-livraison');
        if (cible) {
            cible.scrollIntoView({ behavior: 'smooth' });
        }

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

        //Action des boutons terminer et abandonner

        const btnTerminer = document.getElementById('btn-terminer');
        const btnAbandonner = document.getElementById('btn-abandonner');

        if (btnTerminer) {
            btnTerminer.addEventListener('click', () => {
                btnTerminer.disabled = true;
                btnTerminer.textContent = '⏳ Envoi...';

                envoyerAction('terminer', btnTerminer.dataset.id)
                    .then(data => {
                        if (data.success) {
                            afficherToast('Livraison terminée ! Gain : ' + data.gain_livreur.toFixed(2) + ' €', 'success');
                            setTimeout(() => {
                                window.location.href = 'livraison_fini.php?id=' + btnTerminer.dataset.id;
                            }, 1500);
                        } else {
                            afficherToast(data.message, 'error');
                            btnTerminer.disabled = false;
                            btnTerminer.textContent = '✅ Livraison Terminée';
                        }
                    });
            });
        }

        if (btnAbandonner) {
            btnAbandonner.addEventListener('click', () => {
                if (!confirm('Êtes-vous sûr de vouloir abandonner cette livraison ?')) return;

                btnAbandonner.disabled = true;

                envoyerAction('abandonner', btnAbandonner.dataset.id)
                    .then(data => {
                        if (data.success) {
                            afficherToast('Livraison annulée.', 'success');
                            setTimeout(() => {
                                window.location.href = 'livraisons_en_attente.php';
                            }, 1200);
                        } else {
                            afficherToast(data.message, 'error');
                            btnAbandonner.disabled = false;
                        }
                    });
            });
        }

        function envoyerAction(action, idCommande) {
            const formData = new FormData();
            formData.append('action', action);
            formData.append('id_commande', idCommande);

            return fetch('../TRAITEMENTS/update_livraison.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .catch(() => {
                afficherToast('Erreur réseau. Vérifiez votre connexion.', 'error');
                return { success: false };
            });
        }

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
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 3500);
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
        <?php if ($commandeEnCours): ?>
            <section class="delivery-section" id="zone-livraison">
                
                <div class="delivery-header">
                    <h2>Commande #<?= htmlspecialchars($commandeEnCours['id']) ?></h2>
                    <span class="status-badge">En cours de livraison 🛵</span>
                </div>

                <div class="delivery-container">
                    <div class="customer-block">
                        <div class="customer-info">
                            <h3><?= htmlspecialchars($commandeEnCours['client']) ?></h3>
                        </div>
                        <a href="tel:0000000000" class="phone-btn" aria-label="Appeler le client">📞</a>
                    </div>

                    <hr class="divider">

                    <div class="address-block">
                        <label>📍 Adresse de livraison</label>
                        <p class="address-text"><?= htmlspecialchars($commandeEnCours['adresse']) ?></p>
                        <a href="https://maps.google.com/?q=<?= urlencode($commandeEnCours['adresse']) ?>" target="_blank" class="gps-btn">
                            🗺️ Ouvrir le GPS
                        </a>
                    </div>

                    <div class="access-grid">
                        <div class="access-item">
                            <span class="icon">🔢</span>
                            <span class="label">Digicode</span>
                            <span class="value"><?= htmlspecialchars($clientInfo['code_interphone'] ?? 'N/A') ?></span>
                        </div>
                        <div class="access-item">
                            <span class="icon">🏢</span>
                            <span class="label">Étage</span>
                            <span class="value"><?= htmlspecialchars($commandeEnCours['etage'] ?? 'N/A') ?></span>
                        </div>
                        <div class="access-item">
                            <span class="icon">🚪</span>
                            <span class="label">Appartement</span>
                            <span class="value"><?= htmlspecialchars($commandeEnCours['appartement'] ?? 'N/A') ?></span>
                        </div>
                    </div>

                    <div class="comment-block">
                        <label>💬 Commentaire client</label>
                        <div class="comment-box">
                            <?= htmlspecialchars($commandeEnCours['commentaire'] ?? 'Aucun commentaire laissé par le client.') ?>
                        </div>
                    </div>

                    <div class="delivery-actions">
                        <button type="button" id="btn-abandonner" class="abandon-btn" data-id="<?= $commandeEnCours['id'] ?>">❌</button>

                        <button type="button" id="btn-terminer" class="finish-btn finish-btn--large" data-id="<?= $commandeEnCours['id'] ?>">✅ Livraison Terminée</button>
                    </div>

                </div>
            </section>

        <?php else: ?>
            <section class="delivery-section delivery-section--empty">
                <h2>Aucune livraison en cours</h2>
                <p>Vous n'avez aucune commande attribuée pour le moment.</p>
                <a href="livraisons_en_attente.php" class="finish-btn finish-btn--link">📡 Voir les commandes en attente</a>
            </section>
        <?php endif; ?>
    </main>

    <?php include '../LIB/footer.php'; ?>

</body>
</html>
