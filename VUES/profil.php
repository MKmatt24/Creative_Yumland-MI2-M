<?php include '../LIB/authentification.php';

//Récupération de tous les utilisateurs depuis le JSON
$usersData = file_get_contents('../DATA/users.json');
$users = json_decode($usersData, true);

$currentUser = null;

//Déterminer quel ID on veut afficher
//On utilise l'ID de l'URL ($_GET) s'il existe, sinon on prend celui de la session
$idAAfficher = $_GET['id'] ?? $_SESSION['user_id'];


//Recherche de l'utilisateur correspondant à cet ID
foreach ($users as $user) {
    if ($user['id'] == $idAAfficher) {
        $currentUser = $user;
        break;
    }
}

//Optionnel : Sécurité
if (!$currentUser) {
    echo "Utilisateur introuvable.";
    exit;
}

//Récupération de toutes les commandes
$commandesData = file_get_contents('../DATA/commande.json');
$commandes = json_decode($commandesData, true);

$mesCommandes = [];
$nomComplet = $currentUser['prenom'] . ' ' . $currentUser['nom'];

//Tri pour avoir uniquement les commandes de l'utilisateur
$mesCommandes = [];
//On s'assure que le nom complet est bien calculé
$nomComplet = $currentUser['prenom'] . ' ' . $currentUser['nom'];

if ($commandes && is_array($commandes)) {
    foreach ($commandes as $cmd) {
        // On vérifie si la clé 'client' existe pour éviter une erreur
        if (isset($cmd['client'])) {
            // strcasecmp compare sans vérifier les majuscules/minuscules (plus sûr)
            if (strcasecmp($cmd['client'], $nomComplet) == 0) {
                $mesCommandes[] = $cmd;
            }
        }
    }
}

// Récupération des notations existantes pour masquer le bouton "Noter" si déjà fait
$notationsData = file_exists('../DATA/notation.json') ? file_get_contents('../DATA/notation.json') : '[]';
$notations = json_decode($notationsData, true) ?? [];
// On crée un tableau des IDs de commandes déjà notées pour une recherche rapide
$idsCommandesNotées = array_column($notations, 'commande_id');

//Calcul des points de fidélité à partir de l'historique des commandes livrées (5 points par euro)
$totalPointsGagnes = 0;
foreach ($mesCommandes as $cmd) {
    $statut = strtolower($cmd['statut'] ?? '');
    if ($statut === 'livrée' || $statut === 'livree') {
        $totalPointsGagnes += floor(floatval($cmd['prix_total'] ?? 0)) * 5;
    }
}
//Calcul du nombre de coupons déjà gagnés et des points restants
$nbCouponsGagnes = floor($totalPointsGagnes / 500);
$pointsRestants = $totalPointsGagnes % 500;

//Génération des coupons de fidélité dans coupons.json (codes à usage unique)
$fichierCoupons = '../DATA/coupons.json';
$couponsData = json_decode(file_get_contents($fichierCoupons), true) ?? [];
$prixPlats = ['Hermano Tenders' => 9.00, 'Pollos Burger' => 8.50];
$platsOfferts = ['Hermano Tenders', 'Pollos Burger'];

//On vérifie combien de coupons fidélité existent déjà pour cet utilisateur
$prefixe = 'FIDELITE-' . $currentUser['id'] . '-';
$couponsExistants = 0;
foreach ($couponsData as $code => $infos) {
    if (strpos($code, $prefixe) === 0) {
        $couponsExistants++;
    }
}

