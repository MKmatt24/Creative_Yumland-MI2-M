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

// A. On récupère la clé API et on prépare la chaîne (Ordre : Clé#Trans#Montant#Vendeur#Statut#)
$api_key = trim(getAPIKey($vendeur));
$hash_string = $api_key . "#" . $transaction . "#" . $montant . "#" . $vendeur . "#" . $statut . "#";

// B. On calcule le hash localement pour comparaison
$control_calcule = md5($hash_string);

// C. (L'AJOUT) On normalise le statut pour ne pas rater une validation à cause d'une minuscule
$statut_check = strtoupper($statut);

// D. On vérifie si TOUT est bon : la signature ET le code de succès
$paiement_valide = (
    $control_rx === $control_calcule && 
    ($statut_check === "V" || $statut_check === "OK" || $statut_check === "ACCEPTED")
);

/**
 * 3. TRAITEMENT ET SAUVEGARDE DE LA COMMANDE
 */
if ($paiement_valide) {
    $file = '../DATA/commande.json';
    $commandesData = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

    if (isset($_SESSION['modification_id'])) {
        // Cas d'une modification : on met à jour la commande existante
        foreach ($commandesData as &$c) {
            if ($c['id'] == $_SESSION['modification_id']) {
                $c['articles'] = $_SESSION['panier'];
                // Nouveau total = Ancien total payé + complément
                $c['prix_total'] = $_SESSION['modification_total_initial'] + (float)$montant;
                $c['date_modification'] = date('d/m/Y H:i');
                $c['statut'] = "a_preparer"; // Repasse en file d'attente
                break;
            }
        }
    } else {
        // On prépare la nouvelle commande
        $nouvelle_commande = [
            "id" => $transaction,
            "date" => date('d/m/Y H:i'),
            "client" => $_SESSION['prenom'] . " " . $_SESSION['nom'],
            "prix_total" => (float)$montant, 
            "statut" => "a_preparer",
            "articles" => $_SESSION['panier'],
            "type" => $_SESSION['planification']['type'] ?? 'direct',
            "horaire" => $_SESSION['planification']['horaire'] ?? 'ASAP'
        ];

        if (isset($_SESSION['coupon'])) {
            $nouvelle_commande['coupon_utilise'] = $_SESSION['coupon']['valeur'];
        }

        $commandesData[] = $nouvelle_commande;
    }

    file_put_contents($file, json_encode($commandesData, JSON_PRETTY_PRINT));

    // --- NETTOYAGE APRÈS ACHAT ---
    unset($_SESSION['panier']);       // Vide le panier
    unset($_SESSION['coupon']);       // SUPPRIME LE COUPON ICI
    unset($_SESSION['planification']); // Vide les infos de livraison
    unset($_SESSION['modification_id']);
    unset($_SESSION['modification_total_initial']);
    unset($_SESSION['paiement_complementaire']);
    unset($_SESSION['difference_prix']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Confirmation | Los Pollos Hermanos</title>
    <link rel="shortcut icon" type="image/png" href="../IMAGES/logo.png">
    <link rel="icon" type="image/png" href="../IMAGES/logo.png">
    <link rel="stylesheet" href="../CSS/accueil.css">
    <link rel="stylesheet" href="../CSS/menu.css">
</head>
<body class="page-dark">

<?php include '../LIB/header.php'; ?>

    <div class="theme-switcher-container">
        <button onclick="cycleTheme()" id="theme-toggle-btn" class="theme-switcher-btn">
            🎨 Changer de style
        </button>
    </div>

<main class="feedback-container">
    <div class="feedback-card">
        
        <?php if ($paiement_valide): ?>
            <div class="feedback-icon">🍗</div>
            <h2 class="text-success">✓ PAIEMENT RÉUSSI</h2>
            <p class="mb-10">Merci pour votre confiance. L'excellence est en préparation.</p>
            <p>Référence transaction : <strong class="text-orange"><?= htmlspecialchars($transaction) ?></strong></p>
            
            <div class="feedback-actions">
                <a href="suivi_commande.php" class="btn-full">
                    SUIVRE MA COMMANDE EN DIRECT
                </a>

                <a href="accueil.php" class="btn-secondary-full">
                    Retourner à l'accueil
                </a>
            </div>

        <?php else: ?>
            <div class="feedback-icon">⚠️</div>
            <h2 class="text-orange">ÉCHEC DU PAIEMENT</h2>
            <p class="mb-25">La transaction a été refusée ou la signature est invalide.</p>
            <a href="panier.php" class="btn-full">RÉESSAYER LE PAIEMENT</a>
        <?php endif; ?>

    </div>
</main>

<?php include '../LIB/footer.php'; ?>

</body>
</html>
