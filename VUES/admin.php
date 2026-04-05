<?php include '../LIB/authentification.php'; ?>

<?php

// Vérifier que c'est un admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: connexion.php');
    exit;
}

// Charger les utilisateurs
$users = json_decode(file_get_contents('../DATA/users.json'), true);

// CALCUL DES STATISTIQUES DYNAMIQUES
$total_users = count($users);
$clients_actifs = count(array_filter($users, function($u) {
    return $u['role'] === 'client' && $u['statut'] === 'actif';
}));

// Nouveaux utilisateurs ce mois
$nouveaux_ce_mois = count(array_filter($users, function($u) {
    $date_inscription = strtotime($u['date_inscription'] ?? '1970-01-01');
    $debut_mois = strtotime('first day of this month');
    return $date_inscription >= $debut_mois;
}));

// FILTRAGE - Recherche par nom/prénom/email
$recherche = $_GET['recherche-utilisateur'] ?? '';
if (!empty($recherche)) {
    $users = array_filter($users, function($u) use ($recherche) {
        $recherche_lower = strtolower($recherche);
        return strpos(strtolower($u['nom']), $recherche_lower) !== false ||
               strpos(strtolower($u['prenom']), $recherche_lower) !== false ||
               strpos(strtolower($u['email']), $recherche_lower) !== false;
    });
}

// FILTRAGE - Type d'utilisateur
$type_filtre = $_GET['type-utilisateur'] ?? 'tous';
if ($type_filtre !== 'tous') {
    // Conversion des valeurs du select vers les rôles réels
    $role_map = [
        'clients' => 'client',
        'administrateurs' => 'admin',
        'restaurateurs' => 'restaurateur',
        'livreurs' => 'livreur'
    ];
    
    if (isset($role_map[$type_filtre])) {
        $users = array_filter($users, function($u) use ($role_map, $type_filtre) {
            return $u['role'] === $role_map[$type_filtre];
        });
    }
}

// FILTRAGE - Statut du compte
$statut_filtre = $_GET['statut-compte'] ?? 'tous';
if ($statut_filtre !== 'tous') {
    $users = array_filter($users, function($u) use ($statut_filtre) {
        return $u['statut'] === $statut_filtre;
    });
}