//On génère les coupons manquants
$couponsModifies = false;
for ($i = $couponsExistants; $i < $nbCouponsGagnes; $i++) {
    $plat = $platsOfferts[$i % 2];
    $code = $prefixe . strtoupper(substr(md5($currentUser['id'] . $i . 'fidelite'), 0, 6));
    $couponsData[$code] = [
        'type' => 'fixe',
        'valeur' => $prixPlats[$plat],
        'description' => $plat . ' offert (Fidélité 500 pts)'
    ];
    $couponsModifies = true;
}
if ($couponsModifies) {
    file_put_contents($fichierCoupons, json_encode($couponsData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

//Récupération des coupons fidélité de l'utilisateur pour affichage
$couponsFidelite = [];
foreach ($couponsData as $code => $infos) {
    if (strpos($code, $prefixe) === 0) {
        $couponsFidelite[] = ['code' => $code, 'description' => $infos['description'], 'valeur' => $infos['valeur']];
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - LOS POLLOS HERMANOS</title>
    <link rel="shortcut icon" type="image/png" href="../IMAGES/logo.png">
    <link rel="icon" type="image/png" href="../IMAGES/logo.png">
    <link rel="stylesheet" href="../CSS/profil.css">
    <script defer>
    document.addEventListener('DOMContentLoaded', function() {
        //Gestion du menu mobile
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

        //Edition du profil concernant les requêtes asynchrones (phase 3)

        const userId = <?= json_encode($currentUser['id']) ?>;

        const fieldConfig = [
            { label: 'Nom complet', field: 'nom_complet', type: 'text', validate: validateNom },
            { label: 'Date de naissance', field: 'date_naissance', type: 'date', validate: validateDate },
            { label: 'Email', field: 'email', type: 'email', validate: validateEmail },
            { label: 'Téléphone', field: 'telephone', type: 'tel', validate: validateTelephone },
            { label: 'Adresse de livraison', field: 'adresse', type: 'text', validate: validateAdresse }
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'livreur'): ?>
            ,{ label: 'Objectif du jour', field: 'objectif_jour', type: 'number', validate: validateObjectif }
            <?php endif; ?>
        ];

        const editButtons = document.querySelectorAll('.icon-btn');

        editButtons.forEach((btn, index) => {
            btn.addEventListener('click', () => {
                const config = fieldConfig[index];
                const inputGroup = btn.closest('.input-with-btn');
                const input = inputGroup.querySelector('input');

                if (input.readOnly) {
                    activerEdition(input, btn, config);
                } else {
                    sauvegarderChamp(input, btn, config);
                }
            });
        });

        function activerEdition(input, btn, config) {
            input.readOnly = false;
            input.classList.add('editing');
            input.focus();

            if (config.type === 'date') {
                input.type = 'date';
                const parts = input.value.split('/');
                if (parts.length === 3) {
                    input.value = `${parts[2]}-${parts[1].padStart(2,'0')}-${parts[0].padStart(2,'0')}`;
                }
            }

            btn.textContent = '✅';
            btn.title = 'Sauvegarder';
            input.dataset.originalValue = input.value;

            const container = btn.closest('.input-with-btn');
            let cancelBtn = container.querySelector('.cancel-btn');
            if (!cancelBtn) {
                cancelBtn = document.createElement('button');
                cancelBtn.type = 'button';
                cancelBtn.className = 'icon-btn cancel-btn';
                cancelBtn.textContent = '❌';
                cancelBtn.title = 'Annuler';
                btn.parentElement.appendChild(cancelBtn);
                cancelBtn.addEventListener('click', () => {
                    annulerEdition(input, btn, config);
                    cancelBtn.remove();
                });
            }

            let counter = input.parentElement.parentElement.querySelector('.char-counter');
            if (!counter) {
                counter = document.createElement('span');
                counter.className = 'char-counter';
                input.parentElement.parentElement.appendChild(counter);
            }
            const maxLen = getMaxLength(config.field);
            counter.textContent = `${input.value.length} / ${maxLen}`;
            input.addEventListener('input', () => {
                counter.textContent = `${input.value.length} / ${maxLen}`;
                counter.style.color = input.value.length > maxLen ? '#e74c3c' : '#888';
            });
        }

        function annulerEdition(input, btn, config) {
            input.value = input.dataset.originalValue;
            input.readOnly = true;
            input.classList.remove('editing');
            if (config.type === 'date') input.type = 'text';
            btn.textContent = '✏️';
            btn.title = 'Modifier';
            const counter = input.parentElement.parentElement.querySelector('.char-counter');
            if (counter) counter.remove();
            supprimerErreur(input);
        }

        function sauvegarderChamp(input, btn, config) {
            supprimerErreur(input);
            const valeur = input.value.trim();
            const erreur = config.validate(valeur);

            if (erreur) {
                afficherErreur(input, erreur);
                return;
            }

            btn.disabled = true;
            btn.textContent = '⏳';

            const donnees = new FormData();
            donnees.append('user_id', userId);
            donnees.append('champ', config.field);
            donnees.append('valeur', valeur);

            fetch('../TRAITEMENTS/update_profil.php', {
                method: 'POST',
                body: donnees
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    input.readOnly = true;
                    input.classList.remove('editing');
                    input.classList.add('save-success');
                    if (config.type === 'date') {
                        input.type = 'text';
                        const d = new Date(valeur);
                        input.value = d.toLocaleDateString('fr-FR');
                    }
                    btn.textContent = '✏️';
                    btn.title = 'Modifier';
                    const cancelBtn = btn.parentElement.querySelector('.cancel-btn');
                    if (cancelBtn) cancelBtn.remove();
                    const counter = input.parentElement.parentElement.querySelector('.char-counter');
                    if (counter) counter.remove();
                    afficherNotification('Modification enregistrée !', 'success');
                    setTimeout(() => input.classList.remove('save-success'), 2000);
                } else {
                    afficherErreur(input, data.message || 'Erreur lors de la sauvegarde.');
                    btn.textContent = '✅';
                }
            })
            .catch(() => {
                afficherErreur(input, 'Erreur réseau. Réessayez.');
                btn.textContent = '✅';
            })
            .finally(() => {
                btn.disabled = false;
            });
        }

        //Validation des champs côté client

        function validateNom(value) {
            if (!value || value.length < 3) return 'Le nom doit contenir au moins 3 caractères.';
            if (value.length > 60) return 'Le nom ne doit pas dépasser 60 caractères.';
            if (!/^[a-zA-ZÀ-ÿ\s\-']+$/.test(value)) return 'Le nom ne doit contenir que des lettres.';
            if (!value.includes(' ')) return 'Veuillez entrer le prénom et le nom (séparés par un espace).';
            return null;
        }

        function validateEmail(value) {
            if (!value) return "L'email est obligatoire.";
            if (value.length > 100) return "L'email ne doit pas dépasser 100 caractères.";
            const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!regex.test(value)) return "Format d'email invalide.";
            return null;
        }

        function validateTelephone(value) {
            if (!value) return 'Le téléphone est obligatoire.';
            const cleaned = value.replace(/[\s\.\-]/g, '');
            if (!/^(0|\+33)[1-9]\d{8}$/.test(cleaned)) return 'Numéro de téléphone invalide (format français attendu).';
            return null;
        }

        function validateDate(value) {
            if (!value) return 'La date de naissance est obligatoire.';
            const date = new Date(value);
            if (isNaN(date.getTime())) return 'Date invalide.';
            const today = new Date();
            let age = today.getFullYear() - date.getFullYear();
            const m = today.getMonth() - date.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < date.getDate())) age--;
            if (age < 16) return 'Vous devez avoir au moins 16 ans.';
            if (age > 120) return 'Date de naissance invalide.';
            return null;
        }

        function validateAdresse(value) {
            if (!value) return "L'adresse est obligatoire.";
            if (value.length < 10) return "L'adresse semble trop courte.";
            if (value.length > 150) return "L'adresse ne doit pas dépasser 150 caractères.";
            return null;
        }

        function validateObjectif(value) {
            const num = parseFloat(value);
            if (isNaN(num)) return 'Veuillez entrer un nombre.';
            if (num < 10) return 'L\'objectif doit être d\'au moins 10 €.';
            if (num > 500) return 'L\'objectif ne peut pas dépasser 500 €.';
            return null;
        }

        function getMaxLength(field) {
            const lengths = { nom_complet: 60, email: 100, telephone: 15, date_naissance: 10, adresse: 150, objectif_jour: 6 };
            return lengths[field] || 100;
        }

        //Affichage des erreurs et notifications

        function afficherErreur(input, message) {
            supprimerErreur(input);
            const errorDiv = document.createElement('div');
            errorDiv.className = 'field-error';
            errorDiv.textContent = message;
            input.parentElement.parentElement.appendChild(errorDiv);
            input.classList.add('input-error');
        }

        function supprimerErreur(input) {
            const parent = input.parentElement.parentElement;
            const err = parent.querySelector('.field-error');
            if (err) err.remove();
            input.classList.remove('input-error');
        }

        function afficherNotification(message, type) {
            const existing = document.querySelector('.toast-notification');
            if (existing) existing.remove();
            const toast = document.createElement('div');
            toast.className = `toast-notification toast-${type}`;
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => toast.classList.add('show'), 10);
            setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 300); }, 3000);
        }

        //Gestion du bouton "Commander à nouveau" dans les commandes passées

        //Changement de l'avatar (Phase 3 - Async)

        const avatarBtn = document.querySelector('.edit-avatar-btn');
        const avatarInput = document.getElementById('avatar-input');
        const avatarImg = document.querySelector('.avatar-container img');

        if (avatarBtn && avatarInput) {
            avatarBtn.addEventListener('click', () => avatarInput.click());

            avatarInput.addEventListener('change', function() {
                const fichier = this.files[0];
                if (!fichier) return;

                if (!fichier.type.startsWith('image/')) {
                    afficherNotification('Veuillez choisir une image.', 'error');
                    return;
                }
                if (fichier.size > 2 * 1024 * 1024) {
                    afficherNotification('L\'image ne doit pas dépasser 2 Mo.', 'error');
                    return;
                }

                const formData = new FormData();
                formData.append('avatar', fichier);
                formData.append('user_id', userId);

                avatarBtn.textContent = '⏳';

                fetch('../TRAITEMENTS/update_avatar.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        avatarImg.src = data.chemin + '?t=' + Date.now();
                        afficherNotification('Photo de profil mise à jour !', 'success');
                    } else {
                        afficherNotification(data.message || 'Erreur.', 'error');
                    }
                    avatarBtn.textContent = '📷';
                })
                .catch(() => {
                    afficherNotification('Erreur réseau.', 'error');
                    avatarBtn.textContent = '📷';
                });
            });
        }

        //Commander à nouveau

        document.querySelectorAll('.reorder-btn[data-articles]').forEach(btn => {
            btn.addEventListener('click', function() {
                const articles = JSON.parse(this.dataset.articles);
                if (!articles || articles.length === 0) {
                    afficherNotification('Aucun article dans cette commande.', 'error');
                    return;
                }

                this.disabled = true;
                this.textContent = '⏳ Ajout...';

                const promesses = articles.map(article => {
                    const formData = new FormData();
                    formData.append('nom', article.nom);
                    formData.append('prix', article.prix || 0);
                    formData.append('quantite', article.quantite || 1);

                    return fetch('../TRAITEMENTS/ajouter_panier.php', {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        body: formData
                    }).then(res => res.json());
                });

                Promise.all(promesses)
                    .then(results => {
                        const lastResult = results[results.length - 1];
                        if (lastResult.success) {
                            afficherNotification(articles.length + ' article(s) ajouté(s) au panier !', 'success');
                            this.textContent = '✅ Ajouté !';
                            setTimeout(() => { this.textContent = 'Commander à nouveau'; this.disabled = false; }, 2000);
                        } else {
                            afficherNotification('Erreur lors de l\'ajout au panier.', 'error');
                            this.textContent = 'Commander à nouveau';
                            this.disabled = false;
                        }
                    })
                    .catch(() => {
                        afficherNotification('Erreur réseau.', 'error');
                        this.textContent = 'Commander à nouveau';
                        this.disabled = false;
                    });
            });
        });
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
        <section class="profil-section">
            <div class="profil-container">
                
                <div class="profil-header">
                    <?php if (isset($currentUser['est_bloque']) && $currentUser['est_bloque'] === true): ?>
                        <div class="blocked-banner">
                            <strong>Compte Suspendu :</strong> Vous ne pouvez plus passer de nouvelles commandes. Vos commandes déjà payées restent en cours de traitement et vous seront livrées.
                        </div>
                    <?php endif; ?>

                    <div class="avatar-container">
                        <?php $avatarPath = $currentUser['avatar'] ?? '../IMAGES/avatar_anonyme.png'; ?>
                        <img src="<?= htmlspecialchars($avatarPath) ?>" alt="Avatar">
                        <button type="button" class="edit-avatar-btn" aria-label="Changer la photo">📷</button>
                        <input type="file" id="avatar-input" accept="image/*" hidden>
                    </div>
                    <div class="user-identity">
                        <h2><?= htmlspecialchars($currentUser['prenom'] . ' ' . $currentUser['nom']) ?></h2>
                        <p class="member-date">Membre depuis <?= substr($currentUser['date_inscription'] ?? '2024', 0, 4) ?></p>
                        
                        <div class="loyalty-card">
                            <h3>Los Pollos Club</h3>
                            <?php
                            $objectif = 500;
                            $pourcentage = ($pointsRestants / $objectif) * 100;
                            if ($pourcentage > 100) $pourcentage = 100;
                            ?>
                            <p>Vous avez <strong><?= $pointsRestants ?> / <?= $objectif ?> points</strong></p>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?= round($pourcentage) ?>%;"></div>
                            </div>
                            <p class="points-hint">
                                (Encore <?= $objectif - $pointsRestants ?> points avant votre prochaine récompense !)
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
                                        <?php
    $dateNaissance = $currentUser['date_naissance'] ?? '';
    if ($dateNaissance && strpos($dateNaissance, '-') !== false) {
        $dateNaissance = date('d/m/Y', strtotime($dateNaissance));
    }
