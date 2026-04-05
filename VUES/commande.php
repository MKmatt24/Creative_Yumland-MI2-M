<?php
session_start();

// Protection : Seul le restaurateur (ou admin) peut voir cette page
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'restaurateur' && $_SESSION['role'] !== 'admin')) {
    // header('Location: accueil.php'); 
    // exit();
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
    <link rel="stylesheet" href="../CSS/commande.css">
    <style>
        body { background: #0a0a0a; color: #eee; font-family: sans-serif; margin: 0; }
        .dashboard-wrapper { display: flex; gap: 15px; padding: 20px; overflow-x: auto; min-height: 90vh; }
        
        /* Colonnes */
        .column { background: #151515; border-radius: 8px; min-width: 300px; flex: 1; display: flex; flex-direction: column; border-top: 4px solid #333; }
        .col-title { padding: 15px; text-align: center; font-weight: bold; text-transform: uppercase; border-bottom: 1px solid #222; }
        
        .todo { border-color: #e74c3c; }      /* À Préparer */
        .cooking { border-color: #f1c40f; }   /* En Cuisine */
        .shipping { border-color: #3498db; }  /* En Livraison */
        .done { border-color: #2ecc71; }      /* Livrées */

        .card { background: #202020; margin: 10px; padding: 15px; border-radius: 6px; border: 1px solid #333; font-size: 0.9em; }
        .card h4 { margin: 0 0 10px 0; color: #ff6b35; }
        .items { font-size: 0.85em; color: #bbb; margin: 10px 0; padding-left: 15px; }
        
        select, .btn { width: 100%; padding: 8px; margin-top: 8px; border-radius: 4px; border: none; font-weight: bold; cursor: pointer; }
        select { background: #000; color: #fff; border: 1px solid #444; }
        .btn { background: #ff6b35; color: #000; text-transform: uppercase; font-size: 0.75em; }
        .btn:hover { background: #e65a2b; }
    </style>
</head>
<body>

<?php include '../LIB/header.php'; ?>

<div class="dashboard-wrapper">

    <section class="column todo">
        <div class="col-title">📥 À Préparer</div>
        <?php foreach (filtrerCommandes($commandes, ['a_preparer', 'paye']) as $c): ?>
            <div class="card">
                <h4>#<?= $c['id'] ?? '???' ?> - <?= htmlspecialchars($c['client'] ?? 'Client Web') ?></h4>
                <div class="items">
                    <?php foreach (($c['articles'] ?? []) as $a) echo "• {$a['quantite']}x {$a['nom']}<br>"; ?>
                </div>
                <form action="../TRAITEMENTS/update_statut.php" method="POST">
                    <input type="hidden" name="id_commande" value="<?= $c['id'] ?>">
                    <button type="submit" name="nouveau_statut" value="preparation" class="btn">Lancer la cuisine 🍳</button>
                </form>
            </div>
        <?php endforeach; ?>
    </section>

    <section class="column cooking">
        <div class="col-title">🍳 En Cuisine</div>
        <?php foreach (filtrerCommandes($commandes, ['preparation']) as $c): ?>
            <div class="card">
                <h4>#<?= $c['id'] ?> - <?= htmlspecialchars($c['client'] ?? 'Client Web') ?></h4>
                <div class="items">
                    <?php foreach (($c['articles'] ?? []) as $a) echo "• {$a['quantite']}x {$a['nom']}<br>"; ?>
                </div>
                <form action="../TRAITEMENTS/update_statut.php" method="POST">
                    <input type="hidden" name="id_commande" value="<?= $c['id'] ?>">
                    <select name="id_livreur" required>
                        <option value="">-- Assigner Livreur --</option>
                        <option value="Jesse">Jesse Pinkman</option>
                        <option value="Mike">Mike Ehrmantraut</option>
                    </select>
                    <button type="submit" name="nouveau_statut" value="livraison" class="btn">Prêt pour envoi 🚚</button>
                </form>
            </div>
        <?php endforeach; ?>
    </section>

    <section class="column shipping">
        <div class="col-title">🚚 En Livraison</div>
        <?php foreach (filtrerCommandes($commandes, ['livraison', 'en_livraison']) as $c): ?>
            <div class="card">
                <h4>#<?= $c['id'] ?></h4>
                <p>📍 <?= htmlspecialchars($c['adresse'] ?? 'À emporter') ?></p>
                <p style="color: #3498db;">👤 Livreur : <?= $c['livreur_id'] ?? 'Inconnu' ?></p>
                <form action="../TRAITEMENTS/update_statut.php" method="POST">
                    <input type="hidden" name="id_commande" value="<?= $c['id'] ?>">
                    <button type="submit" name="nouveau_statut" value="livree" class="btn" style="background:#3498db; color:white;">Confirmer Livraison 🏁</button>
                </form>
            </div>
        <?php endforeach; ?>
    </section>

    <section class="column done">