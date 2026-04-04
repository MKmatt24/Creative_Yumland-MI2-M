<?php
session_start();

// 1. Chargement des données
$commandes_json = file_get_contents('../data/commandes.json');
$commandes = json_decode($commandes_json, true);

// Simulation du rôle
$role_utilisateur = isset($_SESSION['user']['role']) ? $_SESSION['user']['role'] : 'restaurateur'; 
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Restaurateur | Los Pollos Hermanos</title>
    <link rel="stylesheet" href="../CSS/commande.css">
    <style>
        .order-card { border-left: 5px solid #e74c3c; margin-bottom: 20px; padding: 15px; background: #f9f9f9; border-radius: 8px; }
        .order-card.prep { border-left-color: #f1c40f; }
        .order-card.delivery { border-left-color: #2ecc71; }
        .items-list { background: #fff; padding: 10px; border-radius: 5px; list-style: none; }
        .items-list li { border-bottom: 1px dashed #ddd; padding: 5px 0; }
        .order-meta { display: flex; justify-content: space-between; font-size: 0.9em; color: #666; margin-bottom: 10px; }
        .badge-type { background: #34495e; color: white; padding: 2px 8px; border-radius: 4px; text-transform: uppercase; }
    </style>
</head>
<body>

<header>
    <nav>
        <div class="logo">
            <a href="accueil.php"><img src="../IMAGES/logo.png" alt="Logo" class="nav-logo"></a>
        </div>
        <ul>
            <li><a href="accueil.php">Accueil</a></li>
            <li><a href="menu.php">Menu</a></li>
            <li><a href="profil.php">Mon Profil</a></li>
        </ul>
    </nav>
</header>

<main class="admin-container">
    
    <section class="order-section">
        <h2 class="section-title">🍳 Commandes à préparer (Cuisine)</h2>
        <div class="orders-grid">
            <?php 
            $count_prep = 0;
            foreach ($commandes as $c): 
                if ($c['statut'] === 'preparation'): 
                    $count_prep++;
            ?>
                <div class="order-card prep">
                    <div class="order-meta">
                        <span>🕒 Reçue à : <?php echo $c['date'] ?? '12:00'; ?></span>
                        <span class="badge-type"><?php echo $c['type'] ?? 'Livraison'; ?></span>
                    </div>

                    <div class="order-header">
                        <span class="order-id">#<?php echo $c['id']; ?></span>
                        <span class="badge">EN PRÉPARATION</span>
                    </div>

                    <h3>Client : <?php echo htmlspecialchars($c['client']); ?></h3>
                    
                    <ul class="items-list">
                        <?php foreach ($c['articles'] as $art): ?>
                            <li><strong><?php echo $art['quantite']; ?>x</strong> <?php echo htmlspecialchars($art['nom']); ?></li>
                        <?php endforeach; ?>
                    </ul>

                    <p><strong>Total à encaisser : <?php echo $c['prix_total']; ?>€</strong></p>
                    
                    <div style="margin: 15px 0; padding: 10px; background: #eee; border-radius: 4px;">
                        <label><strong>Assigner un livreur :</strong></label>
                        <select name="livreur_id" style="width: 100%; padding: 5px;">
                            <option value="">-- Choisir un livreur disponible --</option>
                            <option value="1">Livreur - Maxence (Libre)</option>
                            <option value="2">Livreur - Skinny Pete (En route)</option>
                        </select>
                    </div>

                    <form action="update_statut.php" method="POST">
                        <input type="hidden" name="id_commande" value="<?php echo $c['id']; ?>">
                        <input type="hidden" name="nouveau_statut" value="livraison">
                        <button type="submit" class="btn-ready" style="background-color: #27ae60; color: white; border: none; padding: 12px; border-radius: 5px; cursor: pointer; width: 100%; font-weight: bold;">
                            ✅ MARQUER COMME PRÊT
                        </button>
                    </form>
                </div>
            <?php 
                endif; 
            endforeach; 

            if ($count_prep === 0) echo "<p>Le calme avant la tempête... aucune commande en cuisine.</p>";
            ?>
        </div>
    </section>

    <hr class="separator">

    <section class="order-section">
        <h2 class="section-title">🚚 Suivi des Livraisons en cours</h2>
        <div class="orders-grid">
            <?php 
            $count_delivery = 0;
            foreach ($commandes as $c): 
                if ($c['statut'] === 'livraison'): 
                    $count_delivery++;
            ?>
                <div class="order-card delivery">
                    <div class="order-header">
                        <span class="order-id">#<?php echo $c['id']; ?></span>
                        <span class="badge" style="background:#2ecc71">SUR LA ROUTE</span>
                    </div>
                    <h3><?php echo htmlspecialchars($c['client']); ?></h3>
                    <p>📍 <strong>Destination :</strong> <?php echo htmlspecialchars($c['adresse']); ?></p>
                    <p>👤 <strong>Livreur :</strong> <?php echo !empty($c['livreur_id']) ? "ID #".$c['livreur_id'] : "En attente de prise en charge"; ?></p>
                </div>
            <?php 
                endif; 
            endforeach; 
            
            if ($count_delivery === 0) echo "<p>Aucune commande n'est actuellement sur la route.</p>";
            ?>
        </div>
    </section>

</main>

<footer>
    <p>&copy; 2026 LOS POLLOS HERMANOS - TASTE THE FAMILY</p>
</footer>

</body>
</html>