// FILTRAGE - Date d'inscription
$date_filtre = $_GET['date-inscription'] ?? 'tous';
if ($date_filtre !== 'tous') {
    $now = time();
    $users = array_filter($users, function($u) use ($date_filtre, $now) {
        $date_inscription = strtotime($u['date_inscription'] ?? '1970-01-01');
        
        switch ($date_filtre) {
            case '7jours':
                return ($now - $date_inscription) <= (7 * 24 * 60 * 60);
            case '30jours':
                return ($now - $date_inscription) <= (30 * 24 * 60 * 60);
            case '6mois':
                return ($now - $date_inscription) <= (180 * 24 * 60 * 60);
            case '1an':
                return ($now - $date_inscription) <= (365 * 24 * 60 * 60);
            default:
                return true;
        }
    });
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Los Pollos Hermanos</title>
    <link rel="icon" type="image/png" href="../Images/logo.svg">
    <link rel="stylesheet" href="../CSS/admin.css">
</head>
<body>
    
    <?php include '../LIB/header.php'; ?>

    <main>
        <section class="admin-section">
            <div class="admin-container">
                <h2>Panneau d'administration</h2>
                <p>Gestion des utilisateurs et des commandes</p>

                <div class="admin-filters">
                    <h3>Filtres de recherche</h3>
                    <form action="" method="get">
                        <div class="filter-group">
                            <label for="recherche-utilisateur">Rechercher un utilisateur</label>
                            <input type="text" id="recherche-utilisateur" name="recherche-utilisateur" 
                                   placeholder="Nom, prénom, email..." 
                                   value="<?= htmlspecialchars($recherche) ?>">
                        </div>

                        <div class="filter-group">
                            <label for="type-utilisateur">Type d'utilisateur</label>
                            <select id="type-utilisateur" name="type-utilisateur">
                                <option value="tous" <?= $type_filtre === 'tous' ? 'selected' : '' ?>>Tous les utilisateurs</option>
                                <option value="clients" <?= $type_filtre === 'clients' ? 'selected' : '' ?>>Clients uniquement</option>
                                <option value="administrateurs" <?= $type_filtre === 'administrateurs' ? 'selected' : '' ?>>Administrateurs</option>
                                <option value="restaurateurs" <?= $type_filtre === 'restaurateurs' ? 'selected' : '' ?>>Restaurateurs</option>
                                <option value="livreurs" <?= $type_filtre === 'livreurs' ? 'selected' : '' ?>>Livreurs</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="statut-compte">Statut du compte</label>
                            <select id="statut-compte" name="statut-compte">
                                <option value="tous" <?= $statut_filtre === 'tous' ? 'selected' : '' ?>>Tous les statuts</option>
                                <option value="actif" <?= $statut_filtre === 'actif' ? 'selected' : '' ?>>Actifs</option>
                                <option value="inactif" <?= $statut_filtre === 'inactif' ? 'selected' : '' ?>>Inactifs</option>
                                <option value="suspendu" <?= $statut_filtre === 'suspendu' ? 'selected' : '' ?>>Suspendus</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="date-inscription">Inscrit depuis</label>
                            <select id="date-inscription" name="date-inscription">
                                <option value="tous" <?= $date_filtre === 'tous' ? 'selected' : '' ?>>Toutes les dates</option>
                                <option value="7jours" <?= $date_filtre === '7jours' ? 'selected' : '' ?>>7 derniers jours</option>
                                <option value="30jours" <?= $date_filtre === '30jours' ? 'selected' : '' ?>>30 derniers jours</option>
                                <option value="6mois" <?= $date_filtre === '6mois' ? 'selected' : '' ?>>6 derniers mois</option>
                                <option value="1an" <?= $date_filtre === '1an' ? 'selected' : '' ?>>1 an</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <button type="submit">Rechercher</button>
                            <button type="reset" onclick="window.location.href='admin.php'">Réinitialiser</button>
                        </div>
                    </form>
                </div>

                <div class="admin-stats">
                    <h3>Statistiques</h3>
                    <div class="stats-container">
                        <div class="stat-item">
                            <p class="stat-label">Total utilisateurs</p>
                            <p class="stat-value"><?= $total_users ?></p>
                        </div>
                        <div class="stat-item">
                            <p class="stat-label">Clients actifs</p>
                            <p class="stat-value"><?= $clients_actifs ?></p>
                        </div>
                        <div class="stat-item">
                            <p class="stat-label">Nouveaux ce mois</p>
                            <p class="stat-value"><?= $nouveaux_ce_mois ?></p>
                        </div>
                        <div class="stat-item">
                            <p class="stat-label">Résultats filtrés</p>
                            <p class="stat-value"><?= count($users) ?></p>
                        </div>
                    </div>
                </div>

                <div class="users-list">
                    <h3>Liste des utilisateurs</h3>
                    
                    <?php if (empty($users)): ?>
                        <p style="text-align: center; color: #ff6b35; padding: 2rem;">
                            Aucun utilisateur trouvé avec ces critères.
                        </p>
                    <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Email</th>
                                <th>Type</th>
                                <th>Date inscription</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['id']) ?></td>
                                <td><?= htmlspecialchars($user['nom']) ?></td>
                                <td><?= htmlspecialchars($user['prenom']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= htmlspecialchars($user['role']) ?></td>
                                <td><?= htmlspecialchars($user['date_inscription'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($user['statut']) ?></td>
                                <td>
                                    <a href="profil.php?id=<?= $user['id'] ?>">Voir profil</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>

                <div class="admin-actions">
                    <h3>Actions rapides</h3>
                    <div class="actions-container">
                        <a href="inscription.php">Ajouter un utilisateur</a>
                        <a href="../TRAITEMENTS/export_utilisateurs.php">Exporter la liste (CSV)</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>À propos</h3>
                <p>Los Pollos Hermanos - Le meilleur poulet frit en ville</p>
            </div>
            <div class="footer-section">
                <h3>Nous contacter</h3>
                <p>Email : contact@lospolloshermanos.fr</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 Los Pollos Hermanos. Tous droits réservés.</p>
        </div>
    </footer>
</body>
</html>