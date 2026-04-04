<?php
session_start();

// 1. Chargement des commandes depuis le fichier JSON
$commandes_json = file_get_contents('../DATA/commande.json');
$commandes = json_decode($commandes_json, true);

// Simulation du rôle (Normalement géré par tes camarades via la session)
// On considère ici que l'utilisateur est le "Restaurateur" (Cuisinier)
$role_utilisateur = isset($_SESSION['user']['role']) ? $_SESSION['user']['role'] : 'restaurateur'; 
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suivi des Commandes | Los Pollos Hermanos</title>
    <link rel="stylesheet" href="../CSS/commande.css">
</head>
<body>

<header>
    <nav>
        <div class="logo">
            <div class="logo-box">
                <a href="accueil.php">
                    <img src="../IMAGES/logo.png" alt="Logo Los Pollos" class="nav-logo">
                </a>
            </div>
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
        <h2 class="section-title">Commandes à préparer (Cuisine)</h2>
        <div class="orders-grid" id="prep-grid">
            <?php 
            $count_prep = 0;
            foreach ($commandes as $c): 
                if ($c['statut'] === 'preparation'): 
                    $count_prep++;
            ?>
                <div class="order-card prep">
                    <div class="order-header">
                        <span class="order-id">#<?php echo $c['id']; ?></span>
                        <span class="badge">En cuisine</span>
                    </div>
                    <h3><?php echo htmlspecialchars($c['client']); ?></h3>
                    <p class="order-content">
                        <?php 
                        foreach ($c['articles'] as $art) {
                            echo $art['quantite'] . "x " . $art['nom'] . ", ";
                        }
                        ?>
                    </p>
                    <p><strong>Total : <?php echo $c['prix_total']; ?>€</strong></p>
                    
                    <form action="../TRAITEMENTS/update_statut.php" method="POST" style="margin-top:10px;">
                        <input type="hidden" name="id_commande" value="<?php echo $c['id']; ?>">
                        <input type="hidden" name="nouveau_statut" value="livraison">
                        <button type="submit" class="btn-ready" style="background-color: #27ae60; color: white; border: none; padding: 10px; border-radius: 5px; cursor: pointer; width: 100%;">
                            MARQUER COMME PRÊT
                        </button>
                    </form>
                </div>
            <?php 
                endif; 
            endforeach; 

            if ($count_prep === 0) echo "<p>Aucune commande en attente de préparation.</p>";
            ?>
        </div>
    </section>

    <?php if ($role_utilisateur !== 'restaurateur'): ?>
    <hr class="separator">

    <section class="order-section">
        <h2 class="section-title">En cours de livraison</h2>
        <div class="orders-grid" id="delivery-grid">
            <?php foreach ($commandes as $c): 
                if ($c['statut'] === 'livraison'): 
            ?>
                <div class="order-card delivery">
                    <div class="order-header">
                        <span class="order-id">#<?php echo $c['id']; ?></span>
                        <span class="badge">Sur la route</span>
                    </div>
                    <h3><?php echo htmlspecialchars($c['client']); ?></h3>
                    <p class="order-content">Adresse : <?php echo $c['adresse']; ?></p>
                </div>
            <?php 
                endif; 
            endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

</main>

<footer>
    <p>&copy; 2026 LOS POLLOS HERMANOS - TASTE THE FAMILY</p>
</footer>

</body>
</html>