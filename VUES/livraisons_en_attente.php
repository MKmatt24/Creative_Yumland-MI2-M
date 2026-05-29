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

//Si le livreur a déjà une commande en cours, on le redirige vers livraison.php
foreach ($commandes as $cmd) {
    if (($cmd['statut'] ?? '') === 'livraison' && ($cmd['livreur_id'] ?? '') == $_SESSION['user_id']) {
        header('Location: livraison.php');
        exit();
    }
}

//Quand le livreur accepte une course (fallback sans JS)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id_commande'])) {
    verifier_csrf();
    if ($_POST['action'] === 'accepter') {
        $idCible = $_POST['id_commande'];

//Recherche de la commande pour modfier son statut
        foreach ($commandes as &$cmd) {
            if ($cmd['id'] == $idCible) {
                $cmd['statut'] = 'livraison';
                $cmd['livreur_id'] = $_SESSION['user_id']; 
                break;
            }
        }

//Sauvegarde du fichier JSON modifié et redirection vers la page de livraison
        file_put_contents($fichierCommandes, json_encode($commandes, JSON_PRETTY_PRINT));
        header('Location: livraison.php');
        exit();
    }
}

//Recherche des courses en préparation (disponibles pour les livreurs)
$offres = [];

foreach ($commandes as $cmd) {
    if (($cmd['statut'] ?? '') === 'preparation') {
        $offres[] = $cmd;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Courses disponibles pour les livreurs Los Pollos Hermanos">
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <title>Courses Disponibles - LOS POLLOS HERMANOS</title>
    <link rel="shortcut icon" type="image/png" href="../IMAGES/logo.png">
    <link rel="icon" type="image/png" href="../IMAGES/logo.png">
    <link rel="stylesheet" href="../CSS/livraisons_en_attente.css">
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

        //Acceptation d'une course

        function bindAccepter(btn) {
            btn.addEventListener('click', function() {
                const idCommande = this.dataset.id;
                const card = this.closest('.offer-card');

                this.disabled = true;
                this.textContent = '⏳ Acceptation...';

                const formData = new FormData();
                formData.append('action', 'accepter');
                formData.append('id_commande', idCommande);
                formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').content);

                fetch('../TRAITEMENTS/update_livraison.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        card.style.transition = 'all 0.4s ease';
                        card.style.transform = 'scale(0.95)';
                        card.style.opacity = '0.5';
                        card.style.border = '2px solid #2ecc71';
                        afficherToast('Course acceptée ! Redirection...', 'success');
                        setTimeout(() => { window.location.href = 'livraison.php'; }, 1500);
                    } else {
                        afficherToast(data.message || 'Erreur.', 'error');
                        btn.disabled = false;
                        btn.textContent = 'Accepter la course';
                    }
                })
                .catch(() => {
                    afficherToast('Erreur réseau. Vérifiez votre connexion.', 'error');
                    btn.disabled = false;
                    btn.textContent = 'Accepter la course';
                });
            });
        }

        document.querySelectorAll('.btn-accepter').forEach(bindAccepter);

        //Rafraîchissement automatique de la liste des courses toutes les 15 secondes
        setInterval(() => {
            fetch('livraisons_en_attente.php')
                .then(res => res.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const nouvelleGrille = doc.querySelector('.offers-grid');
                    const grilleActuelle = document.querySelector('.offers-grid');
                    if (nouvelleGrille && grilleActuelle) {
                        const nbActuel = grilleActuelle.querySelectorAll('.offer-card').length;
                        const nbNouveau = nouvelleGrille.querySelectorAll('.offer-card').length;
                        if (nbNouveau !== nbActuel) {
                            grilleActuelle.innerHTML = nouvelleGrille.innerHTML;
                            document.querySelectorAll('.btn-accepter').forEach(bindAccepter);
                        }
                    }
                })
                .catch(() => {});
        }, 15000);

        //Bouton de simulation de commande (pour les tests)

        const btnSimuler = document.getElementById('btn-simuler');
        if (btnSimuler) {
            btnSimuler.addEventListener('click', () => {
                btnSimuler.disabled = true;
                btnSimuler.textContent = '⏳ Génération...';

                fetch('../TRAITEMENTS/generer_commande.php')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        afficherToast(data.message, 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        afficherToast(data.message || 'Erreur.', 'error');
                    }
                    btnSimuler.disabled = false;
                    btnSimuler.textContent = '🎲 Simuler une commande';
                })
                .catch(() => {
                    afficherToast('Erreur réseau.', 'error');
                    btnSimuler.disabled = false;
                    btnSimuler.textContent = '🎲 Simuler une commande';
                });
            });
        }

        function afficherToast(message, type) {
            const existing = document.querySelector('.toast-notification');
            if (existing) existing.remove();

            const toast = document.createElement('div');
            toast.className = 'toast-notification toast-' + type;
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
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
    
    <main role="main" aria-label="Courses disponibles pour livraison">
        <section class="radar-section" aria-labelledby="titre-radar">

            <div class="status-banner" role="status" aria-live="polite">
                <div class="pulse-ring" aria-hidden="true"></div>
                <h2 id="titre-radar">En ligne - Recherche de livraisons en attente...</h2>
            </div>

            <button type="button" class="simulate-btn" id="btn-simuler">🎲 Simuler une commande</button>

            <div class="offers-grid" role="list" aria-label="Liste des courses disponibles">

                <?php if (empty($offres)): ?>
                    <div class="empty-state" role="listitem">
                        <h3>Aucune course disponible</h3>
                        <p>Le restaurant est calme pour le moment. Restez en ligne.</p>
                    </div>
                <?php else: ?>

                    <?php foreach ($offres as $offre):
                        //Lecture des données de distance et gain déjà calculées à la création de la commande
                        $distanceKm = $offre['distance_km'] ?? round(1.5 + mt_rand(0, 60) / 10, 1);
                        $gainEstime = $offre['gain_livreur'] ?? round(2.50 + ($distanceKm * 0.80), 2);
                        $tempsEstime = $offre['temps_minutes'] ?? round($distanceKm * 4);
                    ?>
                        <div class="offer-card" role="listitem" aria-label="Course #<?= htmlspecialchars($offre['id']) ?>">
                            <div class="offer-header">
                                <div class="price-block">
                                    <span class="price-label">Gains estimés (Commande #<?= htmlspecialchars($offre['id']) ?>)</span>
                                    <h3 class="offer-price"><?= number_format($gainEstime, 2, ',', ' ') ?> €</h3>
                                </div>
                                <div class="time-block">
                                    <span class="offer-time">⏱ ~<?= $tempsEstime ?> min</span>
                                    <span class="offer-distance">📍 <?= number_format($distanceKm, 1, ',', '') ?> km au total</span>
                                </div>
                            </div>

                            <div class="offer-route">
                                <div class="route-step pickup">
                                    <strong>Récupération :</strong> Los Pollos Hermanos
                                </div>
                                <div class="route-step dropoff">
                                    <strong>Livraison :</strong> <?= htmlspecialchars($offre['adresse'] ?? 'Adresse non renseignée') ?>
                                </div>
                            </div>

                            <div class="offer-actions">
                                <button type="button" class="decline-btn" aria-label="Ignorer la course" onclick="this.closest('.offer-card').style.display='none';">❌</button>

                                <button type="button" class="accept-btn btn-accepter" data-id="<?= $offre['id'] ?>">Accepter la course</button>
                            </div>
                        </div>
                    <?php endforeach; ?>

                <?php endif; ?>

            </div>
        </section>
    </main>

    <?php include '../LIB/footer.php'; ?>
    
</body>
</html>