?>
                                        <input type="text" value="<?= htmlspecialchars($dateNaissance) ?>" readonly>
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

                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'livreur'): ?>
                            <div class="form-group">
                                <label>Objectif du jour (€)</label>
                                <div class="input-with-btn">
                                    <input type="number" value="<?= htmlspecialchars($currentUser['objectif_jour'] ?? '160') ?>" readonly min="10" max="500" step="5">
                                    <button type="button" class="icon-btn">✏️</button>
                                </div>
                            </div>
                            <?php endif; ?>
                        </form>
                    </div>

                    <div class="history-column">
                        <h3>Dernières Commandes</h3>
                        <div class="order-list">
                            <?php if (empty($mesCommandes)): ?>
                                <p class="empty-orders">Vous n'avez passé aucune commande.</p>
                            <?php else: ?>
                                <?php foreach ($mesCommandes as $commande): ?>
                                    <div class="order-card">
                                        <div class="order-icon">🍗</div>
                                        <div class="order-details">
                                            <h4>Commande #<?= $commande['id'] ?></h4>
                                            <span><?= htmlspecialchars($commande['date'] ?? $commande['date_commande'] ?? 'Date inconnue') ?> • <?= number_format($commande['prix_total'], 2, ',', ' ') ?>€</span>
                                            <span class="order-status">Statut : <?= ucfirst($commande['statut']) ?></span>
                                        </div>
                                        
                                        <div class="order-actions">
                                                                                        <?php if (!(isset($currentUser['est_bloque']) && $currentUser['est_bloque'] === true)): ?>
                                                <button class="reorder-btn" data-articles='<?= htmlspecialchars(json_encode($commande["articles"]), ENT_QUOTES) ?>'>Commander à nouveau</button>
                                            <?php else: ?>
                                                <button class="reorder-btn" disabled title="Action impossible : compte suspendu">Commander à nouveau</button>
                                            <?php endif; ?>
                                            
                                            <?php
                                            // Affichage du bouton "Noter" seulement si la commande est livrée et pas encore notée
                                            $estLivree = in_array(strtolower($commande['statut']), ['livree', 'livrée']);
                                            if ($estLivree && !in_array($commande['id'], $idsCommandesNotées)): 
                                            ?>
                                                <a href="notation.php?commande=<?= $commande['id'] ?>" class="reorder-btn noter-btn">
                                                    Noter
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                   <div class="coupons-section">
                        <h3 class="section-title"><i class="fas fa-ticket-alt"></i> Mes Coupons de Réduction</h3>
                        <div class="coupons-grid">
                            <div class="coupon-card">
                                <div class="coupon-left">
                                    <span class="coupon-value">-10%</span>
                                </div>
                                <div class="coupon-right">
                                    <h4>Offre de Bienvenue</h4>
                                    <p>Code : <span class="coupon-code">POLLOS10</span></p>
                                    <small>Valable sur tout le menu</small>
                                </div>
                            </div>

                            <div class="coupon-card">
                                <div class="coupon-left">
                                    <span class="coupon-value">-5€</span>
                                </div>
                                <div class="coupon-right">
                                    <h4>Fidélité</h4>
                                    <p>Code : <span class="coupon-code">GUSTAVO5</span></p>
                                    <small>Dès 30€ d'achat</small>
                                </div>
                            </div>

                            <?php foreach ($couponsFidelite as $coupon): ?>
                            <div class="coupon-card coupon-fidelite">
                                <div class="coupon-left">
                                    <span class="coupon-value">GRATUIT</span>
                                </div>
                                <div class="coupon-right">
                                    <h4><?= htmlspecialchars($coupon['description']) ?></h4>
                                    <p>Code : <span class="coupon-code"><?= htmlspecialchars($coupon['code']) ?></span></p>
                                    <small>À utiliser dans le panier (-<?= number_format($coupon['valeur'], 2, ',', '') ?> €)</small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div> </div>
        </section>
    </main>

    <?php include '../LIB/footer.php'; ?>

</body>
</html>