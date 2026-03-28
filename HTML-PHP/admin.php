<?php
    session_start();

    // Vérifier que c'est un admin
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header('Location: connexion.php');
        exit;
    }

    // Charger les utilisateurs
    $users = json_decode(file_get_contents('../DATA/users.json'), true);

    // Filtrage (si formulaire soumis)
    $type_filtre = $_GET['type-utilisateur'] ?? 'tous';
    if ($type_filtre !== 'tous') {
        $users = array_filter($users, function($u) use ($type_filtre) {
            return $u['role'] === $type_filtre;
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
    <header>
        <nav>
            <div class="logo">
                <h1>Los Pollos Hermanos - Administration</h1>
            </div>
            <ul>
                <li><a href="accueil.html">Accueil</a></li>
                <li><a href="admin.html">Tableau de bord</a></li>
                <li><a href="connexion.html">Déconnexion</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="admin-section">
            <div class="admin-container">
                <h2>Panneau d'administration</h2>
                <p>Gestion des utilisateurs et des commandes</p>

                <div class="admin-filters">
                    <h3>Filtres de recherche</h3>
                    <form action="admin.html" method="get">
                        <div class="filter-group">
                            <label for="recherche-utilisateur">Rechercher un utilisateur</label>
                            <input type="text" id="recherche-utilisateur" name="recherche-utilisateur" placeholder="Nom, prénom, email...">
                        </div>

                        <div class="filter-group">
                            <label for="type-utilisateur">Type d'utilisateur</label>
                            <select id="type-utilisateur" name="type-utilisateur">
                                <option value="tous">Tous les utilisateurs</option>
                                <option value="clients">Clients uniquement</option>
                                <option value="avec-commandes">Clients avec commandes</option>
                                <option value="administrateurs">Administrateurs</option>
                                <option value="restaurateurs">Restaurateurs</option>
                                <option value="livreurs">Livreurs</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="statut-compte">Statut du compte</label>
                            <select id="statut-compte" name="statut-compte">
                                <option value="tous">Tous les statuts</option>
                                <option value="actif">Actifs</option>
                                <option value="inactif">Inactifs</option>
                                <option value="suspendu">Suspendus</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="date-inscription">Inscrit depuis</label>
                            <select id="date-inscription" name="date-inscription">
                                <option value="tous">Toutes les dates</option>
                                <option value="7jours">7 derniers jours</option>
                                <option value="30jours">30 derniers jours</option>
                                <option value="6mois">6 derniers mois</option>
                                <option value="1an">1 an</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <button type="submit">Rechercher</button>
                            <button type="reset">Réinitialiser</button>
                        </div>
                    </form>
                </div>

                <div class="admin-stats">
                    <h3>Statistiques</h3>
                    <div class="stats-container">
                        <div class="stat-item">
                            <p class="stat-label">Total utilisateurs</p>
                            <p class="stat-value">1 247</p>
                        </div>
                        <div class="stat-item">
                            <p class="stat-label">Clients actifs</p>
                            <p class="stat-value">892</p>
                        </div>
                        <div class="stat-item">
                            <p class="stat-label">Nouveaux ce mois</p>
                            <p class="stat-value">43</p>
                        </div>
                        <div class="stat-item">
                            <p class="stat-label">Avec commandes</p>
                            <p class="stat-value">678</p>
                        </div>
                    </div>
                </div>

                <div class="users-list">
                    <h3>Liste des utilisateurs</h3>
                    
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Email</th>
                                <th>Type</th>
                                <th>Date inscription</th>
                                <th>Nb commandes</th>
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
                                <td>-</td>
                                <td><?= htmlspecialchars($user['statut']) ?></td>
                                <td>
                                    <a href="profil.php?id=<?= $user['id'] ?>">Voir profil</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="pagination">
                        <a href="admin.html?page=1">1</a>
                        <a href="admin.html?page=2">2</a>
                        <a href="admin.html?page=3">3</a>
                        <a href="admin.html?page=4">4</a>
                        <a href="admin.html?page=5">5</a>
                        <span>...</span>
                        <a href="admin.html?page=25">25</a>
                    </div>
                </div>

                <div class="admin-actions">
                    <h3>Actions rapides</h3>
                    <div class="actions-container">
                        <a href="ajouter-utilisateur.html">Ajouter un utilisateur</a>
                        <a href="export-utilisateurs.html">Exporter la liste</a>
                        <a href="statistiques.html">Voir statistiques détaillées</a>
                        <a href="logs.html">Consulter les logs</a>
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
