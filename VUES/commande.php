<?php
session_start();

// Protection : Seul le restaurateur (ou admin) peut voir cette page
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'restaurateur' && $_SESSION['role'] !== 'admin')) {
    header('Location: accueil.php'); 
    exit();
}

$json_file = '../data/commande.json';
$commandes = json_decode(file_get_contents($json_file), true) ?? [];

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
    <link rel="stylesheet" href="../CSS/accueil.css">
    <link rel="stylesheet" href="../CSS/commande.css">
</head>
<body>

<?php include '../LIB/header.php'; ?>

<div class="kanban-board admin-container">

    <section class="kanban-column column-todo">
        <h2 class="section-title">📥 À Préparer</h2>
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
        <h2 class="section-title">🍳 En Cuisine</h2>
        <div class="orders-grid">
        <?php foreach (filtrerCommandes($commandes, ['preparation']) as $c): ?>
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
                        <option value="Jesse">Jesse Pinkman</option>
                        <option value="Mike">Mike Ehrmantraut</option>
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
                    <p style="color: #3498db !important; font-weight: bold;">👤 Livreur : <?= $c['livreur_id'] ?? 'Inconnu' ?></p>
                </div>
                <form action="../TRAITEMENTS/update_statut.php" method="POST">
                    <input type="hidden" name="id_commande" value="<?= $c['id'] ?>">
                    <button type="submit" name="nouveau_statut" value="livree" class="btn-status" style="background-color: #3498db;">Confirmer Livraison 🏁</button>
                </form>
            </div>
        <?php endforeach; ?>
        </div>
    </section>

    <section class="kanban-column column-done">
        <h2 class="section-title">🏁 Livrées</h2>
        <div class="orders-grid">
        <?php foreach (filtrerCommandes($commandes, ['livree']) as $c): ?>
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
