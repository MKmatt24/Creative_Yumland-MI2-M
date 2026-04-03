<?php
session_start();

//Vérification si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit();
}

$id_commande = $_GET['id'] ?? null;

if (!$id_commande) {
    die("Aucune course sélectionnée.");
}

//Récupération des data du fichier JSON
$fichierCommandes = '../DATA/commande.json';
$commandesData = file_get_contents($fichierCommandes);
$commandes = json_decode($commandesData, true);

$commande_trouvee = null;

//Recherche de la commande pour l'affichage
foreach ($commandes as $cmd) {
    if ($cmd['id'] == $id_commande) {
        $commande_trouvee = $cmd;
        break;
    }
}

if (!$commande_trouvee) {
    die("Erreur : Commande introuvable.");
}

$gain_livreur = $commande_trouvee['gain_livreur'] ?? 0;
$distance_km = $commande_trouvee['distance_km'] ?? 0;
$temps_estime = $commande_trouvee['temps_minutes'] ?? 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Livraison Réussie - LOS POLLOS HERMANOS</title>
    <link rel="stylesheet" href="../CSS/livraison_fini.css">
</head>
<body>

    <main class="success-page">
        
        <div class="success-container">
            <div class="check-animation">
                <svg viewBox="0 0 52 52" class="checkmark">
                    <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/>
                    <path class="checkmark__check" fill="none" d="M14.1 27.2 l7.1 7.2 16.7 -16.8"/>
                </svg>
            </div>

            <h1>Livraison Terminée !</h1>
            <p>Merci pour votre rapidité ⚡</p>

            <div class="earnings-card">
                <span class="label">Gain de la course</span>
                <span class="amount">+ <?= number_format($gain_livreur, 2, ',', ' ') ?> €</span>
                <hr>
                <div class="stats-row">
                    <div>
                        <span class="small-label">Temps</span>
                        <span class="value"><?= $temps_estime ?> min</span>
                    </div>
                    <div>
                        <span class="small-label">Distance</span>
                        <span class="value"><?= $distance_km ?> km</span>
                    </div>
                </div>
            </div>

            <div class="action-buttons">
                <a href="Livraisons_en_attente.php" class="primary-btn">🏠 Retour à la page de livraison</a>
            </div>
        </div>

    </main>

</body>
</html>