<?php
session_start();
require_once '../TRAITEMENTS/getapikey.php';

// 1. RÉCUPÉRATION DES PARAMÈTRES RENVOYÉS PAR CYBANK
$transaction  = $_GET['transaction'] ?? "";
$montant      = $_GET['montant'] ?? "";
$vendeur      = $_GET['vendeur'] ?? "";
$statut       = $_GET['status'] ?? $_GET['statut'] ?? "";
$control_rx   = $_GET['control'] ?? "";

// 2. VÉRIFICATION DE LA SIGNATURE (MD5)
$api_key = trim(getAPIKey($vendeur));
$hash_string = $api_key . "#" . $transaction . "#" . $montant . "#" . $vendeur . "#" . $statut . "#";
$control_calcule = md5($hash_string);

// Le paiement est valide si la signature correspond et que le statut est 'accepted' ou 'ok'
$paiement_valide = ($control_rx === $control_calcule && ($statut === "accepted" || $statut === "ok"));

/**
 * 3. TRAITEMENT ET SAUVEGARDE DE LA COMMANDE
 */
if ($paiement_valide) {
    $file = '../DATA/commande.json';
    // Chargement sécurisé des commandes existantes
    $commandesData = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
    if (!is_array($commandesData)) $commandesData = [];

    // Préparation des données de la commande
    $nouvelle_commande = [
        "id"                => "CMD" . str_pad(count($commandesData) + 1, 3, "0", STR_PAD_LEFT),
        "id_transaction"    => $transaction,
        "client"            => ($_SESSION['user']['prenom'] ?? 'Client') . " " . ($_SESSION['user']['nom'] ?? 'Anonyme'),
        "prix_total"        => (float)$montant,
        "date_commande"     => date('Y-m-d H:i:s'),
        
        // Planification récupérée depuis la session (panier.php)
        "type_livraison"    => $_SESSION['planification']['type'] ?? 'immediate',
        "horaire_souhaite"  => $_SESSION['planification']['horaire'] ?? 'ASAP',
        
        // Statut CRITIQUE pour l'affichage restaurateur
        "statut"            => "a_preparer", 
        "statut_logistique" => "a_preparer", 
        "articles"          => $_SESSION['panier'] ?? []
    ];

    // Sauvegarde au format JSON
    $commandesData[] = $nouvelle_commande;
    file_put_contents($file, json_encode($commandesData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    // Nettoyage de la session après succès pour éviter les doublons
    unset($_SESSION['panier']);
    unset($_SESSION['planification']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Confirmation | Los Pollos Hermanos</title>
    <link rel="stylesheet" href="../CSS/accueil.css">
    <link rel="stylesheet" href="../CSS/menu.css">
</head>
<body style="background-color: #0a0a0a; color: white; font-family: sans-serif;">

<?php include '../LIB/header.php'; ?>

<main style="min-height: 70vh; display: flex; align-items: center; justify-content: center;">
    <div style="max-width: 600px; width: 90%; padding: 40px; text-align: center; border: 1px solid #333; border-radius: 15px; background: #111; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
        
        <?php if ($paiement_valide): ?>
            <div style="font-size: 4rem; margin-bottom: 10px;">🍗</div>
            <h2 style="color: #4CAF50; font-size: 2rem; margin-bottom: 20px;">✓ PAIEMENT RÉUSSI</h2>
            <p style="color: #ccc; margin-bottom: 10px;">Merci pour votre confiance. L'excellence est en préparation.</p>
            <p>Référence transaction : <strong style="color: #ff6b35;"><?= htmlspecialchars($transaction) ?></strong></p>
            
            <div style="margin-top: 40px; display: flex; flex-direction: column; gap: 15px;">
                <a href="suivi_commande.php" style="background: #ff6b35; color: white; padding: 15px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 1.1rem;">
                    SUIVRE MA COMMANDE EN DIRECT
                </a>

                <a href="accueil.php" style="color: #888; text-decoration: none; font-size: 0.9rem; margin-top: 10px;">
                    Retourner à l'accueil
                </a>
            </div>

        <?php else: ?>
            <div style="font-size: 4rem; margin-bottom: 10px;">⚠️</div>
            <h2 style="color: #ff6b35; font-size: 2rem; margin-bottom: 20px;">ÉCHEC DU PAIEMENT</h2>
            <p style="color: #ccc; margin-bottom: 25px;">La transaction a été refusée ou la signature est invalide.</p>
            <a href="panier.php" style="background: #ff6b35; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;">RÉESSAYER LE PAIEMENT</a>
        <?php endif; ?>

    </div>
</main>

<footer style="text-align: center; padding: 40px 20px; color: #555; font-size: 0.9rem;">
    <p>&copy; 2026 Los Pollos Hermanos - Albuquerque. Tasty is the word.</p>
</footer>

</body>
</html>