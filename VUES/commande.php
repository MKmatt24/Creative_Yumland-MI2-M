<?php
session_start();

// Protection : Seul le restaurateur (ou admin) peut voir cette page
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'restaurateur' && $_SESSION['role'] !== 'admin')) {
    header('Location: accueil.php'); 
    exit();
}

$json_file = '../DATA/commande.json';
$commandes = json_decode(file_get_contents($json_file), true) ?? [];
$usersData = json_decode(file_get_contents('../DATA/users.json'), true) ?? [];

// Fonction pour filtrer et harmoniser les statuts du JSON
function filtrerCommandes($liste, $statuts_recherches) {
    return array_filter($liste, function($c) use ($statuts_recherches) {
        $s = $c['statut'] ?? ($c['statut_logistique'] ?? 'inconnu');
        return in_array($s, $statuts_recherches);
    });
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Commandes | DashBoard</title>
    <link rel="shortcut icon" type="image/png" href="../IMAGES/logo.png">
    <link rel="icon" type="image/png" href="../IMAGES/logo.png">
    <link rel="stylesheet" href="../CSS/accueil.css">
    <link rel="stylesheet" href="../CSS/commande.css">
</head>
<body>

<?php include '../LIB/header.php'; ?>

<div class="admin-top-bar" style="max-width: 1200px; margin: 20px auto; padding: 0 20px; display: flex; justify-content: flex-end;">
    <a href="gestion_menu.php" class="btn-status" style="background: var(--orange); color: var(--noir); text-decoration: none; padding: 12px 25px; border-radius: 8px; font-weight: bold; box-shadow: 0 4px 10px rgba(255,107,53,0.3);">
        🍴 GÉRER LA CARTE & LES MENUS
    </a>
</div>

<div class="theme-switcher-container">
        <button onclick="cycleTheme()" id="theme-toggle-btn" class="theme-switcher-btn">
            🎨 Changer de style
        </button>
    </div>

<div class="kanban-board admin-container">

    <section class="kanban-column column-todo">
        <h2 class="section-title">📥 Payée</h2>
        <div class="orders-grid">
        <?php foreach (filtrerCommandes($commandes, ['a_preparer', 'paye']) as $c): ?>
            <div class="order-card">
                <div class="order-header">
                    <span class="order-id">#<?= $c['id'] ?? '???' ?></span>
                    <span class="order-time"><?= $c['date'] ?? '' ?></span>
                </div>
                <div class="order-content">
                    <h4><?= htmlspecialchars($c['client'] ?? 'Client Web') ?></h4>
                    <ul class="items-list">
                    <?php foreach (($c['articles'] ?? []) as $a) echo "• {$a['quantite']}x {$a['nom']}<br>"; ?>
                    </ul>
                </div>
                <form action="../TRAITEMENTS/update_statut.php" method="POST">
                    <input type="hidden" name="id_commande" value="<?= $c['id'] ?>">
                    <button type="submit" name="nouveau_statut" value="preparation" class="btn-status">Lancer la cuisine 🍳</button>
                </form>
            </div>
        <?php endforeach; ?>
        </div>
    </section>

    <section class="kanban-column column-cooking">
        <h2 class="section-title">🍳 En Préparation</h2>
        <div class="orders-grid">
        <?php foreach (filtrerCommandes($commandes, ['preparation', 'en préparation']) as $c): ?>
            <div class="order-card">
                <div class="order-header">
                    <span class="order-id">#<?= $c['id'] ?></span>
                </div>
                <div class="order-content">
                    <h4><?= htmlspecialchars($c['client'] ?? 'Client Web') ?></h4>
                    <ul class="items-list">
                    <?php foreach (($c['articles'] ?? []) as $a) echo "• {$a['quantite']}x {$a['nom']}<br>"; ?>
                    </ul>
                </div>
                <form action="../TRAITEMENTS/update_statut.php" method="POST">
                    <input type="hidden" name="id_commande" value="<?= $c['id'] ?>">
                    <select name="id_livreur" class="select-livreur" required>
                        <option value="">-- Assigner Livreur --</option>
                        <?php foreach ($usersData as $u):
                            if (($u['role'] ?? '') === 'livreur'):
                        ?>
                            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?></option>
                        <?php endif; endforeach; ?>
                    </select>
                    <button type="submit" name="nouveau_statut" value="livraison" class="btn-status">Prêt pour envoi 🚚</button>
                </form>
            </div>
        <?php endforeach; ?>
        </div>
    </section>

    <section class="kanban-column column-delivery">
        <h2 class="section-title">🚚 En Livraison</h2>
        <div class="orders-grid">
        <?php foreach (filtrerCommandes($commandes, ['livraison', 'en_livraison']) as $c): ?>
            <div class="order-card">
                <div class="order-header">
                    <span class="order-id">#<?= $c['id'] ?></span>
                </div>
                <div class="order-content">
                    <p>📍 <?= htmlspecialchars($c['adresse'] ?? 'À emporter') ?></p>
                    <?php
                    $nomLivreur = 'Inconnu';
                    if (isset($c['livreur_id']) && is_numeric($c['livreur_id'])) {
                        foreach ($usersData as $ul) {
                            if ($ul['id'] == $c['livreur_id']) {
                                $nomLivreur = $ul['prenom'] ?? $ul['nom'] ?? 'Inconnu';
                                break;
                            }
                        }
                    }
                    ?>
                    <p style="color: #3498db !important; font-weight: bold;">👤 Livreur : <?= htmlspecialchars($nomLivreur) ?></p>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    </section>

    <section class="kanban-column column-done">
        <h2 class="section-title">🏁 Livrées</h2>
        <div class="orders-grid">
        <?php foreach (filtrerCommandes($commandes, ['livree', 'livrée']) as $c): ?>
            <div class="order-card">
                <div class="order-header">
                    <span class="order-id">#<?= $c['id'] ?></span>
                </div>
                <div class="order-content">
                    <h4><?= htmlspecialchars($c['client'] ?? 'Client Web') ?></h4>
                    <ul class="items-list">
                    <?php foreach (($c['articles'] ?? []) as $a) echo "• {$a['quantite']}x {$a['nom']}<br>"; ?>
                    </ul>
                    <p class="total-box">Total: <?= number_format($c['prix_total'] ?? 0, 2) ?> €</p>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    </section>

</div>

<?php include '../LIB/footer.php'; ?>
</body>
</html>
