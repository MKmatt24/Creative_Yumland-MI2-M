<?php include '../LIB/authentification.php'; ?>

<?php
//Récupération de tous les utilisateurs depuis le JSON
$usersData = file_get_contents('../DATA/users.json');
$users = json_decode($usersData, true);

$currentUser = null;

//Recherche de l'utilisateur connecté
foreach ($users as $user) {
    if ($user['id'] == $_SESSION['user_id']) {
        $currentUser = $user;
        break;
    }
}

//Récupération de toutes les commandes
$commandesData = file_get_contents('../DATA/commande.json');
$commandes = json_decode($commandesData, true);

$mesCommandes = [];
$nomComplet = $currentUser['prenom'] . ' ' . $currentUser['nom'];

//Tri pour avoir uniquement les commandes de l'utilisateur
foreach ($commandes as $cmd) {
    if ($cmd['client'] == $nomComplet) {
        $mesCommandes[] = $cmd;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - LOS POLLOS HERMANOS</title>
    <link rel="icon" type="image/png" href="../IMAGES/logo.png">
    <link rel="stylesheet" href="../CSS/profil.css"> 
</head>
<body>

    <?php include '../LIB/header.php'; ?>

    <main>
        <section class="profil-section">
            <div class="profil-container">
                
                <div class="profil-header">
                    <div class="avatar-container">
                        <img src="../IMAGES/avatar_jotaro.png" alt="Avatar">
                        <button class="edit-avatar-btn" aria-label="Changer la photo">📷</button>
                    </div>
                    <div class="user-identity">
                        <h2><?= htmlspecialchars($currentUser['prenom'] . ' ' . $currentUser['nom']) ?></h2>
                        <p class="member-date">Membre depuis <?= substr($currentUser['date_inscription'], 0, 4) ?></p>
                        
                        <div class="loyalty-card">
                            <h3>Los Pollos Club</h3>
                            <?php 
                            $points = $currentUser['points_fidelite'] ?? 0;
                            $objectif = 500; 
                            $pourcentage = ($points / $objectif) * 100;
                            
                            if ($pourcentage > 100) {
                                $pourcentage = 100;
                            }
                            ?>
                            <p>Vous avez <strong><?= htmlspecialchars($points) ?> / <?= $objectif ?> points</strong></p>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?= round($pourcentage) ?>%;"></div>
                            </div>
                            <p style="font-size: 0.8rem; opacity: 0.8; margin-top: 10px;">
                                (Encore <?= $objectif - $points ?> points avant votre prochaine récompense !)
                            </p>
                        </div>
                    </div>
                </div>

                <hr class="divider">

                <div class="profil-content-grid">
                    <div class="info-column">
                        <h3>Mes Coordonnées</h3>
                        <form class="profil-form">
                            <div class="form-group-row">
                                <div class="form-group">
                                    <label>Nom complet</label>
                                    <div class="input-with-btn">
                                        <input type="text" value="<?= htmlspecialchars($currentUser['prenom'] . ' ' . strtoupper($currentUser['nom'])) ?>" readonly>
                                        <button type="button" class="icon-btn">✏️</button>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Date de naissance 🎁</label>
                                    <div class="input-with-btn">
                                        <input type="text" value="<?= htmlspecialchars($currentUser['date_naissance'] ?? '') ?>" readonly>
                                        <button type="button" class="icon-btn">✏️</button>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Email</label>
                                <div class="input-with-btn">
                                    <input type="email" value="<?= htmlspecialchars($currentUser['email']) ?>" readonly>
                                    <button type="button" class="icon-btn">✏️</button>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Téléphone</label>
                                <div class="input-with-btn">
                                    <input type="tel" value="<?= htmlspecialchars($currentUser['telephone']) ?>" readonly>
                                    <button type="button" class="icon-btn">✏️</button>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Adresse de livraison</label>
                                <div class="input-with-btn">
                                    <input type="text" value="<?= htmlspecialchars($currentUser['adresse'] . ', ' . $currentUser['code_postal'] . ' ' . $currentUser['ville']) ?>" readonly>
                                    <button type="button" class="icon-btn">✏️</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="history-column">
                        <h3>Dernières Commandes</h3>
                        <div class="order-list">
                            <?php if (empty($mesCommandes)): ?>
                                <p style="color: #888; text-align: center;">Vous n'avez passé aucune commande.</p>
                            <?php else: ?>
                                <?php foreach ($mesCommandes as $commande): ?>
                                    <div class="order-card">
                                        <div class="order-icon">🍗</div>
                                        <div class="order-details">
                                            <h4>Commande #<?= $commande['id'] ?></h4>
                                            <span><?= htmlspecialchars($commande['date']) ?> • <?= number_format($commande['prix_total'], 2, ',', ' ') ?>€</span>
                                            <span style="display: block; color: #ff6b35; font-size: 0.75rem; margin-top: 5px;">Statut : <?= ucfirst($commande['statut']) ?></span>
                                        </div>
                                        <button class="reorder-btn">Commander à nouveau</button>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                </div> </div>
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
